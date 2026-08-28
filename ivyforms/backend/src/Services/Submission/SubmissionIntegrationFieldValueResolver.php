<?php

namespace IvyForms\Services\Submission;

/**
 * Resolves raw submission values for integration payloads (field id keys, subfields, compounds).
 *
 * @package IvyForms\Services\Submission
 */
final class SubmissionIntegrationFieldValueResolver
{
    /**
     * Resolve field value (mirrors entry system logic).
     *
     * @param object $field The field object
     * @param array<int|string, mixed> $submissionData Submission data keyed by field id
     *     (preferred) or legacy field index
     * @param array<int, string|null> $parentSubfieldKeys Map of child field IDs to subfield keys
     * @param array<int, int> $fieldIdToIndex Map of field IDs to field indices
     * @return mixed The resolved field value
     */
    public function resolveFieldValue(
        object $field,
        array $submissionData,
        array $parentSubfieldKeys,
        array $fieldIdToIndex
    ) {
        $parentId = method_exists($field, 'getParentId') ? $field->getParentId() : null;
        $fieldId = $field->getId();

        if ($this->isResolvableSubfield($parentId, $fieldId, $parentSubfieldKeys)) {
            return $this->resolveSubfieldValue(
                (int) $parentId,
                $fieldId,
                $parentSubfieldKeys,
                $fieldIdToIndex,
                $submissionData
            );
        }

        $fieldIndex = $fieldIdToIndex[$fieldId] ?? null;
        $compoundValue = $this->resolveCompoundFieldValue(
            $field->getType(),
            $parentId,
            $fieldIndex,
            $submissionData,
            $fieldId
        );

        if (is_array($compoundValue)) {
            return $compoundValue;
        }

        $byFieldId = $this->submissionValueForFieldIdKeys($submissionData, $fieldId);
        if ($byFieldId !== null) {
            return $byFieldId;
        }

        if ($fieldIndex !== null && array_key_exists($fieldIndex, $submissionData)) {
            return $submissionData[$fieldIndex];
        }

        return '';
    }

    /**
     * @param array<int|string, mixed> $submissionData
     * @return mixed|null Null when neither int nor string field id key exists
     */
    private function submissionValueForFieldIdKeys(array $submissionData, int $fieldId)
    {
        if (array_key_exists($fieldId, $submissionData)) {
            return $submissionData[$fieldId];
        }

        $fieldIdKey = (string)$fieldId;
        if (array_key_exists($fieldIdKey, $submissionData)) {
            return $submissionData[$fieldIdKey];
        }

        return null;
    }

    /**
     * Resolve subfield value from parent field.
     *
     * Call only when isResolvableSubfield() is true so boolean false submissions are not
     * confused with an "unresolved" sentinel.
     *
     * @param int $parentId Parent field id (> 0)
     * @param int $fieldId
     * @param array<int, string|null> $parentSubfieldKeys
     * @param array<int, int> $fieldIdToIndex
     * @param array<int|string, mixed> $submissionData
     * @return mixed|null Null when subfield key is empty or value is missing in submission data
     */
    private function resolveSubfieldValue(
        int $parentId,
        int $fieldId,
        array $parentSubfieldKeys,
        array $fieldIdToIndex,
        array $submissionData
    ) {
        $subKey = $parentSubfieldKeys[$fieldId];
        if ($subKey === null || $subKey === '') {
            return null;
        }

        return $this->pickSubfieldSubmissionValue($parentId, $subKey, $fieldIdToIndex, $submissionData);
    }

    /**
     * @param array<int, string|null> $parentSubfieldKeys
     */
    private function isResolvableSubfield(?int $parentId, int $fieldId, array $parentSubfieldKeys): bool
    {
        return $parentId !== null && $parentId > 0 && isset($parentSubfieldKeys[$fieldId]);
    }

    /**
     * @param array<int, int> $fieldIdToIndex
     * @param array<int|string, mixed> $submissionData
     * @return mixed|null
     */
    private function pickSubfieldSubmissionValue(
        int $parentId,
        string $subKey,
        array $fieldIdToIndex,
        array $submissionData
    ) {
        if ($this->submissionParentHasSubfieldKey($submissionData, $parentId, $subKey)) {
            return $submissionData[$parentId][$subKey];
        }

        return $this->readSubfieldFromLegacyParentRow($parentId, $subKey, $fieldIdToIndex, $submissionData);
    }

    /**
     * @param array<int|string, mixed> $submissionData
     */
    private function submissionParentHasSubfieldKey(array $submissionData, int $parentId, string $subKey): bool
    {
        $parentRow = $submissionData[$parentId] ?? null;

        return is_array($parentRow) && array_key_exists($subKey, $parentRow);
    }

    /**
     * @param array<int, int> $fieldIdToIndex
     * @param array<int|string, mixed> $submissionData
     * @return mixed|null
     */
    private function readSubfieldFromLegacyParentRow(
        int $parentId,
        string $subKey,
        array $fieldIdToIndex,
        array $submissionData
    ) {
        $parentFieldIndex = $fieldIdToIndex[$parentId] ?? null;
        if ($parentFieldIndex === null) {
            return null;
        }

        $legacyRow = $submissionData[$parentFieldIndex] ?? null;
        if (!is_array($legacyRow) || !array_key_exists($subKey, $legacyRow)) {
            return null;
        }

        return $legacyRow[$subKey];
    }

    /**
     * Resolve compound field value (name, address).
     *
     * @param string $fieldType
     * @param int|null $parentId
     * @param int|null $fieldIndex
     * @param array<int|string, mixed> $submissionData
     * @param int $fieldId Compound field (name/address) database id
     * @return array<mixed>|null
     */
    private function resolveCompoundFieldValue(
        string $fieldType,
        ?int $parentId,
        ?int $fieldIndex,
        array $submissionData,
        int $fieldId
    ) {
        if (!$this->isCompoundRootField($fieldType, $parentId)) {
            return null;
        }

        return $this->compoundFieldArrayFromSubmission($submissionData, $fieldId, $fieldIndex);
    }

    private function isCompoundRootField(string $fieldType, ?int $parentId): bool
    {
        if (!in_array($fieldType, ['name', 'address'], true)) {
            return false;
        }

        return $parentId === null || $parentId === 0;
    }

    /**
     * @param array<int|string, mixed> $submissionData
     * @return array<mixed>|null
     */
    private function compoundFieldArrayFromSubmission(
        array $submissionData,
        int $fieldId,
        ?int $fieldIndex
    ) {
        if (isset($submissionData[$fieldId]) && is_array($submissionData[$fieldId])) {
            return $submissionData[$fieldId];
        }

        if ($fieldIndex !== null && isset($submissionData[$fieldIndex]) && is_array($submissionData[$fieldIndex])) {
            return $submissionData[$fieldIndex];
        }

        return null;
    }
}
