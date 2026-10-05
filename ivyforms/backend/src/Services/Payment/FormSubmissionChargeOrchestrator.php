<?php

namespace IvyForms\Services\Payment;

use IvyForms\Entity\Form\Form;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Response;

/**
 * Charge dispatch for a public submission (amount, gateway, persist, outcome).
 */
class FormSubmissionChargeOrchestrator
{
    private PaymentAmountCalculator $paymentAmountCalculator;

    private PaymentGatewayRegistry $paymentGatewayRegistry;

    private FormSubmissionPaymentService $paymentService;

    public function __construct(
        PaymentAmountCalculator $paymentAmountCalculator,
        PaymentGatewayRegistry $paymentGatewayRegistry,
        FormSubmissionPaymentService $paymentService
    ) {
        $this->paymentAmountCalculator = $paymentAmountCalculator;
        $this->paymentGatewayRegistry = $paymentGatewayRegistry;
        $this->paymentService = $paymentService;
    }

    /**
     * @param array<string, mixed> $rawParams
     * @param array<int, object> $formFields
     * @param array<string, mixed> $submissionData
     * @return array<string, mixed>
     */
    public function process(
        array $rawParams,
        Form $form,
        array $formFields,
        array $submissionData,
        object $paymentField
    ): array {
        $emptyResult = $this->emptyPaymentOutcome();
        $submissionByTypeIndex = $this->mapSubmissionToTypeIndexKeys($submissionData, $formFields);
        $prepared = $this->prepareChargeableRequest(
            $form,
            $formFields,
            $submissionByTypeIndex,
            $paymentField,
            $rawParams,
            $emptyResult
        );
        if ($prepared['errorResponse'] !== null) {
            return $prepared;
        }

        return $this->runGatewayCharge(
            $rawParams,
            $form,
            $formFields,
            $submissionData,
            $paymentField,
            $submissionByTypeIndex,
            $prepared,
            $emptyResult
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPaymentOutcome(): array
    {
        return [
            'errorResponse' => null,
            'paymentResult' => null,
            'paymentTotal' => 0.0,
            'paymentCurrency' => 'USD',
            'paymentMethod' => 'square',
            'squareOrderId' => '',
            'squareCustomerId' => null,
            'customerName' => null,
            'requiresAction' => false,
            'clientSecret' => null,
            'paymentIntentId' => null,
        ];
    }

    /**
     * @param array<int, object> $formFields
     * @param array<string, mixed> $submissionByTypeIndex
     * @param array<string, mixed> $rawParams
     * @param array<string, mixed> $emptyResult
     * @return array<string, mixed>
     */
    private function prepareChargeableRequest(
        Form $form,
        array $formFields,
        array $submissionByTypeIndex,
        object $paymentField,
        array $rawParams,
        array $emptyResult
    ): array {
        $currency = strtoupper((string) $form->getPaymentSettings()->getCurrency());
        if ($currency === '') {
            $currency = 'USD';
        }

        $total = $this->paymentAmountCalculator->calculateTotal(
            array_map(static fn($field) => $field->toArray(), $formFields),
            $submissionByTypeIndex,
            $currency
        );
        if (!MoneyMinorUnits::isChargeable($total, $currency)) {
            return array_merge($emptyResult, [
                'errorResponse' => $this->paymentErrorResponse(
                    BackendStrings::getPaymentStrings()['invalid_payment_amount']
                ),
            ]);
        }

        $gatewayId = $this->resolveGatewayId($paymentField, $rawParams);
        if ($gatewayId === null) {
            return array_merge($emptyResult, [
                'errorResponse' => $this->paymentErrorResponse(
                    BackendStrings::getPaymentStrings()['payment_gateway_unavailable']
                ),
            ]);
        }

        return array_merge($emptyResult, [
            'total' => $total,
            'gatewayId' => $gatewayId,
        ]);
    }

    /**
     * @param array<string, mixed> $rawParams
     * @param array<int, object> $formFields
     * @param array<string, mixed> $submissionData
     * @param array<string, mixed> $submissionByTypeIndex
     * @param array<string, mixed> $prepared
     * @param array<string, mixed> $emptyResult
     * @return array<string, mixed>
     */
    private function runGatewayCharge(
        array $rawParams,
        Form $form,
        array $formFields,
        array $submissionData,
        object $paymentField,
        array $submissionByTypeIndex,
        array $prepared,
        array $emptyResult
    ): array {
        $gatewayId = (string) $prepared['gatewayId'];
        try {
            $gateway = $this->paymentGatewayRegistry->get($gatewayId);
        } catch (\Throwable $e) {
            return array_merge($emptyResult, [
                'paymentMethod' => $gatewayId,
                'errorResponse' => $this->paymentErrorResponse(
                    BackendStrings::getPaymentStrings()['payment_gateway_unavailable']
                ),
            ]);
        }

        $chargeResult = $gateway->processCharge(
            $rawParams,
            $form,
            $formFields,
            $submissionData,
            $paymentField,
            (float) $prepared['total']
        );
        if ($chargeResult->isFailed()) {
            return $this->failedChargeOutcome($chargeResult, $form, $emptyResult);
        }

        return $this->successfulChargeOutcome(
            $chargeResult,
            $form,
            $gatewayId,
            $formFields,
            $submissionData,
            $submissionByTypeIndex,
            $emptyResult
        );
    }

    /**
     * @param array<string, mixed> $emptyResult
     * @return array<string, mixed>
     */
    private function failedChargeOutcome(
        PaymentChargeResult $chargeResult,
        Form $form,
        array $emptyResult
    ): array {
        $this->paymentService->persistChargeFromResult($form, $chargeResult, $chargeResult->getStatus());

        return array_merge($emptyResult, [
            'paymentMethod' => $chargeResult->getPaymentMethod(),
            'errorResponse' => $this->paymentErrorResponse(
                (string) ($chargeResult->getMessage() ?? BackendStrings::getPaymentStrings()['payment_failed'])
            ),
        ]);
    }

    /**
     * @param array<int, object> $formFields
     * @param array<string, mixed> $submissionData
     * @param array<string, mixed> $submissionByTypeIndex
     * @param array<string, mixed> $emptyResult
     * @return array<string, mixed>
     */
    private function successfulChargeOutcome(
        PaymentChargeResult $chargeResult,
        Form $form,
        string $gatewayId,
        array $formFields,
        array $submissionData,
        array $submissionByTypeIndex,
        array $emptyResult
    ): array {
        $customerName = SubmissionPaymentCustomerName::resolve(
            $chargeResult->getCustomerName(),
            $form,
            $gatewayId,
            $formFields,
            $submissionData,
            $submissionByTypeIndex
        );
        $this->paymentService->persistChargeFromResult(
            $form,
            $chargeResult,
            FormSubmissionPaymentService::STATUS_PENDING,
            $customerName
        );

        if ($chargeResult->isRequiresAction()) {
            return array_merge($emptyResult, [
                'paymentMethod' => $chargeResult->getPaymentMethod(),
                'paymentTotal' => $chargeResult->getPaymentTotal(),
                'paymentCurrency' => $chargeResult->getPaymentCurrency(),
                'customerName' => $customerName,
                'requiresAction' => true,
                'clientSecret' => $chargeResult->getClientSecret(),
                'paymentIntentId' => $chargeResult->getPaymentIntentId(),
                'paymentResult' => [
                    'transactionId' => (string) ($chargeResult->getTransactionId() ?? ''),
                    'paymentMethod' => $chargeResult->getPaymentMethod(),
                    'status' => $chargeResult->getStatus(),
                ],
            ]);
        }

        return [
            'errorResponse' => null,
            'paymentResult' => $chargeResult->toPaymentResultArray(),
            'paymentTotal' => $chargeResult->getPaymentTotal(),
            'paymentCurrency' => $chargeResult->getPaymentCurrency(),
            'paymentMethod' => $chargeResult->getPaymentMethod(),
            'squareOrderId' => (string) ($chargeResult->getSquareOrderId() ?? ''),
            'squareCustomerId' => $chargeResult->getSquareCustomerId(),
            'customerName' => $customerName,
            'requiresAction' => false,
            'clientSecret' => null,
            'paymentIntentId' => null,
        ];
    }

    /**
     * @param array<string, mixed> $rawParams
     */
    private function resolveGatewayId(object $paymentField, array $rawParams): ?string
    {
        $fromToken = PaymentMethodFieldConfig::resolveGatewayIdFromToken($rawParams, $paymentField);
        if ($fromToken !== null) {
            return $this->paymentGatewayRegistry->has($fromToken) ? $fromToken : null;
        }

        if (PaymentMethodFieldConfig::resolveGatewayIdFromToken($rawParams) !== null) {
            return null;
        }

        return PaymentMethodFieldConfig::resolvePrimaryGatewayId($paymentField);
    }

    /**
     * @param array<string, mixed> $submissionData
     * @param array<int, object> $formFields
     * @return array<string, mixed>
     */
    private function mapSubmissionToTypeIndexKeys(array $submissionData, array $formFields): array
    {
        $mapped = [];

        foreach ($formFields as $field) {
            if (!method_exists($field, 'getType') || !method_exists($field, 'getIndex')) {
                continue;
            }

            $fieldId = method_exists($field, 'getId') ? $field->getId() : null;
            $type = (string) $field->getType();
            $index = (int) $field->getIndex();
            $key = $type . '_' . $index;

            if ($fieldId !== null && isset($submissionData[$fieldId])) {
                $mapped[$key] = $submissionData[$fieldId];
            } elseif (isset($submissionData[$key])) {
                $mapped[$key] = $submissionData[$key];
            }
        }

        return $mapped;
    }

    private function paymentErrorResponse(string $message): WP_REST_Response
    {
        return new WP_REST_Response([
            'message' => $message,
            'data' => [
                'success' => false,
                'payment_error' => true,
            ],
        ], 400);
    }
}
