<?php

namespace IvyForms\ValueObjects\Payment;

/**
 * Immutable Square order record value object.
 */
final class Order
{
    public int $id;
    public int $entryId;
    public string $squareOrderId;
    public string $status;
    public ?string $squareCustomerId;
    public int $amount;
    public string $currency;
    public ?string $dateCreated;

    /**
     * @param int $id
     * @param int $entryId
     * @param string $squareOrderId
     * @param string $status
     * @param string|null $squareCustomerId
     * @param int $amount
     * @param string $currency
     * @param string|null $dateCreated
     */
    public function __construct(
        int $id,
        int $entryId,
        string $squareOrderId,
        string $status,
        ?string $squareCustomerId,
        int $amount,
        string $currency,
        ?string $dateCreated = null
    ) {
        $this->id = $id;
        $this->entryId = $entryId;
        $this->squareOrderId = $squareOrderId;
        $this->status = $status;
        $this->squareCustomerId = $squareCustomerId;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'entryId'          => $this->entryId,
            'squareOrderId'    => $this->squareOrderId,
            'status'           => $this->status,
            'squareCustomerId' => $this->squareCustomerId,
            'amount'           => $this->amount,
            'currency'         => $this->currency,
            'dateCreated'      => $this->dateCreated,
        ];
    }
}
