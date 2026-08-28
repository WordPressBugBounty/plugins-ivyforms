<?php

namespace IvyForms\Services\Entry\Managers;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Helpers\DateTimeHelper\DateHelper;
use IvyForms\Common\Helpers\DateTimeHelper\TimeHelper;
use IvyForms\Common\Helpers\EntryHelper;
use IvyForms\Services\Field\FieldService;

/**
 * Handles field value formatting and resolution.
 * Responsible for converting field values for display and processing submission data.
 */
class EntryFieldValueManager
{
    private FieldService $fieldService;

    public function __construct(FieldService $fieldService)
    {
        $this->fieldService = $fieldService;
    }

    /**
     * Format entry fields with values converted for frontend display.
     * (e.g., timestamp → HH:MM for time fields)
     *
     * @param array<int, array<string, mixed>> $entryFields
     * @param bool $skipFormatting
     * @return array<int, array<string, mixed>>
     * @SuppressWarnings(PHPMD)
     */
    public function formatForFrontend(array $entryFields, bool $skipFormatting = false): array
    {
        $fieldsById = $this->buildFieldsMap($entryFields);

        // Format each entry field value based on field type
        foreach ($entryFields as &$entryField) {
            $fieldId = (int)$entryField['fieldId'];
            if (!isset($fieldsById[$fieldId])) {
                continue;
            }

            $field = $fieldsById[$fieldId];
            $fieldType = strtolower($field->getType());
            $value = $entryField['fieldValue'];

            $entryField['fieldValue'] = $this->formatFieldValue($fieldType, $value, $skipFormatting, $field);
        }

        return $entryFields;
    }

    /**
     * Resolve the value for a field based on its type and parent/child relationship.
     *
     * @param object $field
     * @param array<mixed> $submissionData
     * @param array<int, array<int>> $parentChildrenMap
     * @param array<int, string|null> $parentSubfieldKeys
     * @return mixed
     */
    public function resolveFieldValue(
        object $field,
        array $submissionData,
        array $parentChildrenMap,
        array $parentSubfieldKeys
    ) {
        $fieldId = $field->getId();
        $fieldType = $field->getType();
        $parentId = method_exists($field, 'getParentId') ? $field->getParentId() : null;

        $parentValue = $this->getParentSubfieldValue($fieldId, $parentId, $submissionData, $parentSubfieldKeys);
        if ($parentValue !== null) {
            return $parentValue;
        }

        /**
         * Filters the resolved field value before standard processing.
         * Allows field-type-specific value resolution (e.g., password hashing).
         *
         * @since 0.1.0
         *
         * @param mixed|null $value The resolved value (null to continue with default processing).
         * @param object $field The field object.
         * @param mixed[] $submissionData The submission data array.
         * @param mixed[] $parentChildrenMap Map of parent to children field relationships.
         * @param mixed[] $parentSubfieldKeys Map of parent subfield keys.
         * @return mixed The resolved field value or null to continue default processing.
         */
        $customValue = apply_filters(
            'ivyforms/entry/field/resolve_value',
            null,
            $field,
            $submissionData,
            $parentChildrenMap,
            $parentSubfieldKeys
        );

        /**
         * Filters the resolved field value for a specific field type.
         * Allows dynamic hook registration per field type (e.g., 'ivyforms/entry/field/resolve_value/password').
         *
         * @since 0.1.0
         *
         * @param mixed|null $value The resolved value (null to continue with default processing).
         * @param object $field The field object.
         * @param mixed[] $submissionData The submission data array.
         * @param mixed[] $parentChildrenMap Map of parent to children field relationships.
         * @param mixed[] $parentSubfieldKeys Map of parent subfield keys.
         * @return mixed The resolved field value or null to continue default processing.
         */
        $customValue = apply_filters(
            "ivyforms/entry/field/resolve_value/{$fieldType}",
            $customValue,
            $field,
            $submissionData,
            $parentChildrenMap,
            $parentSubfieldKeys
        );

        if ($customValue !== null) {
            return $customValue;
        }

        return $this->resolveByType(
            $fieldType,
            $fieldId,
            $parentId,
            $submissionData,
            $parentChildrenMap,
            $parentSubfieldKeys
        );
    }

