<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Map WPForms name fields into IvyForms compound name fields.
 */
class WpFormsNameFieldMapper
{
    /**
     * @param array<string, mixed> $rawField
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @return void
     */
    public static function append(array $rawField, array &$fields, array &$context): void
    {
        $parentIndex = $context['fieldIndex']++;
        $required = !empty($rawField['required']);
        $fields[] = [
            'id'           => $context['id']++,
            'fieldIndex'   => $parentIndex,
            'type'         => 'name',
            'label'        => sanitize_text_field((string) ($rawField['label'] ?? '')),
            'placeholder'  => '',
            'description'  => sanitize_text_field((string) ($rawField['description'] ?? '')),
            'required'     => $required,
            'defaultValue' => '',
            'parentId'     => null,
            'position'     => $context['position']++,
            'rowIndex'     => $context['rowIndex']++,
            'columnIndex'  => 0,
            'width'        => 100,
        ];

        self::appendSubFields($rawField, $fields, $context, $parentIndex, $required);
    }

    /**
     * @param array<string, mixed> $rawField
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @param int $parentIndex
     * @param bool $required
     * @return void
     */
    private static function appendSubFields(
        array $rawField,
        array &$fields,
        array &$context,
        int $parentIndex,
        bool $required
    ): void {
        $format = (string) ($rawField['format'] ?? 'first-last');
        $parts = $format === 'simple'
            ? [['simple', 'nameField1']]
            : [['first', 'nameField1'], ['last', 'nameField2']];

        if ($format === 'first-middle-last') {
            array_splice($parts, 1, 0, [['middle', 'nameField3']]);
        }

        foreach ($parts as $position => $part) {
            [$key, $nameFieldType] = $part;
            $subFieldIndex = $position + 1;
            $fields[] = [
                'id'           => $context['id']++,
                'fieldIndex'   => $parentIndex,
                'type'         => 'text',
                'label'        => sanitize_text_field(ucfirst($key)),
                'placeholder'  => sanitize_text_field((string) ($rawField[$key . '_placeholder'] ?? '')),
                'required'     => $key === 'middle' ? false : $required,
                'defaultValue' => sanitize_text_field((string) ($rawField[$key . '_default'] ?? '')),
                'parentId'     => $parentIndex,
                'position'     => $subFieldIndex,
                'settings'     => wp_json_encode([
                    'nameFieldType' => $nameFieldType,
                    'subFieldIndex' => $subFieldIndex,
                ]) ?: '',
            ];
        }
    }
}
