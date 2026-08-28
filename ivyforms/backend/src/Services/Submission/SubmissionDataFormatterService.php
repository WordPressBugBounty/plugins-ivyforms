<?php

namespace IvyForms\Services\Submission;

use IvyForms\Common\Helpers\EntryHelper;
use IvyForms\Common\Helpers\FieldHelper;

/**
 * Submission Data Formatter Service
 *
 * Centralizes submission data formatting for use across all integrations (webhooks, emails, etc.)
 * Provides a standardized data structure that integrations can adapt as needed.
 *
 * @package IvyForms\Services\Submission
 */
class SubmissionDataFormatterService
{
    /**
     * @var SubmissionFieldFormatter
     */
    private SubmissionFieldFormatter $formatter;

    /**
     * @var SubmissionFieldTypeFormatter
     */
    private SubmissionFieldTypeFormatter $typeFormatter;

    /**
     * @var SubmissionIntegrationFieldValueResolver
     */
    private SubmissionIntegrationFieldValueResolver $fieldValueResolver;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->formatter           = new SubmissionFieldFormatter();
        $this->typeFormatter       = new SubmissionFieldTypeFormatter();
        $this->fieldValueResolver  = new SubmissionIntegrationFieldValueResolver();
    }

    /**
     * Format submission data into a standardized structure for integrations.
     *
     * @param array<int|string, mixed> $submissionData Raw submission data (keys are field database
     *     ids from sanitization; legacy 0-based indices still supported)
     * @param array<int, object> $formFields Array of Field objects
     * @return array{
     *   fields: array<string, mixed>,
     *   user_inputs: array<string, string>,
     *   field_metadata: array<string, array{id: int, type: string, label: string}>
     * }
     */
    public function formatForIntegrations(array $submissionData, array $formFields): array
    {
        // Build parent-children map and subfield keys (same as entry system)
        $parentChildrenMap = EntryHelper::buildParentChildrenMap($formFields);
        $parentSubfieldKeys = EntryHelper::buildParentSubfieldKeys(
            $parentChildrenMap,
            $submissionData,
            $formFields
        );
        $fieldIdToIndex = $this->buildFieldIdToIndexMap($formFields);

        // Build formatted data
        $fields = [];           // Original values (arrays/objects for complex fields)
        $userInputs = [];       // Formatted readable strings
        $fieldMetadata = [];    // Field information for integrations
        $typeCounters = [];     // Track count of each field type

        // Iterate through all fields (including subfields)
        foreach ($formFields as $field) {
            $result = $this->processFieldForIntegration(
                $field,
                $submissionData,
                $parentSubfieldKeys,
                $fieldIdToIndex,
                $typeCounters
            );

            if ($result !== null) {
                $fields[$result['key']] = $result['value'];
                $userInputs[$result['key']] = $result['userInput'];
                $fieldMetadata[$result['key']] = $result['metadata'];
            }
        }

        return [
            'fields' => $fields,
            'user_inputs' => $userInputs,
            'field_metadata' => $fieldMetadata,
        ];
    }

    /**
     * Build field ID to index map.
     *
     * @param array<object> $formFields
     * @return array<int, int>
     */
    private function buildFieldIdToIndexMap(array $formFields): array
    {
        $fieldIdToIndex = [];
        foreach ($formFields as $field) {
            $fieldIndex = $this->getFieldIndex($field);
            if ($fieldIndex !== null) {
                $fieldIdToIndex[$field->getId()] = $fieldIndex;
            }
        }
        return $fieldIdToIndex;
    }

    /**
     * Get field index (converted to 0-based).
     *
     * @param object $field
     * @return int|null
     */
    private function getFieldIndex(object $field): ?int
    {
        if (!method_exists($field, 'getIndex')) {
            return null;
        }

        $fieldIndex = $field->getIndex();
        return ($fieldIndex !== null && $fieldIndex > 0) ? $fieldIndex - 1 : null;
    }

    /**
     * Process a single field for integration formatting.
     *
     * @param object $field
     * @param array<int|string, mixed> $submissionData
     * @param array<int, string|null> $parentSubfieldKeys
     * @param array<int, int> $fieldIdToIndex
     * @param array<string, int> $typeCounters
     * @return array{key: string, value: mixed, userInput: string, metadata: array<string, mixed>}|null
     */
    private function processFieldForIntegration(
        object $field,
        array $submissionData,
        array $parentSubfieldKeys,
        array $fieldIdToIndex,
        array &$typeCounters
    ): ?array {
        $fieldType = $field->getType();
        $parentId = method_exists($field, 'getParentId') ? $field->getParentId() : null;

        // Skip security/CAPTCHA fields and subfields
        if (FieldHelper::isSecurityField($fieldType) || ($parentId !== null && $parentId > 0)) {
            return null;
        }

        $value = $this->fieldValueResolver->resolveFieldValue(
            $field,
            $submissionData,
            $parentSubfieldKeys,
            $fieldIdToIndex
        );

        // Skip empty values
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return null;
        }

        $fieldKey = $this->generateUniqueFieldKey($fieldType, $typeCounters);

        return [
            'key' => $fieldKey,
            'value' => $this->formatFieldValueByType($value, $fieldType, $field),
            'userInput' => $this->formatter->formatForDisplay($value, $fieldType),
            'metadata' => [
                'id' => $field->getId(),
                'type' => $fieldType,
                'label' => $this->getFieldLabel($field),
            ],
        ];
    }

    /**
     * Generate unique field key with counter.
     *
     * @param string $fieldType
     * @param array<string, int> $typeCounters
     * @return string
     */
    private function generateUniqueFieldKey(string $fieldType, array &$typeCounters): string
    {
        if (!isset($typeCounters[$fieldType])) {
            $typeCounters[$fieldType] = 0;
            return $fieldType;
        }

        $typeCounters[$fieldType]++;
        return $fieldType . '_' . $typeCounters[$fieldType];
    }

    /**
     * Get field label from field object.
     *
     * @param object $field
     * @return string
     */
    private function getFieldLabel(object $field): string
    {
        if (!method_exists($field, 'getFieldGeneralSettings')) {
            return '';
        }

        $generalSettings = $field->getFieldGeneralSettings();
        return ($generalSettings && method_exists($generalSettings, 'getLabel'))
            ? $generalSettings->getLabel()
            : '';
    }

    /**
     * Format a raw field value for the integration 'fields' root payload.
     *
     * Delegates to SubmissionFieldTypeFormatter which applies type-specific
     * transformations (phone stripping, date ISO conversion, etc.).
     *
     * @param mixed       $value Raw submission value
     * @param string      $type  Field type slug (e.g. 'phone', 'date', 'email')
     * @param object|null $field Field object for accessing field-level settings
     * @return mixed Formatted value
     */
    public function formatFieldValueByType($value, string $type, ?object $field = null)
    {
        return $this->typeFormatter->format($value, $type, $field);
    }
}
