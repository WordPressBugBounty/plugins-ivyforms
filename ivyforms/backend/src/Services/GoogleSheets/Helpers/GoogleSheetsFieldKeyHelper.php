<?php

namespace IvyForms\Services\GoogleSheets\Helpers;

use IvyForms\Common\Helpers\FieldHelper;

/**
 * Maps form-builder placeholder keys (type_fieldIndex) to submission formatter keys (type, type_1, ...).
 */
class GoogleSheetsFieldKeyHelper
{
    /**
     * @param array<mixed> $formFields
     * @param array<string, mixed> $fieldData
     * @return array<string, mixed>
     */
    public static function buildFieldIndexAliases(array $formFields, array $fieldData): array
    {
        $aliases = [];
        $typeCounters = [];

        foreach ($formFields as $field) {
            if (!self::isRootFormField($field)) {
                continue;
            }

            $type = $field->getType();

            if (!isset($typeCounters[$type])) {
                $typeCounters[$type] = 0;
            }

            $count = $typeCounters[$type];
            $alias = self::buildAliasForField($field, $type, $count, $fieldData);

            if ($alias !== null) {
                $aliases = array_merge($aliases, $alias);
            }

            $typeCounters[$type]++;
        }

        return $aliases;
    }

    /**
     * @param mixed $field
     */
    private static function isRootFormField($field): bool
    {
        if (!is_object($field) || !method_exists($field, 'getType')) {
            return false;
        }

        if (FieldHelper::isSecurityField($field->getType())) {
            return false;
        }

        return !(method_exists($field, 'getParentId') && $field->getParentId() > 0);
    }

    /**
     * @param mixed $field
     * @param array<string, mixed> $fieldData
     * @return array<string, mixed>|null
     */
    private static function buildAliasForField($field, string $type, int $count, array $fieldData): ?array
    {
        if (!method_exists($field, 'getIndex')) {
            return null;
        }

        $fieldIndex = $field->getIndex();

        if ($fieldIndex === null) {
            return null;
        }

        $counterKey = $count === 0 ? $type : "{$type}_{$count}";
        $fieldIndexKey = "{$type}_{$fieldIndex}";

        if (!array_key_exists($counterKey, $fieldData)) {
            return null;
        }

        return [$fieldIndexKey => $fieldData[$counterKey]];
    }
}
