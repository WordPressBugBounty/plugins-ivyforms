<?php

namespace IvyForms\Services\Submission;

/**
 * Submission Field Formatter
 *
 * Handles formatting of field values for display in integrations.
 *
 * @package IvyForms\Services\Submission
 */
class SubmissionFieldFormatter
{
    /**
     * Format field value for display (convert complex values to readable strings).
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    public function formatForDisplay($value, string $type): string
    {
        // Strip whitespace via the single canonical implementation in
        // SubmissionFieldTypeFormatter so the rule is defined in one place.
        if ($type === 'phone' && is_string($value)) {
            return SubmissionFieldTypeFormatter::stripPhoneWhitespace($value);
        }

        // Handle compound field types (name, address) - format as readable string
        if (in_array($type, ['name', 'address'], true) && is_array($value)) {
            return $type === 'name'
                ? $this->formatNameField($value)
                : $this->formatAddressField($value);
        }

        // Handle other array types (checkboxes, multi-select) - join with comma
        if (is_array($value)) {
            $filtered = array_filter($value, function ($fieldValue) {
                return $fieldValue !== '' && $fieldValue !== null;
            });
            return implode(', ', $filtered);
        }

        return (string)$value;
    }

    /**
     * Format name field as readable string.
     *
     * @param array<string, mixed> $value
     * @return string
     */
    private function formatNameField(array $value): string
    {
        $values = array_filter($value, function ($fieldValue) {
            return $fieldValue !== '' && $fieldValue !== null;
        });

        if (count($values) < 2) {
            return implode(' ', $values);
        }

        $valuesList = array_values($values);
        $lastName = $valuesList[1] ?? '';
        $firstName = $valuesList[0] ?? '';
        $middle = isset($valuesList[2]) ? ' ' . implode(' ', array_slice($valuesList, 2)) : '';

        return $lastName . ' ' . $firstName . $middle;
    }

    /**
     * Format address field as readable string.
     *
     * @param array<string, mixed> $value
     * @return string
     */
    private function formatAddressField(array $value): string
    {
        $parts = [];

        if (!empty($value['streetAddress'])) {
            $parts[] = $value['streetAddress'];
        }
        if (!empty($value['addressLine2'])) {
            $parts[] = $value['addressLine2'];
        }

        $cityStateZip = $this->buildCityStateZip($value);
        if ($cityStateZip) {
            $parts[] = $cityStateZip;
        }

        if (!empty($value['country'])) {
            $parts[] = $value['country'];
        }

        return implode("\n", $parts);
    }

    /**
     * Build city, state, zip line.
     *
     * @param array<string, mixed> $value
     * @return string
     */
    private function buildCityStateZip(array $value): string
    {
        $parts = array_filter([
            $value['city'] ?? '',
            $value['state'] ?? '',
            $value['zip'] ?? ''
        ], function ($part) {
            return $part !== '' && $part !== null;
        });

        return implode(', ', $parts);
    }
}
