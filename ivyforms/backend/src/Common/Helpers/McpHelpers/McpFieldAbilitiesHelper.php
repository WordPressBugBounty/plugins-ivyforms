<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

use IvyForms\Services\Field\FieldType;

/**
 * Field-specific helpers used by IvyForms MCP abilities.
 */
class McpFieldAbilitiesHelper
{
    private const SUPPORTED_FIELD_TYPES = [
        'text',
        'textarea',
        'email',
        'number',
        'phone',
        'website',
        'name',
        'radio',
        'checkbox',
        'select',
        'multi-select',
        'address',
        'date',
        'time',
        'rating',
        'html',
        'slider',
        'file-upload',
        'gdpr',
        'product',
        'quantity',
        'total',
        'password',
        'signature',
        'date_time',
        'likert',
        'nps',
        'rich_text',
    ];

    /**
     * @param array<int, array<string, mixed>> $fieldInputs
     * @param array<string, mixed> $form
     * @return array<string, mixed>
     */
    public static function appendFieldsToForm(array $fieldInputs, int $formId, array $form): array
    {
        if (!is_array($form) || empty($form)) {
            return [
                'success' => false,
                'fields'  => [],
                'labels'  => [],
                'message' => "Form {$formId} not found.",
            ];
        }

        $existingFields = $form['fields'] ?? [];
        $maxPosition    = 0;
        $maxRowIndex    = 0;
        $maxFieldIndex  = 0;

        foreach ($existingFields as $field) {
            $maxPosition   = max($maxPosition, (int) ($field['position'] ?? 0));
            $maxRowIndex   = max($maxRowIndex, (int) ($field['rowIndex'] ?? 0));
            $maxFieldIndex = max($maxFieldIndex, (int) ($field['fieldIndex'] ?? 0));
        }

        $newFields = [];
        $labels = [];

        foreach ($fieldInputs as $fieldInput) {
            $appendResult = self::appendSingleFieldInput(
                $fieldInput,
                $form,
                $formId,
                $maxPosition,
                $maxRowIndex,
                $maxFieldIndex,
                $newFields,
                $labels
            );
            if ($appendResult !== null) {
                return $appendResult;
            }
        }

        return [
            'success' => true,
            'fields'  => array_merge($existingFields, $newFields),
            'labels'  => $labels,
            'message' => '',
        ];
    }

    /**
     * @param array<string, mixed> $field
     * @return array<string, mixed>
     */
    public static function duplicateFieldPayload(
        array $field,
        int $formId,
        int $newFieldIndex,
        int $newPosition,
        int $newRowIndex,
        bool $isChild
    ): array {
        $field['id'] = 0;
        $field['formId'] = $formId;
        $field['fieldIndex'] = $newFieldIndex;
        $field['position'] = $newPosition;
        $originalParentId = $field['parentId'] ?? null;
        $originalParentId = is_numeric($originalParentId) ? (int) $originalParentId : null;

        // When duplicating compound fields, callers may provide the duplicated parent ID
        // (e.g. `newParentId` / `duplicatedParentId`). If not, preserve the original parent ID.
        $newParentId = $field['newParentId'] ?? ($field['duplicatedParentId'] ?? null);
        $newParentId = is_numeric($newParentId) ? (int) $newParentId : null;

        $field['parentId'] = $isChild
            ? ($newParentId !== null && $newParentId > 0 ? $newParentId : $originalParentId)
            : null;

        if (!$isChild) {
            $field['rowIndex'] = $newRowIndex;
        }

        unset($field['newParentId'], $field['duplicatedParentId']);

        if (!empty($field['fieldOptions']) && is_array($field['fieldOptions'])) {
            $field['fieldOptions'] = self::duplicateFieldOptions($field['fieldOptions']);
        }

        return $field;
    }

