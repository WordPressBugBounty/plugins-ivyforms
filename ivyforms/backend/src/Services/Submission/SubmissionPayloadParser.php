<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;

/**
 * Normalizes JSON or multipart (form POST) submission payloads.
 */
final class SubmissionPayloadParser
{
    /**
     * @param WP_REST_Request<array<string, mixed>> $request
     *
     * @return array<string, mixed>
     */
    public static function parseRequestPayload(WP_REST_Request $request): array
    {
        $json = $request->get_json_params();
        if (is_array($json)) {
            $resolved = self::resolveStructuredPayload($json, false);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        $params = $request->get_params();
        if (is_array($params)) {
            $resolved = self::resolveStructuredPayload($params, true);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return self::parseMultipartSubmissionPayload($request);
    }

    /**
     * Wrap flat field keys (public API / OpenAPI shape) into embed submission shape.
     *
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    public static function normalizeFlatPayload(array $body): array
    {
        $params = self::extractSubmissionScalars($body);

        if (isset($body['values']) && is_array($body['values'])) {
            $params['values'] = $body['values'];

            return $params;
        }

        $values = [];
        $reservedKeys = ['formId', 'nonce', 'postId', 'referer', 'ivyforms_fm', 'values'];
        foreach ($body as $key => $value) {
            if (!in_array($key, $reservedKeys, true)) {
                $values[$key] = $value;
            }
        }
        $params['values'] = $values;

        if (isset($body['ivyforms_fm'])) {
            $params['ivyforms_fm'] = is_string($body['ivyforms_fm']) ? wp_unslash($body['ivyforms_fm']) : '';
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>|null
     */
    private static function resolveStructuredPayload(array $payload, bool $requireValuesArray): ?array
    {
        if (array_key_exists('values', $payload)) {
            if (!$requireValuesArray || is_array($payload['values'])) {
                return $payload;
            }
        }

        if ($payload === []) {
            return null;
        }

        $normalized = self::normalizeFlatPayload($payload);

        return $normalized['values'] !== [] ? $normalized : null;
    }

    /**
     * @param WP_REST_Request<array<string, mixed>> $request
     *
     * @return array<string, mixed>
     */
    private static function parseMultipartSubmissionPayload(WP_REST_Request $request): array
    {
        $body = $request->get_body_params();
        if (!is_array($body)) {
            $body = [];
        }

        $params = self::extractSubmissionScalars($body);
        $params['values'] = self::decodeSubmissionValues($body);

        if (isset($body['ivyforms_fm'])) {
            $params['ivyforms_fm'] = is_string($body['ivyforms_fm']) ? wp_unslash($body['ivyforms_fm']) : '';
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    private static function extractSubmissionScalars(array $body): array
    {
        $params = [];
        foreach (['formId', 'nonce', 'postId', 'referer'] as $key) {
            if (!array_key_exists($key, $body)) {
                continue;
            }

            $value = $body[$key];

            if ($key === 'nonce' || $key === 'referer') {
                if (!is_scalar($value)) {
                    throw new InvalidArgumentException(
                        BackendStrings::getExceptionStrings()['invalid_request_data']
                    );
                }

                $params[$key] = wp_unslash((string) $value);
                continue;
            }

            if (!is_scalar($value)) {
                $params[$key] = 0;
                continue;
            }

            $unslashed = wp_unslash($value);
            $params[$key] = is_numeric($unslashed) ? (int) $unslashed : 0;
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array<string, mixed>
     */
    private static function decodeSubmissionValues(array $body): array
    {
        if (!isset($body['values'])) {
            return [];
        }

        if (is_string($body['values'])) {
            // WP_REST_Server already runs wp_unslash() on $_POST before populating body params,
            // so the multipart `values` JSON arrives unslashed. Decode it as-is: unslashing again
            // strips the backslashes that escape quotes inside rich-text HTML, which corrupts the
            // JSON and makes json_decode() return null (dropping every field except uploaded files).
            // The wp_unslash() fallback only covers environments that hand back a slashed body.
            $raw = $body['values'];
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                $decoded = json_decode(wp_unslash($raw), true);
            }

            return is_array($decoded) ? $decoded : [];
        }

        if (is_array($body['values'])) {
            return $body['values'];
        }

        return [];
    }
}
