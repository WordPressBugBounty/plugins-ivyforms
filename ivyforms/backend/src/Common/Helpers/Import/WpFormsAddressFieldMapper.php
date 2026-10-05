<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Map a WPForms address field and its configured sub-fields.
 */
class WpFormsAddressFieldMapper
{
    /**
     * @param array<string, mixed> $rawField
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @return void
     */
    public static function append(array $rawField, array &$fields, array &$context): void
    {
        $parts = [
            self::part($rawField, 'address1', 'streetAddress', 'Address Line 1'),
            self::part($rawField, 'address2', 'addressLine2', 'Address Line 2'),
            self::part($rawField, 'city', 'city', 'City'),
            self::part($rawField, 'state', 'state', 'State / Province'),
            self::part($rawField, 'postal', 'zip', 'Zip / Postal Code'),
            self::part($rawField, 'country', 'country', 'Country'),
        ];
        $parts[5]['visible'] = ($rawField['scheme'] ?? 'us') === 'international'
            && empty($rawField['country_hide']);

        ThirdPartyAddressFieldMapper::append(
            (string) ($rawField['label'] ?? ''),
            (string) ($rawField['description'] ?? ''),
            !empty($rawField['required']),
            $parts,
            $fields,
            $context
        );
    }

    /**
     * @param array<string, mixed> $rawField
     * @param string $key
     * @param string $type
     * @param string $label
     * @return array<string, mixed>
     */
    private static function part(array $rawField, string $key, string $type, string $label): array
    {
        return [
            'type'         => $type,
            'label'        => $label,
            'placeholder'  => $rawField[$key . '_placeholder'] ?? '',
            'defaultValue' => $rawField[$key . '_default'] ?? '',
            'visible'      => empty($rawField[$key . '_hide']),
        ];
    }
}
