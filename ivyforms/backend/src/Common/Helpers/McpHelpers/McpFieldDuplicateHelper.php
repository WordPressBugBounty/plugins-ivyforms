<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

/**
 * Duplicate-field logic for IvyForms MCP field operations.
 */
class McpFieldDuplicateHelper
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function duplicateFormField(array $input): array
    {
        $formId = (int) ($input['formId'] ?? 0);
        $fieldId = (int) ($input['fieldId'] ?? 0);

        $formData = McpAbilitiesHelper::restRequest('GET', '/form/' . $formId);
        $form = McpAbilitiesHelper::extractIvyFormsRestPayload($formData);

        if (!is_array($form) || $form === []) {
            return self::failure($formId, $fieldId, 0, "Form {$formId} not found.");
        }

        $existingFields = $form['fields'] ?? [];
        $managedFieldId = self::resolveManagedFieldId($existingFields, $fieldId);
        $targetField = self::findFieldById($existingFields, $managedFieldId);

        if ($targetField === null) {
            return self::failure(
                $formId,
                $fieldId,
                0,
                "Field {$fieldId} was not found in form {$formId}."
            );
        }

        $sequence = self::nextFieldSequence($existingFields);
        $duplicatedFields = [
            McpFieldAbilitiesHelper::duplicateFieldPayload(
                $targetField,
                $formId,
                $sequence['fieldIndex'],
                $sequence['position'],
                $sequence['rowIndex'],
                false
            ),
        ];

        foreach (self::childFieldsForParent($existingFields, $managedFieldId) as $childField) {
            $duplicatedFields[] = McpFieldAbilitiesHelper::duplicateFieldPayload(
                $childField,
                $formId,
                $sequence['fieldIndex'],
                $sequence['position'],
                $sequence['rowIndex'],
                true
            );
        }

        $form['fields'] = array_merge($existingFields, $duplicatedFields);
        $result = McpAbilitiesHelper::restRequest('POST', '/form/update/' . $formId, $form);

        if (!McpAbilitiesHelper::isRestMutationSuccessful($result)) {
            return self::failure(
                $formId,
                $managedFieldId,
                0,
                McpAbilitiesHelper::restMutationErrorMessage(
                    $result,
                    __('Failed to duplicate the field.', 'ivyforms')
                ),
                McpAbilitiesHelper::adminFormUrl($formId)
            );
        }

        $updatedFormData = McpAbilitiesHelper::restRequest('GET', '/form/' . $formId);
        $updatedForm = McpAbilitiesHelper::extractIvyFormsRestPayload($updatedFormData);
        $updatedFields = is_array($updatedForm) ? ($updatedForm['fields'] ?? []) : [];

        $newFieldId = self::resolveDuplicatedParentFieldId($updatedFields, $sequence['fieldIndex']);
        $relinkError = self::relinkDuplicatedChildFields(
            $formId,
            $managedFieldId,
            $newFieldId,
            $sequence['fieldIndex'],
            is_array($updatedForm) ? $updatedForm : null,
            $updatedFields
        );
        if ($relinkError !== null) {
            return $relinkError;
        }

        return [
            'formId'     => $formId,
            'oldFieldId' => $managedFieldId,
            'newFieldId' => $newFieldId,
            'success'    => true,
            'message'    => sprintf(
                /* translators: %s: field label */
                __('Field "%s" duplicated successfully.', 'ivyforms'),
                (string) ($targetField['label'] ?? __('Field', 'ivyforms'))
            ),
            'adminLink'  => McpAbilitiesHelper::adminFormUrl($formId),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @return array<string, mixed>|null
     */
    private static function findFieldById(array $fields, int $fieldId): ?array
    {
        foreach ($fields as $field) {
            if ((int) ($field['id'] ?? 0) === $fieldId) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     */
    private static function resolveManagedFieldId(array $fields, int $fieldId): int
    {
        $field = self::findFieldById($fields, $fieldId);
        if ($field === null) {
            return $fieldId;
        }

        $parentId = (int) ($field['parentId'] ?? 0);

        return $parentId > 0 ? $parentId : $fieldId;
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @return array<int, array<string, mixed>>
     */
    private static function childFieldsForParent(array $fields, int $parentId): array
    {
        return array_values(array_filter(
            $fields,
            static function (array $field) use ($parentId): bool {
                return (int) ($field['parentId'] ?? 0) === $parentId;
            }
        ));
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @return array<string, int>
     */
    private static function nextFieldSequence(array $fields): array
    {
        $maxPosition = 0;
        $maxRowIndex = 0;
        $maxFieldIndex = 0;

        foreach ($fields as $field) {
            $maxPosition = max($maxPosition, (int) ($field['position'] ?? 0));
            $maxRowIndex = max($maxRowIndex, (int) ($field['rowIndex'] ?? 0));
            $maxFieldIndex = max($maxFieldIndex, (int) ($field['fieldIndex'] ?? 0));
        }

        return [
            'position'   => $maxPosition + 1,
            'rowIndex'   => $maxRowIndex + 1,
            'fieldIndex' => $maxFieldIndex + 1,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $updatedFields
     */
    private static function resolveDuplicatedParentFieldId(array $updatedFields, int $newFieldIndex): int
    {
        $matchingFields = array_values(array_filter(
            $updatedFields,
            static function (array $updatedField) use ($newFieldIndex): bool {
                return (int) ($updatedField['fieldIndex'] ?? 0) === $newFieldIndex
                    && (int) ($updatedField['parentId'] ?? 0) === 0;
            }
        ));

        return (int) ($matchingFields[0]['id'] ?? 0);
    }

    /**
     * @param array<int, array<string, mixed>> $updatedFields
     * @param array<string, mixed>|null $updatedForm
     * @return array<string, mixed>|null
     */
    private static function relinkDuplicatedChildFields(
        int $formId,
        int $managedFieldId,
        int $newFieldId,
        int $newFieldIndex,
        ?array $updatedForm,
        array $updatedFields
    ): ?array {
        if ($newFieldId <= 0 || !is_array($updatedForm)) {
            return null;
        }

        $relinkNeeded = false;
        foreach ($updatedFields as $fieldIndex => $updatedField) {
            if ((int) ($updatedField['fieldIndex'] ?? 0) !== $newFieldIndex) {
                continue;
            }
            if ((int) ($updatedField['parentId'] ?? 0) !== $managedFieldId) {
                continue;
            }
            if ((int) ($updatedField['id'] ?? 0) === $newFieldId) {
                continue;
            }

            $updatedFields[$fieldIndex]['parentId'] = $newFieldId;
            $relinkNeeded = true;
        }

        if (!$relinkNeeded) {
            return null;
        }

        $updatedForm['fields'] = $updatedFields;
        $relinkResult = McpAbilitiesHelper::restRequest('POST', '/form/update/' . $formId, $updatedForm);

        if (McpAbilitiesHelper::isRestMutationSuccessful($relinkResult)) {
            return null;
        }

        return self::failure(
            $formId,
            $managedFieldId,
            $newFieldId,
            McpAbilitiesHelper::restMutationErrorMessage(
                $relinkResult,
                __('Field duplicated but child fields could not be linked.', 'ivyforms')
            ),
            McpAbilitiesHelper::adminFormUrl($formId)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private static function failure(
        int $formId,
        int $oldFieldId,
        int $newFieldId,
        string $message,
        string $adminLink = ''
    ): array {
        return [
            'formId'     => $formId,
            'oldFieldId' => $oldFieldId,
            'newFieldId' => $newFieldId,
            'success'    => false,
            'message'    => $message,
            'adminLink'  => $adminLink,
        ];
    }
}
