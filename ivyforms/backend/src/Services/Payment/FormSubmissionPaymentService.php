<?php

namespace IvyForms\Services\Payment;

use IvyForms\Entity\Form\Form;
use IvyForms\Entity\Payment\Order as OrderEntity;
use IvyForms\Entity\Payment\Payment as PaymentEntity;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\ValueObjects\Payment\Order as OrderValueObject;
use IvyForms\ValueObjects\Payment\Payment as PaymentValueObject;
use WP_REST_Response;

/**
 * Payment processing during public form submission (gateway dispatch + persistence).
 */
class FormSubmissionPaymentService
{
    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_FAILED = 'failed';

    public const STATUS_ORPHANED = 'orphaned';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_DISPUTED = 'disputed';

    private PaymentAmountCalculator $paymentAmountCalculator;

    private PaymentManager $paymentManager;

    private OrderManager $orderManager;

    private PaymentGatewayRegistry $paymentGatewayRegistry;

    public function __construct(
        PaymentAmountCalculator $paymentAmountCalculator,
        PaymentManager $paymentManager,
        OrderManager $orderManager,
        PaymentGatewayRegistry $paymentGatewayRegistry
    ) {
        $this->paymentAmountCalculator = $paymentAmountCalculator;
        $this->paymentManager = $paymentManager;
        $this->orderManager = $orderManager;
        $this->paymentGatewayRegistry = $paymentGatewayRegistry;
    }

    /**
     * @param array<int, object> $formFields
     */
    public function findPaymentMethodField(array $formFields): ?object
    {
        foreach ($formFields as $field) {
            if (method_exists($field, 'getType') && $field->getType() === 'payment-method') {
                return $field;
            }
        }

        return null;
    }

    public function persistChargeFromResult(
        Form $form,
        PaymentChargeResult $chargeResult,
        string $status,
        ?string $customerName = null
    ): void {
        if ($status === self::STATUS_PENDING) {
            $this->persistDurableStripePayment($chargeResult, $form, $status, $customerName);

            return;
        }

        $this->persistPayment(
            0,
            $form->getId(),
            [
                'transactionId' => (string) ($chargeResult->getTransactionId() ?? ''),
                'receiptUrl' => $chargeResult->getReceiptUrl(),
                'providerMeta' => $chargeResult->getProviderMeta(),
            ],
            $chargeResult->getPaymentTotal(),
            $chargeResult->getPaymentCurrency(),
            $chargeResult->getPaymentMethod(),
            $chargeResult->getStatus()
        );
    }

    public function paymentAlreadyLinkedToEntry(string $transactionId): bool
    {
        return $this->paymentManager->paymentAlreadyLinkedToEntry($transactionId);
    }

    public function duplicatePaymentErrorResponse(): WP_REST_Response
    {
        return $this->paymentErrorResponse(
            BackendStrings::getPaymentStrings()['duplicate_payment_transaction']
        );
    }

    /**
     * Persist a successful submission payment. An entryId of 0 means the form does not store
     * entries, so the payment is kept without an entry link instead of being dropped.
     *
     * @param array<string, mixed> $paymentOutcome
     * @return bool False when a payment was expected but could not be recorded (e.g. duplicate transactionId).
     */
    public function recordSubmissionPayment(int $entryId, int $formId, array $paymentOutcome): bool
    {
        return $this->recordPaymentFromOutcome($formId, $paymentOutcome, self::STATUS_SUCCEEDED, $entryId);
    }

    /**
     * Persist a charge whose submission could not be stored, so the transaction is not lost.
     *
     * @param array<string, mixed> $paymentOutcome
     * @return bool False when a payment was expected but could not be recorded (e.g. duplicate transactionId).
     */
    public function recordOrphanedPayment(int $formId, array $paymentOutcome): bool
    {
        return $this->recordPaymentFromOutcome($formId, $paymentOutcome, self::STATUS_ORPHANED);
    }

    /**
     * @param array<string, mixed> $paymentOutcome
     */
    private function recordPaymentFromOutcome(
        int $formId,
        array $paymentOutcome,
        string $status,
        int $entryId = 0
    ): bool {
        $paymentResult = $paymentOutcome['paymentResult'] ?? null;
        if (!is_array($paymentResult)) {
            return true;
        }

        return $this->persistPayment(
            $entryId,
            $formId,
            $paymentResult,
            (float) ($paymentOutcome['paymentTotal'] ?? 0),
            (string) ($paymentOutcome['paymentCurrency'] ?? 'USD'),
            (string) ($paymentOutcome['paymentMethod'] ?? ''),
            $status,
            isset($paymentOutcome['customerName']) ? (string) $paymentOutcome['customerName'] : null
        );
    }

