<?php

namespace IvyForms\Services\Payment;

/**
 * Outcome of a payment gateway charge attempt during form submission.
 */
final class PaymentChargeResult
{
    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_REQUIRES_ACTION = 'requires_action';

    public const STATUS_FAILED = 'failed';

    public const STATUS_DECLINED = 'declined';

    private string $status;

    private string $paymentMethod;

    private float $paymentTotal;

    private string $paymentCurrency;

    private ?string $transactionId;

    private ?string $receiptUrl;

    private ?string $clientSecret;

    private ?string $paymentIntentId;

    private ?string $message;

    private ?string $customerName;

    private ?string $squareOrderId;

    private ?string $squareCustomerId;

    private ?string $providerMeta;

    /**
     * @param array{
     *     status: string,
     *     paymentMethod: string,
     *     paymentTotal?: float,
     *     paymentCurrency?: string,
     *     transactionId?: ?string,
     *     receiptUrl?: ?string,
     *     clientSecret?: ?string,
     *     paymentIntentId?: ?string,
     *     message?: ?string,
     *     customerName?: ?string,
     *     squareOrderId?: ?string,
     *     squareCustomerId?: ?string,
     *     providerMeta?: ?string
     * } $data
     */
    private function __construct(array $data)
    {
        $this->status = $data['status'];
        $this->paymentMethod = $data['paymentMethod'];
        $this->paymentTotal = (float) ($data['paymentTotal'] ?? 0.0);
        $this->paymentCurrency = (string) ($data['paymentCurrency'] ?? 'USD');
        $this->transactionId = $data['transactionId'] ?? null;
        $this->receiptUrl = $data['receiptUrl'] ?? null;
        $this->clientSecret = $data['clientSecret'] ?? null;
        $this->paymentIntentId = $data['paymentIntentId'] ?? null;
        $this->message = $data['message'] ?? null;
        $this->customerName = $data['customerName'] ?? null;
        $this->squareOrderId = $data['squareOrderId'] ?? null;
        $this->squareCustomerId = $data['squareCustomerId'] ?? null;
        $this->providerMeta = $data['providerMeta'] ?? null;
    }

    /**
     * @param array<string, mixed> $paymentResult Gateway success payload (must include transactionId)
     */
    public static function succeeded(
        string $paymentMethod,
        array $paymentResult,
        float $paymentTotal,
        string $paymentCurrency,
        ?string $customerName = null,
        ?string $squareOrderId = null,
        ?string $squareCustomerId = null
    ): self {
        return new self([
            'status' => self::STATUS_SUCCEEDED,
            'paymentMethod' => $paymentMethod,
            'paymentTotal' => $paymentTotal,
            'paymentCurrency' => $paymentCurrency,
            'transactionId' => (string) ($paymentResult['transactionId'] ?? ''),
            'receiptUrl' => !empty($paymentResult['receiptUrl'])
                ? (string) $paymentResult['receiptUrl']
                : null,
            'customerName' => $customerName,
            'squareOrderId' => $squareOrderId,
            'squareCustomerId' => $squareCustomerId,
            'providerMeta' => isset($paymentResult['providerMeta'])
                ? (string) $paymentResult['providerMeta']
                : null,
        ]);
    }

    public static function requiresAction(
        string $paymentMethod,
        string $clientSecret,
        string $paymentIntentId,
        float $paymentTotal,
        string $paymentCurrency
    ): self {
        return new self([
            'status' => self::STATUS_REQUIRES_ACTION,
            'paymentMethod' => $paymentMethod,
            'paymentTotal' => $paymentTotal,
            'paymentCurrency' => $paymentCurrency,
            'clientSecret' => $clientSecret,
            'paymentIntentId' => $paymentIntentId,
            'transactionId' => $paymentIntentId,
        ]);
    }

    public static function failed(string $paymentMethod, string $message): self
    {
        return new self([
            'status' => self::STATUS_FAILED,
            'paymentMethod' => $paymentMethod,
            'message' => $message,
        ]);
    }

    public static function terminalFailure(
        string $paymentMethod,
        string $message,
        string $transactionId,
        float $paymentTotal,
        string $paymentCurrency,
        string $status = self::STATUS_FAILED,
        ?string $receiptUrl = null
    ): self {
        return new self([
            'status' => $status,
            'paymentMethod' => $paymentMethod,
            'message' => $message,
            'transactionId' => $transactionId,
            'paymentTotal' => $paymentTotal,
            'paymentCurrency' => $paymentCurrency,
            'receiptUrl' => $receiptUrl,
        ]);
    }

    public function isSucceeded(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    public function isRequiresAction(): bool
    {
        return $this->status === self::STATUS_REQUIRES_ACTION;
    }

    public function isFailed(): bool
    {
        return !$this->isSucceeded() && !$this->isRequiresAction();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getPaymentTotal(): float
    {
        return $this->paymentTotal;
    }

    public function getPaymentCurrency(): string
    {
        return $this->paymentCurrency;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getReceiptUrl(): ?string
    {
        return $this->receiptUrl;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }

    public function getPaymentIntentId(): ?string
    {
        return $this->paymentIntentId;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function getSquareOrderId(): ?string
    {
        return $this->squareOrderId;
    }

    public function getSquareCustomerId(): ?string
    {
        return $this->squareCustomerId;
    }

    public function getProviderMeta(): ?string
    {
        return $this->providerMeta;
    }

    /**
     * Shape expected by FormSubmissionController for a successful charge.
     *
     * @return array<string, mixed>
     */
    public function toPaymentResultArray(): array
    {
        return [
            'success' => true,
            'transactionId' => (string) ($this->transactionId ?? ''),
            'paymentMethod' => $this->paymentMethod,
            'status' => $this->status,
            'receiptUrl' => $this->receiptUrl,
            'providerMeta' => $this->providerMeta,
        ];
    }
}
