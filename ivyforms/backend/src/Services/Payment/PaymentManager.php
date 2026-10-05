<?php

namespace IvyForms\Services\Payment;

use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Entity\Payment\Payment;
use IvyForms\Repository\Payment\PaymentRepositoryInterface;

class PaymentManager
{
    private PaymentRepositoryInterface $paymentRepository;

    private PaymentListCustomerName $paymentListCustomerName;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        PaymentListCustomerName $paymentListCustomerName
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->paymentListCustomerName = $paymentListCustomerName;
    }

    public function createPayment(Payment $payment): int
    {
        return $this->paymentRepository->add($payment);
    }

    public function getPaymentByEntryId(int $entryId): ?Payment
    {
        return $this->paymentRepository->getByEntryId($entryId);
    }

    public function paymentExistsByTransactionId(string $transactionId): bool
    {
        return $this->paymentRepository->existsByTransactionId($transactionId);
    }

    public function paymentAlreadyLinkedToEntry(string $transactionId): bool
    {
        $existing = $this->getByTransactionId($transactionId);

        return $existing !== null && (int) $existing->getEntryId() > 0;
    }

    public function getByTransactionId(string $transactionId): ?Payment
    {
        return $this->paymentRepository->getByTransactionId($transactionId);
    }

    public function getPaymentByTransactionId(string $transactionId): ?Payment
    {
        return $this->getByTransactionId($transactionId);
    }

    public function getPaymentByProviderOrderId(string $orderId): ?Payment
    {
        return $this->paymentRepository->findByProviderOrderId($orderId);
    }

    public function updatePaymentStatus(int $id, string $status): bool
    {
        return $this->paymentRepository->updateStatus($id, $status);
    }

    public function updatePayment(Payment $payment): void
    {
        $this->paymentRepository->update($payment);
    }

    /**
     * Insert or update a payment keyed by transactionId (Stripe PaymentIntent id).
     * Returns false when a succeeded payment is already linked to an entry.
     */
    public function upsertByTransactionId(Payment $payment): bool
    {
        $existing = $this->getByTransactionId($payment->getTransactionId());
        if ($existing === null) {
            return $this->insertOrReload($payment);
        }

        return $this->updateExistingPayment($existing, $payment);
    }

    private function insertOrReload(Payment $payment): bool
    {
        try {
            $this->createPayment($payment);

            return true;
        } catch (QueryExecutionException $e) {
            $existing = $this->getByTransactionId($payment->getTransactionId());
            if ($existing === null) {
                throw $e;
            }

            return $this->updateExistingPayment($existing, $payment);
        }
    }

    private function updateExistingPayment(Payment $existing, Payment $incoming): bool
    {
        if ($this->isDuplicateSucceeded($existing, $incoming)) {
            return false;
        }

        if ($existing->getStatus() === 'succeeded' && $incoming->getStatus() !== 'succeeded') {
            return true;
        }

        $this->mergePaymentUpdate($existing, $incoming);
        $this->updatePayment($existing);

        return true;
    }

    private function isDuplicateSucceeded(Payment $existing, Payment $incoming): bool
    {
        return $existing->getStatus() === 'succeeded'
            && $existing->getEntryId()
            && $incoming->getStatus() === 'succeeded'
            && $incoming->getEntryId();
    }

    private function mergePaymentUpdate(Payment $existing, Payment $incoming): void
    {
        $existing->setStatus($incoming->getStatus());
        if ($incoming->getEntryId()) {
            $existing->setEntryId($incoming->getEntryId());
        }
        if ($incoming->getSquareReceiptUrl()) {
            $existing->setSquareReceiptUrl($incoming->getSquareReceiptUrl());
        }
        if ($incoming->getProviderMeta()) {
            $existing->setProviderMeta($incoming->getProviderMeta());
        }
        if ($incoming->getCustomerName()) {
            $existing->setCustomerName($incoming->getCustomerName());
        }
    }

    /**
     * @param array<string, mixed> $criteria
     * @return array{items: array<int, array<string, mixed>>, total: int, meta: array<string, mixed>}
     */
    public function searchPayments(array $criteria): array
    {
        $result = $this->paymentRepository->search($criteria);
        $items = $this->paymentListCustomerName->attach($result['data'] ?? []);

        return [
            'items' => $items,
            'total' => (int) ($result['meta']['total'] ?? 0),
            'meta' => $result['meta'],
        ];
    }
}
