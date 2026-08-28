<?php

namespace IvyForms\Common\Helpers;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Services\Translations\BackendStrings;

class FormImportValidator
{
    /**
     * Validate top-level import payload and return forms array.
     *
     * @param mixed $importData
     *
     * @return array<int, array<string, mixed>>
     * @throws InvalidArgumentException
     */
    public static function validateImportPayload($importData): array
    {
        if (empty($importData)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['no_import_data_provided']
            );
        }

        if (!isset($importData['forms']) || !is_array($importData['forms'])) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_import_data_structure']
            );
        }

        foreach ($importData['forms'] as $formData) {
            self::validateSingleFormStructure($formData);
        }

        return $importData['forms'];
    }

    /**
     * Build a map of field database ID to fieldIndex.
     *
     * @param array<int, array<string, mixed>> $fields
     * @return array<int, int>
     */
    public static function buildFieldIdToIndexMap(array $fields): array
    {
        $idToIndexMap = [];

        foreach ($fields as $field) {
            if (isset($field['id']) && isset($field['fieldIndex'])) {
                $idToIndexMap[(int)$field['id']] = (int)$field['fieldIndex'];
            }
        }

        return $idToIndexMap;
    }

    /**
     * Prepare form data for export from entity toArray payload.
     *
     * @param mixed $form
     * @return array<string, mixed>
     */
    public static function prepareFormDataForExport($form): array
    {
        $formArray = $form->toArray();
        unset(
            $formArray['id'],
            $formArray['author'],
            $formArray['dateCreated'],
            $formArray['dateEdited'],
            $formArray['fields']
        );

        return $formArray;
    }

    /**
     * Remove style settings from an exported form payload.
     *
     * @param array<string, mixed> $formArray
     * @return array<string, mixed>
     */
    public static function stripStyleSettingsFromFormExport(array $formArray): array
    {
        unset($formArray['styleSettings']);

        return $formArray;
    }

    /**
     * Prepare fields data for export.
     *
     * @param array<int, array<string, mixed>> $fields
     * @param array<int, int> $idToIndexMap
     * @return array<int, array<string, mixed>>
     */
    public static function prepareFieldsDataForExport(array $fields, array $idToIndexMap): array
    {
        if (empty($fields)) {
            return [];
        }

        $exportFields = [];
        foreach ($fields as $fieldArray) {
            $exportFields[] = self::prepareFieldForExport($fieldArray, $idToIndexMap);
        }

        return $exportFields;
    }

    /**
     * Prepare a single field for export.
     *
     * @param array<string, mixed> $fieldArray
     * @param array<int, int> $idToIndexMap
     * @return array<string, mixed>
     */
    private static function prepareFieldForExport(array $fieldArray, array $idToIndexMap): array
    {
        $fieldArray = self::normalizeFieldData($fieldArray);
        unset($fieldArray['id'], $fieldArray['formId'], $fieldArray['position'], $fieldArray['rows']);

        $fieldArray['parentId'] = self::convertParentIdToFieldIndex($fieldArray, $idToIndexMap);
        $fieldArray['fieldOptions'] = self::prepareFieldOptionsForExport($fieldArray);

        return self::normalizeFieldData($fieldArray);
    }

    /**
     * Normalize malformed top-level numeric field keys into a flat associative array.
     *
     * @param array<string|int, mixed> $fieldData
     * @return array<string, mixed>
     */
    private static function normalizeFieldData(array $fieldData): array
    {
        $normalizedFieldData = [];

        foreach ($fieldData as $key => $value) {
            if (is_int($key)) {
                if (!is_array($value)) {
                    continue;
                }

                foreach ($value as $nestedKey => $nestedValue) {
                    if (is_string($nestedKey)) {
                        $normalizedFieldData[$nestedKey] = $nestedValue;
                    }
                }

                continue;
            }

            $normalizedFieldData[$key] = $value;
        }

        return $normalizedFieldData;
    }

    /**
     * Convert parent database ID to fieldIndex.
     *
     * @param array<string, mixed> $fieldArray
     * @param array<int, int> $idToIndexMap
     * @return int|null
     */
    private static function convertParentIdToFieldIndex(array $fieldArray, array $idToIndexMap): ?int
    {
        if (!array_key_exists('parentId', $fieldArray) || $fieldArray['parentId'] === null) {
            return null;
        }

        return $idToIndexMap[(int)$fieldArray['parentId']] ?? null;
    }

    /**
     * Prepare field options for export.
     *
     * @param array<string, mixed> $fieldArray
     * @return array<int, array<string, mixed>>|null
     */
    private static function prepareFieldOptionsForExport(array $fieldArray): ?array
    {
        if (empty($fieldArray['fieldOptions'])) {
            return null;
        }

        $exportFieldOptions = [];
        foreach ($fieldArray['fieldOptions'] as $option) {
            unset($option['id'], $option['fieldId']);
            $exportFieldOptions[] = $option;
        }

        return $exportFieldOptions;
    }

    /**
     * Prepare notifications data for export from entity toArray payload.
     *
     * @param array<int, mixed> $notifications
     * @return array<int, array<string, mixed>>
     */
    public static function prepareNotificationsDataForExport(array $notifications): array
    {
        if (empty($notifications)) {
            return [];
        }

        $exportNotifications = [];
        foreach ($notifications as $notification) {
            $notificationArray = $notification->toArray();
            unset($notificationArray['id'], $notificationArray['formId']);
            $exportNotifications[] = $notificationArray;
        }

        return $exportNotifications;
    }

    /**
     * Prepare confirmation data for export from entity toArray payload.
     *
     * @param mixed $confirmation
     * @return array<string, mixed>|null
     */
    public static function prepareConfirmationDataForExport($confirmation): ?array
    {
        if (!$confirmation) {
            return null;
        }

        $confirmationArray = $confirmation->toArray();
        unset($confirmationArray['id'], $confirmationArray['formId']);

        return $confirmationArray;
    }

    /**
     * Prepare form data payload for import.
     *
     * @param array<string, mixed> $formData
     * @param string $author
     * @param string $currentTime
     * @param string $submitLabel
     * @return array<string, mixed>
     */
    public static function prepareFormArrayForImport(
        array $formData,
        string $author,
        string $currentTime,
        string $submitLabel
    ): array {
        $formArray = $formData['form'] ?? [];
        $formArray['id'] = 0;
        $formArray['author'] = $author;
        $formArray['dateCreated'] = $currentTime;
        $formArray['dateEdited'] = $currentTime;

        if (!isset($formArray['integrationSettings'])) {
            $formArray['integrationSettings'] = '{}';
        }

        if (!isset($formArray['formActionButtons'])) {
            $formArray['formActionButtons'] = [
                'submitButtonSettings' => [
                    'label'    => $submitLabel,
                    'position' => 'default'
                ]
            ];
        }

        if (array_key_exists('styleSettings', $formArray)) {
            $sanitizedStyles = Sanitizer::sanitizeStyleSettings($formArray['styleSettings']);
            if ($sanitizedStyles === null) {
                unset($formArray['styleSettings']);
            }
            if ($sanitizedStyles !== null) {
                $formArray['styleSettings'] = $sanitizedStyles;
            }
        }

        return $formArray;
    }

    /**
     * Normalize imported fields before persistence.
     *
     * @param array<int, array<string, mixed>> $fieldsData
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeFieldsForImport(array $fieldsData): array
    {
        return array_map(static fn(array $fieldData): array => self::normalizeFieldData($fieldData), $fieldsData);
    }

    /**
     * Validate single form import structure.
     *
     * @param mixed $formData
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private static function validateSingleFormStructure($formData): void
    {
        self::assertArray(
            $formData,
            BackendStrings::getExceptionStrings()['invalid_import_data_structure']
        );

        self::assertRequiredArrayKey(
            $formData,
            'form',
            BackendStrings::getExceptionStrings()['import_structure_form_missing'],
            BackendStrings::getExceptionStrings()['import_structure_form_invalid']
        );

        self::assertRequiredArrayKey(
            $formData,
            'fields',
            BackendStrings::getExceptionStrings()['import_structure_fields_missing'],
            BackendStrings::getExceptionStrings()['import_structure_fields_invalid']
        );

        self::assertRequiredArrayKey(
            $formData,
            'notifications',
            BackendStrings::getExceptionStrings()['import_structure_notifications_missing'],
            BackendStrings::getExceptionStrings()['import_structure_notifications_invalid']
        );

        self::assertConfirmationStructure($formData);
    }

    /**
     * Assert that a value is an array.
     *
     * @param mixed $value
     * @param string $message
     * @return void
     * @throws InvalidArgumentException
     */
    private static function assertArray($value, string $message): void
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * Assert that a key exists and its value is an array.
     *
     * @param array<string, mixed> $payload
     * @param string $key
     * @param string $missingMessage
     * @param string $invalidMessage
     * @return void
     * @throws InvalidArgumentException
     */
    private static function assertRequiredArrayKey(
        array $payload,
        string $key,
        string $missingMessage,
        string $invalidMessage
    ): void {
        if (!array_key_exists($key, $payload)) {
            throw new InvalidArgumentException($missingMessage);
        }

        if (!is_array($payload[$key])) {
            throw new InvalidArgumentException($invalidMessage);
        }
    }

    /**
     * Assert confirmation section exists and has a supported type.
     *
     * @param array<string, mixed> $payload
     * @return void
     * @throws InvalidArgumentException
     */
    private static function assertConfirmationStructure(array $payload): void
    {
        if (!array_key_exists('confirmation', $payload)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['import_structure_confirmation_missing']
            );
        }

        if (!is_array($payload['confirmation']) && !is_object($payload['confirmation'])) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['import_structure_confirmation_invalid']
            );
        }
    }
}
