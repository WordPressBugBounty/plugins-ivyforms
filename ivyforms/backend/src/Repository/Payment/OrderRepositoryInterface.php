<?php

namespace IvyForms\Repository\Payment;

use IvyForms\Entity\Payment\Order;

interface OrderRepositoryInterface
{
    /**
     * @param Order $entity
     * @return int
     */
    public function add($entity): int;

    /**
     * @param string $squareOrderId
     * @return Order|null
     */
    public function getBySquareOrderId(string $squareOrderId): ?Order;

    /**
     * @param string $squareOrderId
     * @param string $status
     * @return bool
     */
    public function updateStatus(string $squareOrderId, string $status): bool;

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int;
}
