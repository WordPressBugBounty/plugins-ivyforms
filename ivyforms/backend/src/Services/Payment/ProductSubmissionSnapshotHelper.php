<?php

namespace IvyForms\Services\Payment;

/**
 * Handles product submission snapshot parsing and normalization.
 *
 * Snapshot line priceAmount values are for entry display only — never use them
 * to compute charge amounts (see ProductSelectionSubtotalCalculator).
 */
class ProductSubmissionSnapshotHelper
{
    /**
     * @param mixed $rawValue
     * @return mixed
     */
    public function normalizeProductSubmissionValue($rawValue)
    {
        if (!is_string($rawValue)) {
            return $rawValue;
        }

        $trimmed = trim($rawValue);
        if ($trimmed === '' || ($trimmed[0] ?? '') !== '{') {
            return $rawValue;
        }

        $decoded = json_decode($trimmed, true);
        if (!is_array($decoded) || (int) ($decoded['v'] ?? 0) !== 1) {
            return $rawValue;
        }

        $productType = (string) ($decoded['productType'] ?? '');
        $raw = (string) ($decoded['raw'] ?? '');

        if ($productType === 'checkbox') {
            return array_values(
                array_filter(
                    array_map('trim', explode(',', $raw)),
                    static fn(string $part): bool => $part !== ''
                )
            );
        }

        return $raw !== '' ? $raw : $rawValue;
    }
}
