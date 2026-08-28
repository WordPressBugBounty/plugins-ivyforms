<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

/**
 * Extracted reorder logic for IvyForms MCP field operations.
 */
class McpFieldReorderHelper
{
    /**
     * Reorder/update layout state for form fields.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function reorderFormFields(array $input): array
    {
        $context = McpFieldReorderSupportHelper::contextOrError($input);
        if (!$context['success']) {
            return $context;
        }

        $formId = (int) $context['formId'];
        $existingFields = $context['existingFields'];
        $updatesById = $context['updatesById'];

        $form = $context['form'];
        $updatesById = McpFieldReorderSupportHelper::validatePageIds($context['updatesById'], $form, $formId);
        if (isset($updatesById['success']) && $updatesById['success'] === false) {
            return $updatesById;
        }

        [$updatedFields, $updatedFieldsCount] = self::reorderApplyUpdates(
            $existingFields,
            $updatesById
        );

        $form = $context['form'];
        $form['fields'] = $updatedFields;

        $result = McpAbilitiesHelper::restRequest('POST', '/form/update/' . $formId, $form);

        return McpFieldReorderSupportHelper::resultFromRest(
            $formId,
            $result,
            $updatedFieldsCount
        );
    }

    /**
     * @param int $formId
     * @param string $message
     * @return array<string, mixed>
     */
    public static function reorderError(int $formId, string $message): array
    {
        return [
            'formId'             => $formId,
            'success'            => false,
            'message'            => $message,
            'updatedFieldsCount' => 0,
            'adminLink'          => '',
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $existingFields
     * @param array<int, mixed> $fieldUpdates
     * @return array<int, array<string, int|string|null>>
     */
    public static function reorderNormalizeUpdates(array $existingFields, array $fieldUpdates): array
    {
        $updatesById = [];

        foreach ($fieldUpdates as $fieldUpdate) {
            $updateEntry = self::reorderBuildUpdateEntry($existingFields, $fieldUpdate);
            if ($updateEntry === null) {
                continue;
            }

            $updatesById[$updateEntry['fieldId']] = $updateEntry['update'];
        }

        return $updatesById;
    }

    /**
     * @param array<int, array<string, mixed>> $existingFields
     * @param mixed $fieldUpdate
     * @return array{fieldId: int, update: array<string, int|string|null>}|null
     */
    private static function reorderBuildUpdateEntry(array $existingFields, $fieldUpdate): ?array
    {
        if (!is_array($fieldUpdate) || !isset($fieldUpdate['id'], $fieldUpdate['position'])) {
            return null;
        }

        $incomingId = (int) $fieldUpdate['id'];
        $position = (int) $fieldUpdate['position'];
        if ($incomingId <= 0 || $position <= 0) {
            return null;
        }

        $managedFieldId = self::reorderResolveManagedFieldId($existingFields, $incomingId);
        if ($managedFieldId <= 0) {
            return null;
        }

        return [
            'fieldId' => $managedFieldId,
            'update'  => [
                'position' => $position,
                'rowIndex' => array_key_exists('rowIndex', $fieldUpdate) ? (int) $fieldUpdate['rowIndex'] : null,
                'columnIndex' => array_key_exists('columnIndex', $fieldUpdate)
                    ? (int) $fieldUpdate['columnIndex']
                    : null,
                'width' => self::reorderNormalizeWidth($fieldUpdate),
                'pageId' => array_key_exists('pageId', $fieldUpdate)
                    ? sanitize_text_field((string) $fieldUpdate['pageId'])
                    : null,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $fieldUpdate
     */
    public static function reorderNormalizeWidth(array $fieldUpdate): ?int
    {
        if (!array_key_exists('width', $fieldUpdate)) {
            return null;
        }

        $width = (int) $fieldUpdate['width'];
        return in_array($width, [25, 50, 75, 100], true) ? $width : null;
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @param array<int, array<string, int|string|null>> $updatesById
     * @return array{0: array<int, array<string, mixed>>, 1: int}
     */
    public static function reorderApplyUpdates(array $fields, array $updatesById): array
    {
        $updatedFieldsCount = 0;
        $pageIdByParentId = [];

        foreach ($fields as &$field) {
            $currentId = (int) ($field['id'] ?? 0);
            $currentParentId = (int) ($field['parentId'] ?? 0);

            $managedUpdate = $updatesById[$currentId] ?? null;
            $update = $managedUpdate ?? ($updatesById[$currentParentId] ?? null);

            if ($update === null) {
                continue;
            }

            $field['position'] = (int) $update['position'];
            if ($managedUpdate === null) {
                self::reorderApplyInheritedPageId($field, $currentParentId, $pageIdByParentId);
                continue;
            }

            if (self::reorderApplyManagedUpdate($field, $update, $currentId, $currentParentId, $pageIdByParentId)) {
                $updatedFieldsCount++;
            }
        }

        unset($field);

        return [$fields, $updatedFieldsCount];
    }

    /**
     * @param array<string, mixed> $field
     * @param array<int, string> $pageIdByParentId
     */
    private static function reorderApplyInheritedPageId(
        array &$field,
        int $currentParentId,
        array $pageIdByParentId
    ): void {
        if ($currentParentId <= 0) {
            return;
        }

        if (!isset($pageIdByParentId[$currentParentId]) || $pageIdByParentId[$currentParentId] === '') {
            return;
        }

        McpMultiPageHelper::applyPageIdToField($field, $pageIdByParentId[$currentParentId]);
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, int|string|null> $update
     * @param array<int, string> $pageIdByParentId
     */
    private static function reorderApplyManagedUpdate(
        array &$field,
        array $update,
        int $currentId,
        int $currentParentId,
        array &$pageIdByParentId
    ): bool {
        if ($update['rowIndex'] !== null) {
            $field['rowIndex'] = (int) $update['rowIndex'];
        }
        if ($update['columnIndex'] !== null) {
            $field['columnIndex'] = (int) $update['columnIndex'];
        }
        if ($update['width'] !== null) {
            $field['width'] = (int) $update['width'];
        }
        if ($update['pageId'] !== null && $update['pageId'] !== '') {
            McpMultiPageHelper::applyPageIdToField($field, (string) $update['pageId']);
            if ($currentParentId === 0) {
                $pageIdByParentId[$currentId] = (string) $update['pageId'];
            }
        }

        return true;
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     */
    public static function reorderResolveManagedFieldId(array $fields, int $fieldId): int
    {
        foreach ($fields as $field) {
            if ((int) ($field['id'] ?? 0) !== $fieldId) {
                continue;
            }

            $parentId = (int) ($field['parentId'] ?? 0);
            return $parentId > 0 ? $parentId : $fieldId;
        }

        return $fieldId;
    }
}
