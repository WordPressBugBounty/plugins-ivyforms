<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

use IvyForms\Services\Translations\BackendStrings;

/**
 * REST persistence helpers for IvyForms MCP multipage operations.
 */
class McpMultiPagePersistenceHelper
{
    /**
     * @return array<string, mixed>|null Error response when formId or Pro check fails.
     */
    public static function validateFormIdAndPro(int $formId): ?array
    {
        if ($formId <= 0) {
            return self::errorResponse(0, self::multipageStrings()['valid_form_id_required']);
        }

        return McpMultiPageHelper::proRequiredError($formId);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function loadFormOrNull(int $formId): ?array
    {
        $formData = McpAbilitiesHelper::restRequest('GET', '/form/' . $formId);
        $form = McpAbilitiesHelper::extractIvyFormsRestPayload($formData);

        return is_array($form) && $form !== [] ? $form : null;
    }

    /**
     * @param array<string, mixed> $form
     * @return array<string, mixed>
     */
    public static function persistForm(int $formId, array $form, string $successMessage): array
    {
        $result = McpAbilitiesHelper::restRequest('POST', '/form/update/' . $formId, $form);
        $success = McpAbilitiesHelper::isRestMutationSuccessful($result);

        $updatedForm = self::loadFormOrNull($formId);

        $pages = is_array($updatedForm) && is_array($updatedForm['pages'] ?? null)
            ? $updatedForm['pages']
            : [];

        $message = $success
            ? $successMessage
            : McpAbilitiesHelper::restMutationErrorMessage(
                $result,
                self::multipageStrings()['failed_update_multipage_form']
            );

        return [
            'formId'    => $formId,
            'success'   => $success,
            'message'   => $message,
            'pages'     => McpMultiPagePagesHelper::stripFieldsFromPages($pages),
            'adminLink' => McpAbilitiesHelper::adminFormUrl($formId),
        ];
    }

    public static function formNotFoundMessage(int $formId): string
    {
        return sprintf(self::multipageStrings()['form_not_found_with_id'], $formId);
    }

    /**
     * @return array<string, mixed>
     */
    public static function errorResponse(int $formId, string $message): array
    {
        return [
            'formId'    => $formId,
            'success'   => false,
            'message'   => $message,
            'pages'     => [],
            'adminLink' => $formId > 0 ? McpAbilitiesHelper::adminFormUrl($formId) : '',
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function multipageStrings(): array
    {
        /** @var array<string, string> */
        return BackendStrings::getMcpMultipageStrings();
    }
}