    /**
     * @param mixed $fieldInput
     * @param array<string, mixed> $form
     * @param array<int, array<string, mixed>> $newFields
     * @param array<int, string> $labels
     * @return array<string, mixed>|null Error payload, or null on success (mutates counters and accumulators).
     */
    private static function appendSingleFieldInput(
        $fieldInput,
        array $form,
        int $formId,
        int &$maxPosition,
        int &$maxRowIndex,
        int &$maxFieldIndex,
        array &$newFields,
        array &$labels
    ): ?array {
        if (!is_array($fieldInput)) {
            return self::appendFieldsFailure('Each field input must be an object.');
        }

        $type = sanitize_text_field((string) ($fieldInput['type'] ?? ''));
        $validation = self::validateFieldType($type);
        if (!$validation['valid']) {
            return self::appendFieldsFailure($validation['message']);
        }

        $maxPosition++;
        $maxRowIndex++;
        $maxFieldIndex++;

        $pageIdResult = self::resolvePageIdForFieldInput($fieldInput, $form);
        if (isset($pageIdResult['error'])) {
            return self::appendFieldsFailure($pageIdResult['error']);
        }
        $fieldInput = $pageIdResult['input'];

        $payload = self::buildFieldPayload($fieldInput, $formId, $maxPosition, $maxRowIndex, $maxFieldIndex);
        $newFields = array_merge($newFields, $payload['fields']);
        $labels[] = $payload['label'];

        return null;
    }

    /**
     * @param array<string, mixed> $fieldInput
     * @param array<string, mixed> $form
     * @return array{input: array<string, mixed>}|array{error: string}
     */
    private static function resolvePageIdForFieldInput(array $fieldInput, array $form): array
    {
        if (!empty($fieldInput['pageId'])) {
            $resolvedPageId = McpMultiPageHelper::normalizePageIdForForm(
                (string) $fieldInput['pageId'],
                $form
            );
            if ($resolvedPageId === null) {
                return [
                    'error' => sprintf(
                        __('Invalid pageId "%s" for this form.', 'ivyforms'),
                        sanitize_text_field((string) $fieldInput['pageId'])
                    ),
                ];
            }

            $fieldInput['pageId'] = $resolvedPageId;

            return ['input' => $fieldInput];
        }

        $defaultPageId = McpMultiPageHelper::resolveDefaultPageIdForForm($form);
        if ($defaultPageId !== null) {
            $fieldInput['pageId'] = $defaultPageId;
        }

        return ['input' => $fieldInput];
    }

    /**
     * @return array<string, mixed>
     */
    private static function appendFieldsFailure(string $message): array
    {
        return [
            'success' => false,
            'fields'  => [],
            'labels'  => [],
            'message' => $message,
        ];
    }

    /**
     * @return array{valid: bool, message: string}
     */
    private static function validateFieldType(string $type): array
    {
        if (!in_array($type, self::SUPPORTED_FIELD_TYPES, true)) {
            return [
                'valid'   => false,
                'message' => sprintf('Field type "%s" is not supported by IvyForms MCP.', $type),
            ];
        }

        if (!FieldType::isValid($type)) {
            return [
                'valid'   => false,
                'message' => sprintf(
                    'Field type "%s" is not allowed for this site or license plan.',
                    $type
                ),
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private static function buildFieldPayload(
        array $input,
        int $formId,
        int $position,
        int $rowIndex,
        int $fieldIndex
    ): array {
        return McpFieldPayloadHelper::buildFieldPayload($input, $formId, $position, $rowIndex, $fieldIndex);
    }

    /**
     * @param array<int, array<string, mixed>> $fieldOptions
     * @return array<int, array<string, mixed>>
     */
    private static function duplicateFieldOptions(array $fieldOptions): array
    {
        return McpFieldPayloadHelper::duplicateFieldOptions($fieldOptions);
    }

    /**
     * Reorder/update layout state for form fields.
     *
     * This is the former implementation from:
     * `Infrastructure/WP/MCP/Abilities/IvyFormsFieldsReorderHelper.php`.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function reorderFormFields(array $input): array
    {
        return McpFieldReorderHelper::reorderFormFields($input);
    }
}