    /**
     * Resolve value based on field type.
     *
     * @param string $fieldType
     * @param int $fieldId
     * @param int|null $parentId
     * @param array<mixed> $submissionData
     * @param array<int, array<int>> $parentChildrenMap
     * @param array<int, string|null> $parentSubfieldKeys
     * @return mixed
     */
    private function resolveByType(
        string $fieldType,
        int $fieldId,
        ?int $parentId,
        array $submissionData,
        array $parentChildrenMap,
        array $parentSubfieldKeys
    ) {
        $isParent = $parentId === null || $parentId === 0;

        if ($isParent) {
            /**
             * Filters the resolved value for a parent field before compound resolution.
             * Allows Pro field types (e.g. likert) to resolve parent values without
             * hardcoding field types in Lite.
             *
             * @since 0.1.0
             *
             * @param mixed|null $value The resolved value (null to continue with default processing).
             * @param string $fieldType The field type.
             * @param int $fieldId The parent field ID.
             * @param mixed[] $parentChildrenMap Map of parent to children field relationships.
             * @param mixed[] $parentSubfieldKeys Map of parent subfield keys.
             * @param mixed[] $submissionData The submission data array.
             * @return mixed The resolved parent field value or null to continue default processing.
             */
            $parentValue = apply_filters(
                'ivyforms/entry/resolve_parent_field_value',
                null,
                $fieldType,
                $fieldId,
                $parentChildrenMap,
                $parentSubfieldKeys,
                $submissionData
            );

            if ($parentValue !== null) {
                return $parentValue;
            }
        }

        if (in_array($fieldType, ['name', 'address'], true) && $isParent) {
            return $this->resolveParentFieldValue(
                $fieldId,
                $parentChildrenMap,
                $parentSubfieldKeys,
                $submissionData
            );
        }

        if ($fieldType === 'time') {
            $value = EntryHelper::getDefaultFieldValue($fieldId, $submissionData);
            return TimeHelper::normalizeTimeValue($value);
        }

        if ($fieldType === 'date') {
            $value = EntryHelper::getDefaultFieldValue($fieldId, $submissionData);
            return DateHelper::normalizeDateValue($value);
        }

        return EntryHelper::getDefaultFieldValue($fieldId, $submissionData);
    }

    /**
     * Resolve value for a parent field (name, address).
     *
     * @param int $fieldId
     * @param array<int, array<int>> $parentChildrenMap
     * @param array<int, string|null> $parentSubfieldKeys
     * @param array<int|string, mixed> $submissionData
     * @return string
     */
    private function resolveParentFieldValue(
        int $fieldId,
        array $parentChildrenMap,
        array $parentSubfieldKeys,
        array $submissionData
    ): string {
        return $this->getCompoundFieldValue($fieldId, $parentChildrenMap, $parentSubfieldKeys, $submissionData);
    }

    /**
     * Build a map of field entities indexed by field ID.
     *
     * @param array<int, array<string, mixed>> $entryFields
     * @return array<int, object>
     */
    private function buildFieldsMap(array $entryFields): array
    {
        // Extract unique field IDs from entry fields
        $fieldIds = array_unique(array_column($entryFields, 'fieldId'));
        if (empty($fieldIds)) {
            return [];
        }

        // Single query to load only needed fields
        return $this->fieldService->getFieldsByIds($fieldIds);
    }

    /**
     * Format field value based on field type.
     *
     * @param string $fieldType
     * @param mixed $value
     * @param bool $skipFormatting
     * @param object|null $field
     * @return mixed
     * @SuppressWarnings(PHPMD)
     */
    private function formatFieldValue(string $fieldType, $value, bool $skipFormatting = false, ?object $field = null)
    {
        // Convert timestamp to HH:MM format for time fields
        if ($fieldType === 'time' && is_numeric($value)) {
            $value = TimeHelper::timestampToTime((int)$value);
        }

        // Convert timestamp to date format for date fields
        if ($fieldType === 'date' && is_numeric($value)) {
            $value = DateHelper::timestampToDate((int)$value);
        }

        /**
         * Filters the formatted field value for frontend display.
         * Allows field-type-specific formatting.
         *
         * @since 0.1.0
         *
         * @param mixed $value The field value to format.
         * @param string $fieldType The field type (lowercase).
         * @return mixed The formatted field value.
         */
        $value = apply_filters('ivyforms/entry/field/format_value', $value, $fieldType);

        /**
         * Filters the formatted field value for a specific field type.
         * Allows dynamic hook registration per field type.
         *
         * @since 0.1.0
         *
         * @param mixed $value The field value to format.
         * @param bool $skipFormatting Whether the field value should be formatted.
         * @param object|null $field The field entity object.
         * @return mixed The formatted field value.
         */
        return apply_filters("ivyforms/entry/field/format_value/{$fieldType}", $value, $skipFormatting, $field);
    }

    /**
     * Get the value of a parent subfield.
     *
     * @param int $fieldId
     * @param int|null $parentId
     * @param array<int|string, mixed> $submissionData
     * @param array<int, string|null> $parentSubfieldKeys
     * @return mixed|null
     */
    private function getParentSubfieldValue(
        int $fieldId,
        ?int $parentId,
        array $submissionData,
        array $parentSubfieldKeys
    ) {
        return EntryHelper::getParentSubfieldValue($fieldId, $parentId, $submissionData, $parentSubfieldKeys);
    }

    /**
     * Get the compound field value by combining child field values.
     *
     * @param int $fieldId
     * @param array<int, array<int>> $parentChildrenMap
     * @param array<int, string|null> $parentSubfieldKeys
     * @param array<int|string, mixed> $submissionData
     * @return string
     */
    private function getCompoundFieldValue(
        int $fieldId,
        array $parentChildrenMap,
        array $parentSubfieldKeys,
        array $submissionData
    ): string {
        return EntryHelper::getCompoundFieldValue($fieldId, $parentChildrenMap, $parentSubfieldKeys, $submissionData);
    }
}
