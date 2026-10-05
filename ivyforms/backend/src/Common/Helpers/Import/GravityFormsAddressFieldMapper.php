<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Map a Gravity Forms address field and its visible inputs.
 */
class GravityFormsAddressFieldMapper
{
    /**
     * @param array<string, mixed> $rawField
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @return void
     */
    public static function append(array $rawField, array &$fields, array &$context): void
    {
        $typeMap = [
            '1' => 'streetAddress',
            '2' => 'addressLine2',
            '3' => 'city',
            '4' => 'state',
            '5' => 'zip',
            '6' => 'country',
        ];
        $parts = [];

        foreach (($rawField['inputs'] ?? []) as $input) {
            if (!is_array($input)) {
                continue;
            }

            $inputIdParts = explode('.', (string) ($input['id'] ?? ''));
            $suffix = (string) end($inputIdParts);
            if (!isset($typeMap[$suffix])) {
                continue;
            }

            $parts[] = [
                'type'         => $typeMap[$suffix],
                'label'        => $input['label'] ?? '',
                'placeholder'  => $input['placeholder'] ?? '',
                'defaultValue' => $input['defaultValue'] ?? '',
                'visible'      => empty($input['isHidden']),
            ];
        }

        ThirdPartyAddressFieldMapper::append(
            (string) ($rawField['label'] ?? ''),
            (string) ($rawField['description'] ?? ''),
            !empty($rawField['isRequired']),
            $parts,
            $fields,
            $context
        );
    }
}
