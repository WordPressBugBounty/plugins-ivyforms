<?php

namespace IvyForms\Services\Payment;

use IvyForms\Entity\Payment\Order;
use IvyForms\Repository\Payment\OrderRepositoryInterface;

class OrderManager
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function createOrder(Order $order): int
    {
        return $this->orderRepository->add($order);
    }

    public function getOrderBySquareOrderId(string $squareOrderId): ?Order
    {
        return $this->orderRepository->getBySquareOrderId($squareOrderId);
    }

    public function updateOrderStatus(string $squareOrderId, string $status): bool
    {
        return $this->orderRepository->updateStatus($squareOrderId, $status);
    }
}
