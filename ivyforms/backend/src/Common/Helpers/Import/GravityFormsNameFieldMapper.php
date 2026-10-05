<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Map Gravity Forms name fields into IvyForms compound name fields.
 */
class GravityFormsNameFieldMapper
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
        $required = !empty($rawField['isRequired']);
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

        $inputs = is_array($rawField['inputs'] ?? null) ? $rawField['inputs'] : [];
        if (empty($inputs) || ($rawField['nameFormat'] ?? '') === 'simple') {
            self::appendSimpleField($rawField, $fields, $context, $parentIndex, $required);
            return;
        }

        self::appendAdvancedFields($inputs, $fields, $context, $parentIndex, $required);
    }

    /**
     * @param array<string, mixed> $rawField
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @param int $parentIndex
     * @param bool $required
     * @return void
     */
    private static function appendSimpleField(
        array $rawField,
        array &$fields,
        array &$context,
        int $parentIndex,
        bool $required
    ): void {
        self::appendChild(
            [
                'label'        => $rawField['label'] ?? '',
                'placeholder'  => $rawField['placeholder'] ?? '',
                'defaultValue' => $rawField['defaultValue'] ?? '',
            ],
            'nameField1',
            1,
            $fields,
            $context,
            $parentIndex,
            $required
        );
    }

    /**
     * @param array<int, mixed> $inputs
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @param int $parentIndex
     * @param bool $required
     * @return void
     */
    private static function appendAdvancedFields(
        array $inputs,
        array &$fields,
        array &$context,
        int $parentIndex,
        bool $required
    ): void {
        $nameTypeMap = [
            '3' => 'nameField1',
            '6' => 'nameField2',
            '4' => 'nameField3',
        ];
        $position = 1;

        foreach ($inputs as $input) {
            if (!is_array($input) || !empty($input['isHidden'])) {
                continue;
            }

            $inputIdParts = explode('.', (string) ($input['id'] ?? ''));
            $suffix = (string) end($inputIdParts);
            if (!isset($nameTypeMap[$suffix])) {
                continue;
            }

            self::appendChild(
                $input,
                $nameTypeMap[$suffix],
                $position++,
                $fields,
                $context,
                $parentIndex,
                $required
            );
        }
    }

    /**
     * @param array<string, mixed> $input
     * @param string $nameFieldType
     * @param int $position
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @param int $parentIndex
     * @param bool $required
     * @return void
     */
    private static function appendChild(
        array $input,
        string $nameFieldType,
        int $position,
        array &$fields,
        array &$context,
        int $parentIndex,
        bool $required
    ): void {
        $fields[] = [
            'id'           => $context['id']++,
            'fieldIndex'   => $parentIndex,
            'type'         => 'text',
            'label'        => sanitize_text_field((string) ($input['customLabel'] ?? $input['label'] ?? '')),
            'placeholder'  => sanitize_text_field((string) ($input['placeholder'] ?? '')),
            'required'     => $required,
            'defaultValue' => sanitize_text_field((string) ($input['defaultValue'] ?? '')),
            'parentId'     => $parentIndex,
            'position'     => $position,
            'settings'     => wp_json_encode([
                'nameFieldType' => $nameFieldType,
                'subFieldIndex' => $position,
            ]) ?: '',
        ];
    }
}
