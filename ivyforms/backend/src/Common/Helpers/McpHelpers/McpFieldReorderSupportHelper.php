<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

/**
 * Request/response helpers for IvyForms MCP field reorder operations.
 */
class McpFieldReorderSupportHelper
{
    /**
     * Returns reorder context or an error response (same shape as {@see McpFieldReorderHelper::reorderError}).
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function contextOrError(array $input): array
    {
        $formId = (int) ($input['formId'] ?? 0);
        $fieldUpdates = $input['fields'] ?? [];

        if ($formId <= 0) {
            return McpFieldReorderHelper::reorderError(
                $formId,
                __('A valid form ID is required.', 'ivyforms')
            );
        }

        if (!is_array($fieldUpdates) || $fieldUpdates === []) {
            return McpFieldReorderHelper::reorderError(
                $formId,
                __('At least one field update is required.', 'ivyforms')
            );
        }

        $formData = McpAbilitiesHelper::restRequest('GET', '/form/' . $formId);
        $form = McpAbilitiesHelper::extractIvyFormsRestPayload($formData);

        if (!is_array($form) || $form === []) {
            return McpFieldReorderHelper::reorderError($formId, "Form {$formId} not found.");
        }

        $existingFields = $form['fields'] ?? [];
        $updatesById = McpFieldReorderHelper::reorderNormalizeUpdates($existingFields, $fieldUpdates);

        if ($updatesById === []) {
            return McpFieldReorderHelper::reorderError(
                $formId,
                __('No valid field updates were provided.', 'ivyforms')
            );
        }

        return [
            'success'        => true,
            'formId'         => $formId,
            'form'           => $form,
            'existingFields' => $existingFields,
            'updatesById'    => $updatesById,
        ];
    }

    /**
     * Builds final reorder response from REST update result.
     *
     * @param mixed $result REST response
     * @return array<string, mixed>
     */
    public static function resultFromRest(int $formId, $result, int $updatedFieldsCount): array
    {
        $success = McpAbilitiesHelper::isRestMutationSuccessful($result);

        $message = __('Field order updated successfully.', 'ivyforms');
        if (!$success) {
            $message = McpAbilitiesHelper::restMutationErrorMessage(
                $result,
                __('Failed to update field order.', 'ivyforms')
            );
        }

        return [
            'formId'             => $formId,
            'success'            => $success,
            'message'            => $message,
            'updatedFieldsCount' => $updatedFieldsCount,
            'adminLink'          => McpAbilitiesHelper::adminFormUrl($formId),
        ];
    }

    /**
     * @param array<int, array<string, int|string|null>> $updatesById
     * @param array<string, mixed> $form
     * @return array<int, array<string, int|string|null>>|array<string, mixed>
     */
    public static function validatePageIds(array $updatesById, array $form, int $formId): array
    {
        $validated = [];

        foreach ($updatesById as $fieldId => $update) {
            if (!is_array($update)) {
                continue;
            }

            $pageId = $update['pageId'] ?? null;
            if ($pageId !== null && $pageId !== '') {
                $normalizedPageId = McpMultiPageHelper::normalizePageIdForForm((string) $pageId, $form);
                if ($normalizedPageId === null) {
                    return McpFieldReorderHelper::reorderError(
                        $formId,
                        sprintf(
                            __('Invalid pageId "%s" for this form.', 'ivyforms'),
                            sanitize_text_field((string) $pageId)
                        )
                    );
                }
                $update['pageId'] = $normalizedPageId;
            }

            $validated[$fieldId] = $update;
        }

        return $validated;
    }
}
