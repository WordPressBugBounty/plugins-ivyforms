<?php

namespace IvyForms\Services\Payment;

use IvyForms\Repository\EntryField\EntryFieldNameEmailRepositoryInterface;

/**
 * Fills empty Payments customer cells from the linked entry's mapped Name/Email fields.
 */
class PaymentListCustomerName
{
    private EntryFieldNameEmailRepositoryInterface $entryFieldNameEmailRepository;

    public function __construct(EntryFieldNameEmailRepositoryInterface $entryFieldNameEmailRepository)
    {
        $this->entryFieldNameEmailRepository = $entryFieldNameEmailRepository;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    public function attach(array $rows): array
    {
        $neededEntryIds = [];
        foreach ($rows as $index => $row) {
            if (trim((string) ($row['customerName'] ?? '')) !== '' || (int) ($row['entryId'] ?? 0) <= 0) {
                unset($rows[$index]['formIntegrationSettings']);
                continue;
            }
            $neededEntryIds[(int) $row['entryId']][] = $index;
        }
        if ($neededEntryIds === []) {
            return $rows;
        }

        $valuesByEntry = $this->entryFieldNameEmailRepository->findNameEmailValuesByEntryIds(
            array_keys($neededEntryIds)
        );
        foreach ($neededEntryIds as $entryId => $indexes) {
            foreach ($indexes as $index) {
                $picked = $this->pick(
                    $this->gatewaySettingsFromRow($rows[$index]),
                    $valuesByEntry[$entryId] ?? []
                );
                $rows[$index]['customerName'] = $picked !== '' ? $picked : null;
                unset($rows[$index]['formIntegrationSettings']);
            }
        }

        return array_values($rows);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function gatewaySettingsFromRow(array $row): array
    {
        $all = $this->decodeIntegrationSettings($row['formIntegrationSettings'] ?? null);
        $method = (string) ($row['paymentMethod'] ?? '');
        if ($method !== '' && is_array($all[$method] ?? null)) {
            return $all[$method];
        }

        return [];
    }

    /**
     * @param array<string, mixed> $gatewaySettings
     * @param array<string, string> $entryValues
     */
    private function pick(array $gatewaySettings, array $entryValues): string
    {
        $fromName = $this->mappedOrPrefixed(
            $entryValues,
            (string) ($gatewaySettings['billingNameField'] ?? ''),
            'name_'
        );
        if ($fromName !== '') {
            return $fromName;
        }

        return $this->mappedOrPrefixed(
            $entryValues,
            (string) ($gatewaySettings['buyerEmailField'] ?? ''),
            'email_'
        );
    }

    /**
     * @param array<string, string> $values
     */
    private function mappedOrPrefixed(array $values, string $mappedKey, string $prefix): string
    {
        if ($mappedKey === '' || empty($values[$mappedKey])) {
            return '';
        }

        return $this->sanitizeByPrefix($prefix, $values[$mappedKey]);
    }

    private function sanitizeByPrefix(string $prefix, string $value): string
    {
        return $prefix === 'email_' ? sanitize_email($value) : sanitize_text_field($value);
    }

    /**
     * @param mixed $raw
     * @return array<string, mixed>
     */
    private function decodeIntegrationSettings($raw): array
    {
        $decoded = [];
        if (is_array($raw)) {
            $decoded = $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
        }
        if (!is_array($decoded)) {
            return [];
        }
        if (isset($decoded['integrationSettings']) && is_array($decoded['integrationSettings'])) {
            return $decoded['integrationSettings'];
        }

        return $decoded;
    }
}
