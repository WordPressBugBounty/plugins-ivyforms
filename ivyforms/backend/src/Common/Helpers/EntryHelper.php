<?php

namespace IvyForms\Common\Helpers;

use IvyForms\Entity\Entry\Entry;
use IvyForms\Services\Security\Common\IpDetectionService;

class EntryHelper
{
    /**
     * Get the user agent string.
     *
     * @return string
     */
    public static function getUserAgent(): string
    {
        $userAgent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
        return $userAgent ?: '';
    }

    /**
     * Get the source URL.
     *
     * @return string|null
     */
    public static function getSourceURL(): ?string
    {
        $sourceURL = filter_input(INPUT_SERVER, 'HTTP_REFERER');
        return $sourceURL ?: '';
    }

    /**
     * Get the browser name from the user agent string.
     *
     * @param string $userAgent
     * @return string
     */
    public static function getBrowserName(string $userAgent): string
    {
        if (str_contains($userAgent, 'Firefox')) {
            return 'Mozilla Firefox';
        } elseif (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg')) {
            return 'Google Chrome';
        } elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome')) {
            return 'Apple Safari';
        } elseif (str_contains($userAgent, 'Edg')) {
            return 'Microsoft Edge';
        } elseif (str_contains($userAgent, 'MSIE') || str_contains($userAgent, 'Trident')) {
            return 'Internet Explorer';
        }
        return 'Unknown';
    }

    /**
     * Build entry data array for form submissions.
     *
     * @param int $formId
     * @return array<string, mixed>
     */
    public static function buildEntryData(int $formId): array
    {
        return [
            'formId'        => $formId,
            'userId'        => get_current_user_id() ?: null,
            'status'        => Entry::DEFAULT_STATUS,
            'ipAddress'     => IpDetectionService::getUserIpAddress(),
            'userAgent'     => self::getUserAgent(),
            'sourceURL'     => self::getSourceURL(),
        ];
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
    public static function getParentSubfieldValue(
        int $fieldId,
        ?int $parentId,
        array $submissionData,
        array $parentSubfieldKeys
    ) {
        if (!$parentId || !isset($parentSubfieldKeys[$fieldId])) {
            return null;
        }

        $subKey = $parentSubfieldKeys[$fieldId];
        $parentValue = self::decodeSubmissionValue($submissionData[$parentId] ?? []);

        if (!isset($parentValue[$subKey])) {
            return null;
        }

        $val = $parentValue[$subKey];

        return is_array($val) ? implode(', ', $val) : $val;
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
    public static function getCompoundFieldValue(
        int $fieldId,
        array $parentChildrenMap,
        array $parentSubfieldKeys,
        array $submissionData
    ): string {
        if (empty($parentChildrenMap[$fieldId])) {
            return '';
        }

        $parentValue = self::decodeSubmissionValue($submissionData[$fieldId] ?? []);
        $childValues = [];

        foreach ($parentChildrenMap[$fieldId] as $childId) {
            $subKey = $parentSubfieldKeys[$childId] ?? null;
            if ($subKey !== null && isset($parentValue[$subKey]) && $parentValue[$subKey] !== '') {
                $val = $parentValue[$subKey];
                $childValues[] = is_array($val) ? implode(', ', $val) : $val;
            }
        }

        return implode(' ', $childValues);
    }

    /**
     * Decode a submission value into an array.
     * Handles JSON strings (e.g., Likert submissions) and ensures an array is returned.
     *
     * @param mixed $value
     * @return array<string, mixed>
     */
    public static function decodeSubmissionValue($value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Get the default field value from submission data.
     *
     * @param int $fieldId
     * @param array<int|string, mixed> $submissionData
     * @return mixed|string
     */
    public static function getDefaultFieldValue(int $fieldId, array $submissionData)
    {
        return $submissionData[$fieldId] ?? '';
    }

    /**
     * Build parent-children map from form fields.
     *
     * @param array<int, object> $formFields
     * @return array<int, array<int>>
     */
    public static function buildParentChildrenMap(array $formFields): array
    {
        $parentChildrenMap = [];
        foreach ($formFields as $field) {
            $parentId = method_exists($field, 'getParentId') ? $field->getParentId() : null;
            if ($parentId && $parentId > 0) {
                $parentChildrenMap[$parentId][] = $field->getId();
            }
        }
        return $parentChildrenMap;
    }

    /**
     * Build subfield key map for each parent.
     *
     * @param array<int, array<int>> $parentChildrenMap
     * @param array<mixed> $submissionData
     * @param array<int, object> $formFields
     * @return array<int, string|null>
     */
    public static function buildParentSubfieldKeys(
        array $parentChildrenMap,
        array $submissionData,
        array $formFields = []
    ): array {
        $fieldById = [];
        foreach ($formFields as $field) {
            if (method_exists($field, 'getId')) {
                $fieldById[$field->getId()] = $field;
            }
        }

        $parentSubfieldKeys = [];
        foreach ($parentChildrenMap as $parentId => $childIds) {
            $parentValue = self::decodeSubmissionValue($submissionData[$parentId] ?? []);
            $keys = array_keys($parentValue);
            $orderedChildIds = NameFieldKeyHelper::sortChildIdsByDisplayOrder($childIds, $fieldById);
            foreach ($orderedChildIds as $i => $childId) {
                $subKey = NameFieldKeyHelper::resolveStoredNameSubfieldKey($fieldById[$childId] ?? null);
                if ($subKey === null) {
                    $subKey = $keys[$i] ?? null;
                }
                $parentSubfieldKeys[$childId] = $subKey;
            }
        }
        return $parentSubfieldKeys;
    }
}
