<?php

namespace IvyForms\Services\Payment;

use IvyForms\Common\Helpers\EntryHelper;

/**
 * Resolves mapped form field values for Square/Stripe/PayPal buyer info.
 */
class SquareBuyerSubmissionValues
{
    /**
     * @param array<string, mixed> $submissionByTypeIndex
     * @param array<int, object> $formFields
     * @param array<int|string, mixed> $submissionData
     * @return mixed
     */
    public static function mappedValue(
        string $key,
        array $submissionByTypeIndex,
        array $formFields,
        array $submissionData
    ) {
        if ($key === '') {
            return null;
        }

        if (array_key_exists($key, $submissionByTypeIndex)) {
            return self::decodeValue($submissionByTypeIndex[$key]);
        }

        return self::valueFromFieldId($key, $formFields, $submissionData);
    }

    /**
     * @param mixed $nameValue
     * @param array<int, object> $formFields
     * @param array<int|string, mixed> $submissionData
     * @return array{first_name: string, last_name: string}|null
     */
    public static function nameParts($nameValue, string $nameKey, array $formFields, array $submissionData): ?array
    {
        $fromValue = self::namePartsFromSubmissionValue($nameValue);
        if ($fromValue !== null) {
            return $fromValue;
        }

        return self::namePartsFromCompoundField($nameKey, $formFields, $submissionData);
    }

    /**
     * @param mixed $nameValue
     * @return array{first_name: string, last_name: string}|null
     */
    private static function namePartsFromSubmissionValue($nameValue): ?array
    {
        $nameValue = self::decodeValue($nameValue);
        if (is_string($nameValue) || is_numeric($nameValue)) {
            return self::namePartsFromString((string) $nameValue);
        }

        if (!is_array($nameValue)) {
            return null;
        }

        $first = self::firstPresent($nameValue, ['first', 'first_name', 'given_name', 'nameField1']);
        $last = self::firstPresent($nameValue, ['last', 'last_name', 'surname', 'nameField2']);
        if ($first === '' && $last === '') {
            $parts = self::flattenedNameParts($nameValue);
            $first = $parts[0] ?? '';
            $last = implode(' ', array_slice($parts, 1));
        }

        if ($first === '' && $last === '') {
            return null;
        }

        return [
            'first_name' => $first,
            'last_name' => $last,
        ];
    }

    /**
     * @return array{first_name: string, last_name: string}|null
     */
    private static function namePartsFromString(string $nameValue): ?array
    {
        $full = sanitize_text_field(trim($nameValue));

        return $full !== '' ? ['first_name' => $full, 'last_name' => ''] : null;
    }

    /**
     * @param array<int, object> $formFields
     * @param array<int|string, mixed> $submissionData
     * @return array{first_name: string, last_name: string}|null
     */
    private static function namePartsFromCompoundField(
        string $nameKey,
        array $formFields,
        array $submissionData
    ): ?array {
        $field = self::fieldForTypeIndexKey($nameKey, $formFields);
        if ($field === null || !method_exists($field, 'getId')) {
            return null;
        }

        $parentChildrenMap = EntryHelper::buildParentChildrenMap($formFields);
        $name = trim(EntryHelper::getCompoundFieldValue(
            $field->getId(),
            $parentChildrenMap,
            EntryHelper::buildParentSubfieldKeys($parentChildrenMap, $submissionData, $formFields),
            $submissionData
        ));

        return $name !== '' ? ['first_name' => $name, 'last_name' => ''] : null;
    }

    /**
     * @param array<int, object> $formFields
     * @param array<int|string, mixed> $submissionData
     * @return mixed
     */
    private static function valueFromFieldId(string $key, array $formFields, array $submissionData)
    {
        $field = self::fieldForTypeIndexKey($key, $formFields);
        $fieldId = $field !== null && method_exists($field, 'getId') ? $field->getId() : null;
        if ($fieldId === null) {
            return array_key_exists($key, $submissionData) ? self::decodeValue($submissionData[$key]) : null;
        }

        if (array_key_exists($fieldId, $submissionData)) {
            return self::decodeValue($submissionData[$fieldId]);
        }

        if (array_key_exists((string) $fieldId, $submissionData)) {
            return self::decodeValue($submissionData[(string) $fieldId]);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $nameValue
     * @param list<string> $keys
     */
    private static function firstPresent(array $nameValue, array $keys): string
    {
        foreach ($keys as $key) {
            if (!isset($nameValue[$key])) {
                continue;
            }

            $value = sanitize_text_field((string) $nameValue[$key]);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    /**
     * @param array<int|string, mixed> $nameValue
     * @return list<string>
     */
    private static function flattenedNameParts(array $nameValue): array
    {
        $parts = [];
        foreach ($nameValue as $part) {
            if (!is_string($part) && !is_numeric($part)) {
                continue;
            }

            $normalized = sanitize_text_field(trim((string) $part));
            if ($normalized !== '') {
                $parts[] = $normalized;
            }
        }

        return $parts;
    }

    /**
     * @param array<int, object> $formFields
     */
    private static function fieldForTypeIndexKey(string $key, array $formFields): ?object
    {
        foreach ($formFields as $field) {
            if (!method_exists($field, 'getType') || !method_exists($field, 'getIndex')) {
                continue;
            }

            if ((string) $field->getType() . '_' . (int) $field->getIndex() === $key) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function decodeValue($value)
    {
        if (is_object($value)) {
            return get_object_vars($value);
        }

        if (!is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);
        if ($trimmed === '' || ($trimmed[0] !== '{' && $trimmed[0] !== '[')) {
            return $value;
        }

        $decoded = json_decode($trimmed, true);

        return is_array($decoded) ? $decoded : $value;
    }
}
