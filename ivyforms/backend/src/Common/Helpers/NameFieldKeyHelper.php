<?php

namespace IvyForms\Common\Helpers;

class NameFieldKeyHelper
{
    /**
     * Sort child field IDs by stored display order before positional fallback.
     *
     * @param array<int> $childIds
     * @param array<int, object> $fieldById
     * @return array<int>
     */
    public static function sortChildIdsByDisplayOrder(array $childIds, array $fieldById): array
    {
        $sortKeys = [];
        foreach ($childIds as $position => $childId) {
            $displayIndex = self::getSubFieldDisplayIndex($fieldById[$childId] ?? null);
            $sortKeys[] = [$displayIndex ?? PHP_INT_MAX, $position, $childId];
        }

        usort(
            $sortKeys,
            static function (array $left, array $right): int {
                if ($left[0] !== $right[0]) {
                    return $left[0] <=> $right[0];
                }

                return $left[1] <=> $right[1];
            }
        );

        return array_column($sortKeys, 2);
    }

    /**
     * Resolve the stable name subfield key stored on a child field.
     *
     * @param object|null $field
     * @return string|null
     */
    public static function resolveStoredNameSubfieldKey(?object $field): ?string
    {
        if ($field === null) {
            return null;
        }

        $nameFieldType = null;
        if (method_exists($field, 'getAdditionalProperty')) {
            $nameFieldType = $field->getAdditionalProperty('nameFieldType');
        }

        if (is_string($nameFieldType) && preg_match('/^nameField\d+$/i', $nameFieldType) === 1) {
            return $nameFieldType;
        }

        $label = null;
        if (method_exists($field, 'getFieldGeneralSettings')) {
            $label = $field->getFieldGeneralSettings()->getLabel();
        }

        return self::inferNameFieldKeyFromLabel($label);
    }

    /**
     * Infer a stable name subfield key from a field label.
     *
     * @param mixed $label
     * @return string|null
     */
    public static function inferNameFieldKeyFromLabel($label): ?string
    {
        if (!is_string($label) || trim($label) === '') {
            return null;
        }

        $trimmedLabel = trim($label);

        if (preg_match('/^nameField(\d+)$/i', $trimmedLabel, $matches) === 1) {
            return 'nameField' . $matches[1];
        }

        if (preg_match('/name\s*field\s*(\d+)/i', $trimmedLabel, $matches) === 1) {
            return 'nameField' . $matches[1];
        }

        return null;
    }

    /**
     * @param object|null $field
     * @return int|null
     */
    private static function getSubFieldDisplayIndex(?object $field): ?int
    {
        if ($field === null || !method_exists($field, 'getAdditionalProperty')) {
            return null;
        }

        $subFieldIndex = $field->getAdditionalProperty('subFieldIndex');
        if (is_int($subFieldIndex)) {
            return $subFieldIndex;
        }

        if (is_string($subFieldIndex) && trim($subFieldIndex) !== '' && is_numeric(trim($subFieldIndex))) {
            return (int)$subFieldIndex;
        }

        if (is_numeric($subFieldIndex)) {
            return (int)$subFieldIndex;
        }

        return null;
    }
}
