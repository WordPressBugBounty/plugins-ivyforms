<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Build an IvyForms composite address field from third-party address parts.
 */
class ThirdPartyAddressFieldMapper
{
    /**
     * @param string $label
     * @param string $description
     * @param bool $required
     * @param array<int, array<string, mixed>> $parts
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @return void
     */
    public static function append(
        string $label,
        string $description,
        bool $required,
        array $parts,
        array &$fields,
        array &$context
    ): void {
        $parentIndex = $context['fieldIndex']++;
        $fields[] = [
            'id'           => $context['id']++,
            'fieldIndex'   => $parentIndex,
            'type'         => 'address',
            'label'        => sanitize_text_field($label),
            'placeholder'  => '',
            'description'  => sanitize_text_field($description),
            'required'     => $required,
            'defaultValue' => '',
            'parentId'     => null,
            'position'     => $context['position']++,
            'rowIndex'     => $context['rowIndex']++,
            'columnIndex'  => 0,
            'width'        => 100,
        ];

        $position = 1;
        foreach ($parts as $part) {
            if (($part['visible'] ?? true) === false) {
                continue;
            }

            $addressType = (string) ($part['type'] ?? '');
            $fields[] = [
                'id'           => $context['id']++,
                'fieldIndex'   => $parentIndex,
                'type'         => 'text',
                'label'        => sanitize_text_field((string) ($part['label'] ?? '')),
                'placeholder'  => sanitize_text_field((string) ($part['placeholder'] ?? '')),
                'required'     => $addressType === 'addressLine2' ? false : $required,
                'defaultValue' => sanitize_text_field((string) ($part['defaultValue'] ?? '')),
                'parentId'     => $parentIndex,
                'position'     => $position++,
                'visible'      => true,
                'settings'     => wp_json_encode(['addressFieldType' => $addressType]) ?: '',
            ];
        }
    }
}
