<?php

namespace IvyForms\Entity\Payment;

use IvyForms\ValueObjects\Payment\Order as OrderValueObject;

/**
 * Order entity wrapping the value object.
 */
class Order
{
    private OrderValueObject $order;

    public function __construct(OrderValueObject $order)
    {
        $this->order = $order;
    }

    public function getId(): int
    {
        return $this->order->id;
    }

    public function setId(int $id): void
    {
        $this->order->id = $id;
    }

    public function getEntryId(): int
    {
        return $this->order->entryId;
    }

    public function getSquareOrderId(): string
    {
        return $this->order->squareOrderId;
    }

    public function getStatus(): string
    {
        return $this->order->status;
    }

    public function setStatus(string $status): void
    {
        $this->order->status = $status;
    }

    public function getSquareCustomerId(): ?string
    {
        return $this->order->squareCustomerId;
    }

    public function getAmount(): int
    {
        return $this->order->amount;
    }

    public function getCurrency(): string
    {
        return $this->order->currency;
    }

    public function getDateCreated(): ?string
    {
        return $this->order->dateCreated;
    }

    public function setDateCreated(string $dateCreated): void
    {
        $this->order->dateCreated = $dateCreated;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->order->toArray();
    }
}
