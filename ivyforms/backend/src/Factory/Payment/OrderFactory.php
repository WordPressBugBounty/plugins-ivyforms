<?php

namespace IvyForms\Factory\Payment;

use IvyForms\Entity\Payment\Order as OrderEntity;
use IvyForms\ValueObjects\Payment\Order as OrderValueObject;

/**
 * Factory for order entities.
 */
class OrderFactory
{
    /**
     * @param array<string, mixed> $data
     * @return OrderEntity
     */
    public static function create(array $data): OrderEntity
    {
        $orderObject = new OrderValueObject(
            (int) ($data['id'] ?? 0),
            (int) ($data['entryId'] ?? 0),
            (string) ($data['squareOrderId'] ?? ''),
            (string) ($data['status'] ?? 'OPEN'),
            isset($data['squareCustomerId']) ? (string) $data['squareCustomerId'] : null,
            (int) ($data['amount'] ?? 0),
            (string) ($data['currency'] ?? 'USD'),
            isset($data['dateCreated']) ? (string) $data['dateCreated'] : null
        );

        $order = new OrderEntity($orderObject);

        if (isset($data['id'])) {
            $order->setId((int) $data['id']);
        }

        if (isset($data['dateCreated'])) {
            $order->setDateCreated((string) $data['dateCreated']);
        }

        return $order;
    }
}
