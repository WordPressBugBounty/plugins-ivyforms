<?php

namespace IvyForms\Services\Payment;

use IvyForms\Entity\Form\Form;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Square card charge during public form submission.
 */
class SquarePaymentGateway implements PaymentGatewayInterface
{
    private SquarePaymentService $squarePaymentService;

    private SquareOrderService $squareOrderService;

    public function __construct(
        SquarePaymentService $squarePaymentService,
        SquareOrderService $squareOrderService
    ) {
        $this->squarePaymentService = $squarePaymentService;
        $this->squareOrderService = $squareOrderService;
    }

    public function getId(): string
    {
        return 'square';
    }

    /**
     * @param array<string, mixed> $rawParams
     * @param array<int, object> $formFields
     * @param array<string, mixed> $submissionData
     * @param object $paymentField
     */
    public function processCharge(
        array $rawParams,
        Form $form,
        array $formFields,
        array $submissionData,
        object $paymentField,
        float $total
    ): PaymentChargeResult {
        $tokenError = $this->validatePaymentTokens($rawParams);
        if ($tokenError !== null) {
            return PaymentChargeResult::failed($this->getId(), $tokenError);
        }

        $squareSettings = $this->squarePaymentService->getSquareSettings();
        $connectionError = $this->validateSquareConnection($squareSettings, $form);
        if ($connectionError !== null) {
            return PaymentChargeResult::failed($this->getId(), $connectionError);
        }

        $squareCurrency = (string) ($squareSettings['currency'] ?? 'USD');
        if (!MoneyMinorUnits::isChargeable($total, $squareCurrency)) {
            return PaymentChargeResult::failed(
                $this->getId(),
                BackendStrings::getPaymentStrings()['invalid_payment_amount']
            );
        }

        $submissionByTypeIndex = $this->mapSubmissionToTypeIndexKeys($submissionData, $formFields);
        $locationId = (string) ($squareSettings['locationId'] ?? '');
        $amountCents = MoneyMinorUnits::toMinor($total, $squareCurrency);
        $sourceId = sanitize_text_field((string) ($rawParams['sourceId'] ?? ''));
        $idempotencyKey = $this->serverIdempotencyKey(
            $form->getId(),
            $sourceId,
            $amountCents,
            $squareCurrency
        );
        $squareFormSettings = $this->resolveSquareFormSettings($form);
        $buyerInfo = SquareBuyerInfoBuilder::build(
            $squareFormSettings,
            $submissionByTypeIndex,
            $formFields,
            $submissionData
        );
        $uniqueRef = $this->buildUniqueRef();

        $customerId = $this->squareOrderService->getOrCreateCustomerFromEmail(
            (string) ($buyerInfo['email'] ?? '')
        );

        $orderResult = $this->squareOrderService->createOrder(
            $locationId,
            $amountCents,
            $squareCurrency,
            $idempotencyKey,
            $form->getName(),
            $uniqueRef,
            $customerId
        );

        if (empty($orderResult['success'])) {
            return PaymentChargeResult::failed(
                $this->getId(),
                $orderResult['message']
            );
        }

        $squareOrderId = (string) $orderResult['orderId'];

        $paymentResult = $this->squarePaymentService->processPayment(
            $sourceId,
            $idempotencyKey,
            $amountCents,
            $squareCurrency,
            $locationId,
            $this->buildPaymentNote(
                (string) ($squareFormSettings['paymentDescription'] ?? ''),
                $form->getName(),
                $uniqueRef
            ),
            $buyerInfo,
            $squareOrderId,
            $customerId
        );

        if (empty($paymentResult['success'])) {
            $this->squareOrderService->cancelOrder($squareOrderId, $locationId);

            return PaymentChargeResult::failed(
                $this->getId(),
                (string) ($paymentResult['message'] ?? BackendStrings::getPaymentStrings()['payment_failed'])
            );
        }

        return PaymentChargeResult::succeeded(
            $this->getId(),
            $paymentResult,
            $total,
            $squareCurrency,
            SquareBuyerInfoBuilder::customerName($buyerInfo),
            $squareOrderId,
            $customerId
        );
    }

    /**
     * @param array<string, mixed> $rawParams
     */
    private function validatePaymentTokens(array $rawParams): ?string
    {
        $sourceId = sanitize_text_field((string) ($rawParams['sourceId'] ?? ''));

        return $sourceId !== ''
            ? null
            : BackendStrings::getPaymentStrings()['payment_token_missing'];
    }

    /**
     * Square caps idempotency keys at 45 characters; createOrder appends "-order".
     */
    private function serverIdempotencyKey(
        int $formId,
        string $sourceId,
        int $amountCents,
        string $currency
    ): string {
        return substr(
            hash(
                'sha256',
                $formId . '|' . $sourceId . '|' . $amountCents . '|' . strtoupper($currency)
            ),
            0,
            32
        );
    }

    /**
     * @param array<string, mixed> $squareSettings
     */
    private function validateSquareConnection(array $squareSettings, Form $form): ?string
    {
        if (empty($squareSettings['connected'])) {
            return BackendStrings::getPaymentStrings()['square_not_connected'];
        }

        $formCurrency = $form->getPaymentSettings()->getCurrency();
        $squareCurrency = (string) ($squareSettings['currency'] ?? 'USD');
        if (strtoupper($formCurrency) !== strtoupper($squareCurrency)) {
            return BackendStrings::getPaymentStrings()['currency_mismatch'];
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveSquareFormSettings(Form $form): array
    {
        $integrationSettings = $form->getIntegrationSettings();
        if ($integrationSettings === null) {
            return [];
        }

        return $integrationSettings->get('square') ?? [];
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

    private function buildPaymentNote(string $template, string $formName, string $uniqueRef): string
    {
        $custom = str_replace('{form_name}', $formName, $template);
        $base = sanitize_text_field($custom !== '' ? $custom : $formName);

        return sprintf('IvyForms - %s [%s]', $base, $uniqueRef);
    }

    private function buildUniqueRef(): string
    {
        return strtoupper(substr(md5(wp_generate_uuid4()), 0, 8));
    }
}
