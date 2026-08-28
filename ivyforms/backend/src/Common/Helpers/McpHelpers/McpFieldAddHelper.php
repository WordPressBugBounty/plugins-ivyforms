<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Add-field logic for IvyForms MCP field abilities.
 */
class McpFieldAddHelper
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function addFormField(array $input): array
    {
        $strings = BackendStrings::getMcpFieldStrings();
        $formId = (int) ($input['formId'] ?? 0);
        if ($formId <= 0) {
            return self::singleFieldFailure(0, BackendStrings::getExceptionStrings()['invalid_form_id']);
        }

        $loaded = self::loadFormForMutation($formId);
        if (isset($loaded['error'])) {
            return self::singleFieldFailure($formId, $loaded['error']);
        }

        /** @var array<string, mixed> $form */
        $form = $loaded['form'];
        $appendResult = McpFieldAbilitiesHelper::appendFieldsToForm([$input], $formId, $form);
        if (!$appendResult['success']) {
            return self::singleFieldFailure($formId, $appendResult['message']);
        }

        $form['fields'] = $appendResult['fields'];
        $result = McpAbilitiesHelper::restRequest('POST', '/form/update/' . $formId, $form);
        $success = McpAbilitiesHelper::isRestMutationSuccessful($result);
        $label = $appendResult['labels'][0] ?? $strings['field'];

        return [
            'formId'    => $formId,
            'success'   => $success,
            'message'   => $success
                ? sprintf($strings['field_added_successfully_to_form'], $label, $formId)
                : McpAbilitiesHelper::restMutationErrorMessage($result, $strings['failed_to_add_field']),
            'adminLink' => McpAbilitiesHelper::adminFormUrl($formId),
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function addFormFields(array $input): array
    {
        $strings = BackendStrings::getMcpFieldStrings();
        $formId = (int) ($input['formId'] ?? 0);
        $fieldInputs = $input['fields'] ?? [];

        if ($formId <= 0) {
            return self::multiFieldFailure(0, BackendStrings::getExceptionStrings()['invalid_form_id']);
        }

        if (!is_array($fieldInputs) || $fieldInputs === []) {
            return self::multiFieldFailure(
                $formId,
                $strings['at_least_one_field_definition_required']
            );
        }

        $loaded = self::loadFormForMutation($formId);
        if (isset($loaded['error'])) {
            return self::multiFieldFailure($formId, $loaded['error']);
        }

        /** @var array<string, mixed> $form */
        $form = $loaded['form'];
        $appendResult = McpFieldAbilitiesHelper::appendFieldsToForm($fieldInputs, $formId, $form);
        if (!$appendResult['success']) {
            return self::multiFieldFailure($formId, $appendResult['message']);
        }

        $form['fields'] = $appendResult['fields'];
        $result = McpAbilitiesHelper::restRequest('POST', '/form/update/' . $formId, $form);
        $success = McpAbilitiesHelper::isRestMutationSuccessful($result);

        return [
            'formId'      => $formId,
            'success'     => $success,
            'message'     => $success
                ? sprintf($strings['added_fields_successfully'], count($appendResult['labels']))
                : McpAbilitiesHelper::restMutationErrorMessage(
                    $result,
                    $strings['failed_to_add_fields']
                ),
            'addedFields' => $appendResult['labels'],
            'adminLink'   => McpAbilitiesHelper::adminFormUrl($formId),
        ];
    }

    /**
     * @return array{form?: array<string, mixed>, error?: string}
     */
    private static function loadFormForMutation(int $formId): array
    {
        $exceptionStrings = BackendStrings::getExceptionStrings();
        $formData = McpAbilitiesHelper::restRequest('GET', '/form/' . $formId);

        // Root REST error envelopes (code/status) must not be treated as form payloads.
        if (!McpAbilitiesHelper::isRestMutationSuccessful($formData)) {
            return [
                'error' => McpAbilitiesHelper::restMutationErrorMessage(
                    $formData,
                    $exceptionStrings['form_not_found']
                ),
            ];
        }

        $form = McpAbilitiesHelper::extractIvyFormsRestPayload($formData);
        if (!is_array($form) || $form === [] || !isset($form['id'])) {
            return [
                'error' => sprintf(
                    BackendStrings::getMcpMultipageStrings()['form_not_found_with_id'],
                    $formId
                ),
            ];
        }

        return ['form' => $form];
    }

    /**
     * @return array<string, mixed>
     */
    private static function singleFieldFailure(int $formId, string $message): array
    {
        return [
            'formId'    => $formId,
            'success'   => false,
            'message'   => $message,
            'adminLink' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function multiFieldFailure(int $formId, string $message): array
    {
        return [
            'formId'      => $formId,
            'success'     => false,
            'message'     => $message,
            'addedFields' => [],
            'adminLink'   => '',
        ];
    }
}
