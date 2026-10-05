<?php

namespace IvyForms\Entity\Payment;

use IvyForms\ValueObjects\Payment\Payment as PaymentValueObject;

/**
 * Payment entity wrapping the value object.
 */
class Payment
{
    private PaymentValueObject $payment;

    public function __construct(PaymentValueObject $payment)
    {
        $this->payment = $payment;
    }

    public function getId(): int
    {
        return $this->payment->id;
    }

    public function setId(int $id): void
    {
        $this->payment->id = $id;
    }

    public function getEntryId(): ?int
    {
        return $this->payment->entryId;
    }

    public function getFormId(): int
    {
        return $this->payment->formId;
    }

    public function getTransactionId(): string
    {
        return $this->payment->transactionId;
    }

    public function getPaymentMethod(): string
    {
        return $this->payment->paymentMethod;
    }

    public function getCustomerName(): ?string
    {
        return $this->payment->customerName;
    }

    public function setCustomerName(?string $customerName): void
    {
        $this->payment = $this->payment->withCustomerName(
            $customerName !== null && $customerName !== '' ? $customerName : null
        );
    }

    public function getAmount(): int
    {
        return $this->payment->amount;
    }

    public function getCurrency(): string
    {
        return $this->payment->currency;
    }

    public function getSquareReceiptUrl(): ?string
    {
        return $this->payment->squareReceiptUrl;
    }

    public function getStatus(): string
    {
        return $this->payment->status;
    }

    public function setStatus(string $status): void
    {
        $this->payment->status = $status;
    }

    public function getProviderMeta(): ?string
    {
        return $this->payment->providerMeta;
    }

    public function setProviderMeta(?string $providerMeta): void
    {
        $this->payment = $this->payment->withProviderMeta($providerMeta);
    }

    public function setEntryId(?int $entryId): void
    {
        $this->payment->entryId = $entryId !== null && $entryId > 0 ? $entryId : null;
    }

    public function setSquareReceiptUrl(?string $receiptUrl): void
    {
        $this->payment->squareReceiptUrl = $receiptUrl !== null && $receiptUrl !== ''
            ? $receiptUrl
            : null;
    }

    public function getDateCreated(): ?string
    {
        return $this->payment->dateCreated;
    }

    public function setDateCreated(string $dateCreated): void
    {
        $this->payment->dateCreated = $dateCreated;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->payment->toArray();
    }
}
