<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

use IvyForms\Infrastructure\WP\MCP\McpAbilityPermissions;
use IvyForms\Infrastructure\WP\MCP\McpFeatureGate;
use WP_REST_Request;

/**
 * Shared helpers used by IvyForms MCP ability registrars.
 */
class McpAbilitiesHelper
{
    private const NAMESPACE = 'ivyforms/v1';

    /**
     * Executes a REST request to the IvyForms internal REST API on behalf of an
     * MCP ability execution.
     *
     * The MCP boundary already enforces user/capability checks; we add a fail-closed
     * guard here so that a future bug in a registrar (or a third-party caller) cannot
     * bypass authentication and reach the admin REST controllers directly.
     *
     * @param array<string, mixed> $params
     *
     * @return array<int|string, mixed>|int|string|bool|float|null
     */
    public static function restRequest(string $method, string $route, array $params = [])
    {
        if (!McpFeatureGate::isEnabled() || !McpAbilityPermissions::canUseMcp()) {
            return [
                'code'    => 'rest_forbidden',
                'message' => 'Insufficient permission for IvyForms MCP REST bridge.',
                'data'    => ['status' => 403],
            ];
        }

        $request = new WP_REST_Request($method, '/' . self::NAMESPACE . $route);
        $request->set_header('X-WP-Nonce', wp_create_nonce('wp_rest'));

        if ($method === 'GET') {
            $request->set_query_params($params);
        }

        if ($method !== 'GET') {
            // Populate both JSON body and body params so controllers using
            // get_param()/get_params() work whether WordPress parses JSON or not.
            $request->set_header('Content-Type', 'application/json');
            $request->set_body(wp_json_encode($params));
            $request->set_body_params($params);
        }

        $response = rest_do_request($request);
        $data     = rest_get_server()->response_to_data($response, false);

        return $data;
    }

    /**
     * Extracts the inner IvyForms REST payload portion from a full REST response.
     *
     * @param mixed $response REST payload
     *
     * @return array<int|string, mixed>|int|string|bool|float|null
     */
    public static function extractIvyFormsRestPayload($response)
    {
        if (!is_array($response)) {
            return $response;
        }

        return $response['data']['data'] ?? $response['data'] ?? $response;
    }

    /**
     * @param mixed $response REST payload
     *
     * @return array<int|string, mixed>
     */
    public static function notificationArrayFromRestResponse($response): array
    {
        $payload = self::extractIvyFormsRestPayload($response);

        if (!is_array($payload)) {
            return [];
        }

        if (
            isset($payload['message'], $payload['data'])
            && is_array($payload['data'])
            && array_key_exists('id', $payload['data'])
        ) {
            return $payload['data'];
        }

        return $payload;
    }

    /**
     * Builds a partial update payload for notification settings.
     *
     * @param array<string, mixed> $notification
     * @return array<string, mixed>
     */
    public static function buildNotificationUpdatePayload(array $notification): array
    {
        $keys = [
            'id',
            'name',
            'sender',
            'replyTo',
            'receiver',
            'cc',
            'bcc',
            'enabled',
            'subject',
            'message',
            'showEmptyFields',
            'smartLogic',
            'formId',
        ];

        $payload = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $notification)) {
                $payload[$key] = $notification[$key];
            }
        }

        return $payload;
    }

    private static function formNameForSubject(int $formId): string
    {
        if ($formId <= 0) {
            return '';
        }

        $response = self::restRequest('GET', '/form/' . $formId);
        $form = self::extractIvyFormsRestPayload($response);

        if (
            isset($form['message'], $form['data'])
            && is_array($form['data'])
            && array_key_exists('name', $form['data'])
        ) {
            $form = $form['data'];
        }

        if (!is_array($form) || empty($form['name'])) {
            return '';
        }

        return sanitize_text_field((string) $form['name']);
    }

    public static function notificationSubjectForForm(int $formId, string $subject = ''): string
    {
        $formName = self::formNameForSubject($formId);

        if ($subject === '') {
            return $formName
                ? sprintf('New form submission: %s', $formName)
                : 'New form submission';
        }

        if ($formName === '') {
            return $subject;
        }

        return str_replace(['{form_name}', '{{form_name}}'], $formName, $subject);
    }

    /**
     * @param mixed $response REST payload returned by {@see restRequest()}.
     */
    public static function isRestMutationSuccessful($response): bool
    {
        if (!is_array($response)) {
            return false;
        }

        if (isset($response['code']) && is_string($response['code']) && $response['code'] !== '') {
            return false;
        }

        $status = (int) ($response['data']['status'] ?? 200);

        return $status >= 200 && $status < 300;
    }

    /**
     * @param mixed $response REST payload returned by {@see restRequest()}.
     */
    public static function restMutationErrorMessage($response, string $fallback): string
    {
        if (!is_array($response)) {
            return $fallback;
        }

        $message = $response['message'] ?? null;
        if (is_string($message) && $message !== '') {
            return $message;
        }

        $nestedMessage = $response['data']['message'] ?? null;
        if (is_string($nestedMessage) && $nestedMessage !== '') {
            return $nestedMessage;
        }

        return $fallback;
    }

    public static function adminFormUrl(int $formId): string
    {
        if ($formId <= 0) {
            return '';
        }
        return admin_url('admin.php?page=ivyforms-builder#/manage/' . $formId);
    }

    public static function adminEntryUrl(int $entryId): string
    {
        if ($entryId <= 0) {
            return '';
        }
        return admin_url('admin.php?page=ivyforms-entries#/details/' . $entryId);
    }
}