    /**
     * @param array<string, mixed> $paymentResult
     */
    private function persistPayment(
        int $entryId,
        int $formId,
        array $paymentResult,
        float $paymentTotal,
        string $paymentCurrency,
        string $paymentMethod,
        string $status,
        ?string $customerName = null
    ): bool {
        $transactionId = (string) ($paymentResult['transactionId'] ?? '');
        if ($transactionId === '') {
            return false;
        }

        $paymentEntity = new PaymentEntity(
            new PaymentValueObject(
                0,
                $entryId > 0 ? $entryId : null,
                $formId,
                $transactionId,
                $paymentMethod,
                $customerName !== null && $customerName !== '' ? $customerName : null,
                MoneyMinorUnits::toMinor($paymentTotal, $paymentCurrency),
                strtoupper($paymentCurrency),
                !empty($paymentResult['receiptUrl']) ? (string) $paymentResult['receiptUrl'] : null,
                null,
                !empty($paymentResult['providerMeta']) ? (string) $paymentResult['providerMeta'] : null
            )
        );
        $paymentEntity->setStatus($status);

        return $this->paymentManager->upsertByTransactionId($paymentEntity);
    }

    /**
     * Customers column value: mapped name field, then mapped email.
     *
     * @param array<string, mixed> $buyerInfo
     */
    public static function resolveCustomerNameFromBuyerInfo(array $buyerInfo): ?string
    {
        return SquareBuyerInfoBuilder::customerName($buyerInfo);
    }

    /**
     * Persist the Square order record linked to an entry.
     */
    public function recordOrderForEntry(
        int $entryId,
        string $squareOrderId,
        ?string $squareCustomerId,
        int $amountCents,
        string $currency
    ): void {
        if ($squareOrderId === '') {
            return;
        }

        $orderEntity = new OrderEntity(
            new OrderValueObject(
                0,
                $entryId,
                $squareOrderId,
                'COMPLETED',
                $squareCustomerId,
                $amountCents,
                strtoupper($currency)
            )
        );

        $this->orderManager->createOrder($orderEntity);
    }

    /**
     * @param array<string, mixed> $rawParams
     * @param array<int, object> $formFields
     * @param array<string, mixed> $submissionData
     * @return array{
     *     errorResponse: ?WP_REST_Response,
     *     paymentResult: ?array<string, mixed>,
     *     paymentTotal: float,
     *     paymentCurrency: string,
     *     paymentMethod: string,
     *     squareOrderId: string,
     *     squareCustomerId: ?string,
     *     customerName: ?string,
     *     requiresAction: bool,
     *     clientSecret: ?string,
     *     paymentIntentId: ?string
     * }
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processWhenRequired(
        array $rawParams,
        Form $form,
        array $formFields,
        array $submissionData,
        ?object $paymentField
    ): array {
        if ($this->shouldSkipPayment($paymentField, $rawParams)) {
            return $this->skippedPaymentOutcome();
        }

        return (new FormSubmissionChargeOrchestrator(
            $this->paymentAmountCalculator,
            $this->paymentGatewayRegistry,
            $this
        ))->process($rawParams, $form, $formFields, $submissionData, $paymentField);
    }

    /**
     * @return array<string, mixed>
     */
    private function skippedPaymentOutcome(): array
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
     * @param array<string, mixed> $rawParams
     */
    private function shouldSkipPayment(?object $paymentField, array $rawParams): bool
    {
        if ($paymentField === null) {
            return true;
        }

        return !PaymentMethodFieldConfig::isRequired($paymentField)
            && PaymentMethodFieldConfig::resolveGatewayIdFromToken($rawParams, $paymentField) === null;
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

    private function persistDurableStripePayment(
        PaymentChargeResult $chargeResult,
        Form $form,
        string $status,
        ?string $customerName = null
    ): void {
        $transactionId = (string) ($chargeResult->getTransactionId() ?? '');
        if (
            !in_array($chargeResult->getPaymentMethod(), ['stripe', 'paypal'], true)
            || $transactionId === ''
        ) {
            return;
        }

        $this->persistPayment(
            0,
            $form->getId(),
            [
                'transactionId' => $transactionId,
                'receiptUrl' => $chargeResult->getReceiptUrl(),
                'providerMeta' => $chargeResult->getProviderMeta(),
            ],
            $chargeResult->getPaymentTotal(),
            $chargeResult->getPaymentCurrency(),
            $chargeResult->getPaymentMethod(),
            $status,
            $customerName ?? $chargeResult->getCustomerName()
        );
    }
}
