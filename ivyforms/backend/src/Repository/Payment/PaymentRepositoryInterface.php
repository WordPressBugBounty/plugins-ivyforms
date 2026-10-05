<?php

namespace IvyForms\Repository\Payment;

use IvyForms\Entity\Payment\Payment;

interface PaymentRepositoryInterface
{
    /**
     * @param Payment $entity
     * @return int
     */
    public function add($entity): int;

    /**
     * @param int $entryId
     * @return Payment|null
     */
    public function getByEntryId(int $entryId): ?Payment;

    public function existsByTransactionId(string $transactionId): bool;

    public function getByTransactionId(string $transactionId): ?Payment;

    public function findByProviderOrderId(string $orderId): ?Payment;

    public function updateStatus(int $id, string $status): bool;

    public function update(Payment $entity): void;

    /**
     * @param array<string, mixed>|null $params
     * @return array<string, mixed>
     */
    public function search(?array $params): array;

    /**
     * @param array<string, mixed>|null $params
     * @return int
     */
    public function getCount(?array $params): int;

    /**
     * @param int $id
     * @return int
     */
    public function delete(int $id): int;
}
