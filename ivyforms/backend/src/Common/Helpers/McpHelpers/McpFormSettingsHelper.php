<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Update-form-settings logic for IvyForms MCP abilities.
 */
class McpFormSettingsHelper
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function updateFormSettings(array $input): array
    {
        $formId = (int) ($input['formId'] ?? 0);
        if ($formId <= 0) {
            return ['error' => BackendStrings::getExceptionStrings()['invalid_form_id']];
        }

        $loaded = self::loadFormForSettingsUpdate($formId);
        if (isset($loaded['error'])) {
            return ['error' => $loaded['error']];
        }

        $merged = self::mergeValidatedSettings($loaded['form'], self::aliasTitleToName($input));
        if ($merged['error'] !== null) {
            return ['error' => $merged['error']];
        }

        $form = $merged['form'];
        [$success, $result] = self::persistFormSettingsUpdate($formId, $form);

        return [
            'formId'    => $formId,
            'updated'   => $success,
            'name'      => self::formName($form),
            'adminLink' => McpAbilitiesHelper::adminFormUrl($formId),
            'raw'       => $result,
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private static function aliasTitleToName(array $input): array
    {
        if (!array_key_exists('name', $input) && array_key_exists('title', $input)) {
            $input['name'] = $input['title'];
        }

        return $input;
    }

    /**
     * @return array{form?: array<string, mixed>, error?: string}
     */
    private static function loadFormForSettingsUpdate(int $formId): array
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
     * @param array<string, mixed> $form
     * @param array<string, mixed> $input
     * @return array{form: array<string, mixed>, error: string|null}
     */
    private static function mergeValidatedSettings(array $form, array $input): array
    {
        foreach (['name' => 255, 'description' => 1000] as $key => $maxLength) {
            $merge = self::mergeValidatedTextSetting($form, $input, $key, $maxLength);
            if ($merge['error'] !== null) {
                return $merge;
            }
            $form = $merge['form'];
        }

        return [
            'form'  => self::mergeValidatedBooleanSettings($form, $input),
            'error' => null,
        ];
    }

    /**
     * @param array<string, mixed> $form
     * @param array<string, mixed> $input
     * @return array{form: array<string, mixed>, error: string|null}
     */
    private static function mergeValidatedTextSetting(
        array $form,
        array $input,
        string $key,
        int $maxLength
    ): array {
        if (!array_key_exists($key, $input)) {
            return ['form' => $form, 'error' => null];
        }

        if (!is_string($input[$key])) {
            return [
                'form'  => $form,
                'error' => BackendStrings::getExceptionStrings()['invalid_request_data'],
            ];
        }

        $value = sanitize_text_field($input[$key]);
        if (strlen($value) > $maxLength) {
            return [
                'form'  => $form,
                'error' => sprintf(
                    /* translators: 1: Field name, 2: Max length. */
                    __('%1$s must be at most %2$d characters.', 'ivyforms'),
                    $key,
                    $maxLength
                ),
            ];
        }

        $form[$key] = $value;

        return ['form' => $form, 'error' => null];
    }

    /**
     * @param array<string, mixed> $form
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private static function mergeValidatedBooleanSettings(array $form, array $input): array
    {
        foreach (['published', 'showTitle', 'showDescription', 'storeEntries'] as $boolKey) {
            if (!array_key_exists($boolKey, $input) || !is_bool($input[$boolKey])) {
                continue;
            }
            $form[$boolKey] = $input[$boolKey];
        }

        return $form;
    }

    /**
     * @param array<string, mixed> $form
     */
    private static function formName(array $form): ?string
    {
        return is_string($form['name'] ?? null) ? $form['name'] : null;
    }

    /**
     * @param array<string, mixed> $form
     * @return array{0: bool, 1: mixed}
     */
    private static function persistFormSettingsUpdate(int $formId, array $form): array
    {
        try {
            $result  = McpAbilitiesHelper::restRequest('POST', '/form/update/' . $formId, $form);
            $success = McpAbilitiesHelper::isRestMutationSuccessful($result);
        } catch (\Throwable $exception) {
            $result  = [
                'code'    => 'exception',
                'message' => $exception->getMessage(),
            ];
            $success = false;
        }

        return [$success, $result];
    }
}
