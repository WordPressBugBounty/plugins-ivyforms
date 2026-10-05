<?php

namespace IvyForms\ValueObjects\Payment;

/**
 * Immutable payment record value object.
 */
final class Payment
{
    public int $id;
    public ?int $entryId;
    public int $formId;
    public string $transactionId;
    public string $paymentMethod;
    public ?string $customerName;
    public int $amount;
    public string $currency;
    public ?string $squareReceiptUrl;
    public ?string $dateCreated;
    public string $status;
    public ?string $providerMeta;

    /**
     * @param int $id
     * @param int|null $entryId
     * @param int $formId
     * @param string $transactionId
     * @param string $paymentMethod
     * @param string|null $customerName
     * @param int $amount
     * @param string $currency
     * @param string|null $squareReceiptUrl
     * @param string|null $dateCreated
     * @param string|null $providerMeta
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        int $id,
        ?int $entryId,
        int $formId,
        string $transactionId,
        string $paymentMethod,
        ?string $customerName,
        int $amount,
        string $currency,
        ?string $squareReceiptUrl = null,
        ?string $dateCreated = null,
        ?string $providerMeta = null
    ) {
        $this->id = $id;
        $this->entryId = $entryId;
        $this->formId = $formId;
        $this->transactionId = $transactionId;
        $this->paymentMethod = $paymentMethod;
        $this->customerName = $customerName;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->squareReceiptUrl = $squareReceiptUrl;
        $this->dateCreated = $dateCreated;
        $this->status = 'succeeded';
        $this->providerMeta = $providerMeta;
    }

    public function withCustomerName(?string $customerName): self
    {
        $copy = new self(
            $this->id,
            $this->entryId,
            $this->formId,
            $this->transactionId,
            $this->paymentMethod,
            $customerName,
            $this->amount,
            $this->currency,
            $this->squareReceiptUrl,
            $this->dateCreated,
            $this->providerMeta
        );
        $copy->status = $this->status;

        return $copy;
    }

    public function withProviderMeta(?string $providerMeta): self
    {
        $copy = new self(
            $this->id,
            $this->entryId,
            $this->formId,
            $this->transactionId,
            $this->paymentMethod,
            $this->customerName,
            $this->amount,
            $this->currency,
            $this->squareReceiptUrl,
            $this->dateCreated,
            $providerMeta
        );
        $copy->status = $this->status;

        return $copy;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'entryId' => $this->entryId,
            'formId' => $this->formId,
            'transactionId' => $this->transactionId,
            'paymentMethod' => $this->paymentMethod,
            'customerName' => $this->customerName,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'squareReceiptUrl' => $this->squareReceiptUrl,
            'dateCreated' => $this->dateCreated,
            'status' => $this->status,
            'providerMeta' => $this->providerMeta,
        ];
    }
}
