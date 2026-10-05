<?php

namespace IvyForms\Factory\Payment;

use IvyForms\Entity\Payment\Payment as PaymentEntity;
use IvyForms\ValueObjects\Payment\Payment as PaymentValueObject;

/**
 * Factory for payment entities.
 */
class PaymentFactory
{
    /**
     * @param array<string, mixed> $data
     * @return PaymentEntity
     */
    public static function create(array $data): PaymentEntity
    {
        $entryId = null;
        if (array_key_exists('entryId', $data) && $data['entryId'] !== null && $data['entryId'] !== '') {
            $entryId = (int) $data['entryId'];
            if ($entryId <= 0) {
                $entryId = null;
            }
        }

        $paymentObject = new PaymentValueObject(
            (int) ($data['id'] ?? 0),
            $entryId,
            (int) ($data['formId'] ?? 0),
            (string) ($data['transactionId'] ?? ''),
            (string) ($data['paymentMethod'] ?? 'square'),
            isset($data['customerName']) && $data['customerName'] !== ''
                ? (string) $data['customerName']
                : null,
            (int) ($data['amount'] ?? 0),
            (string) ($data['currency'] ?? 'USD'),
            isset($data['squareReceiptUrl']) ? (string) $data['squareReceiptUrl'] : null,
            isset($data['dateCreated']) ? (string) $data['dateCreated'] : null,
            isset($data['providerMeta']) && $data['providerMeta'] !== ''
                ? (string) $data['providerMeta']
                : null
        );

        $payment = new PaymentEntity($paymentObject);
        $payment->setStatus((string) ($data['status'] ?? 'succeeded'));

        if (isset($data['id'])) {
            $payment->setId((int) $data['id']);
        }

        if (isset($data['dateCreated'])) {
            $payment->setDateCreated((string) $data['dateCreated']);
        }

        return $payment;
    }
}
