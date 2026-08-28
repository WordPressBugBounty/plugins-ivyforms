<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Create-form logic for IvyForms MCP abilities.
 */
class McpFormCreateHelper
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function createForm(array $input): array
    {
        $data = McpAbilitiesHelper::restRequest(
            'POST',
            '/form/add',
            ['template_id' => self::resolveTemplateId($input)]
        );
        $strings = BackendStrings::getAllFormsStrings();
        $failedMessage = $strings['failed_to_create_form'];

        if (!McpAbilitiesHelper::isRestMutationSuccessful($data)) {
            $message = McpAbilitiesHelper::restMutationErrorMessage($data, $failedMessage);

            return self::createFormFailure($message, $data);
        }

        return self::buildCreatedFormResult($data, $strings['form_created'], $failedMessage);
    }

    /**
     * Map common natural-language / alias template ids to canonical IvyForms ids.
     */
    public static function normalizeTemplateId(string $rawTemplateId): string
    {
        $normalized = strtolower(trim($rawTemplateId));
        $normalized = (string) preg_replace('/[\s\-]+/', '_', $normalized);
        $normalized = (string) preg_replace('/_+/', '_', $normalized);
        $normalized = trim($normalized, '_');

        $aliases = [
            'blank'              => 'blank_form',
            'blank_form'         => 'blank_form',
            'empty'              => 'blank_form',
            'empty_form'         => 'blank_form',
            'scratch'            => 'blank_form',
            'start_from_scratch' => 'blank_form',
            'new'                => 'blank_form',
            'new_form'           => 'blank_form',
            'contact'            => 'contact_form',
            'contact_form'       => 'contact_form',
            'contact_us'         => 'contact_form',
            'contactus'          => 'contact_form',
        ];

        return $aliases[$normalized] ?? $normalized;
    }

    /**
     * @param array<string, mixed> $input
     */
    private static function resolveTemplateId(array $input): string
    {
        $candidate = $input['templateId'] ?? $input['template_id'] ?? 'blank_form';
        if (!is_scalar($candidate)) {
            $candidate = 'blank_form';
        }

        return self::normalizeTemplateId(sanitize_text_field((string) $candidate));
    }

    /**
     * @param mixed $raw
     * @return array<string, mixed>
     */
    private static function createFormFailure(string $message, $raw): array
    {
        return [
            'error'   => $message,
            'raw'     => $raw,
            'formId'  => null,
            'message' => $message,
        ];
    }

    /**
     * @param mixed $data
     * @return array<string, mixed>
     */
    private static function buildCreatedFormResult($data, string $successMessage, string $failedMessage): array
    {
        $created = McpAbilitiesHelper::extractIvyFormsRestPayload($data);
        if (!is_array($created)) {
            return self::createFormFailure($failedMessage, $data);
        }

        $payloadError = self::createdPayloadErrorMessage($created, $failedMessage);
        if ($payloadError !== null) {
            return self::createFormFailure($payloadError, $created);
        }

        $formId = isset($created['id']) ? (int) $created['id'] : 0;
        if ($formId <= 0) {
            return self::createFormFailure($failedMessage, $created);
        }

        return [
            'formId'    => $formId,
            'adminLink' => McpAbilitiesHelper::adminFormUrl($formId),
            'message'   => $successMessage,
            'raw'       => $created,
        ];
    }

    /**
     * @param array<string, mixed> $created
     */
    private static function createdPayloadErrorMessage(array $created, string $failedMessage): ?string
    {
        if (!isset($created['error'])) {
            return null;
        }

        if (is_string($created['error']) && $created['error'] !== '') {
            return $created['error'];
        }

        return $failedMessage;
    }
}
