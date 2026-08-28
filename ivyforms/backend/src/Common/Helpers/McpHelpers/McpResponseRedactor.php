<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

/**
 * Strips secrets and PII from payloads before they leave through the MCP boundary.
 *
 * Apply only inside MCP ability `execute_callback` returns; admin REST controllers
 * keep returning full data so the IvyForms admin UI is unaffected.
 *
 * Entry redaction masks tracking metadata (IP, user agent, source URL), submission
 * field values, and email/phone-like strings elsewhere in the payload. Use {@code adminLink}
 * in entry responses when full submission detail is required in the WordPress admin.
 *
 * Filters:
 * - {@code ivyforms/mcp/redact_pii}: enable entry/submission masking. Default {@code true}.
 */
class McpResponseRedactor
{
    public const FILTER_REDACT_PII = 'ivyforms/mcp/redact_pii';

    /**
     * Entry keys that always carry identifying or tracking data.
     *
     * @var array<int, string>
     */
    private const ENTRY_TRACKING_KEYS = [
        'ipAddress',
        'userAgent',
        'sourceURL',
    ];

    /**
     * Pattern matching common secret-looking keys (api keys, tokens, passwords, ...).
     */
    private const SENSITIVE_KEY_PATTERN =
        '/(api[_-]?key|secret|token|password|smtp|client[_-]?secret|private[_-]?key' .
        '|access[_-]?token|refresh[_-]?token|webhook[_-]?secret|salt|signature)/i';

    /**
     * Recursively strip secret-looking values from a settings payload.
     *
     * @param array<int|string, mixed> $settings
     * @return array<int|string, mixed>
     */
    public static function redactSettings(array $settings): array
    {
        $result = [];

        foreach ($settings as $key => $value) {
            if (self::isSensitiveKey((string) $key)) {
                $result[$key] = self::maskScalar($value);
                continue;
            }

            if (is_array($value)) {
                $result[$key] = self::redactSettings($value);
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Mask PII in an entry payload.
     *
     * Returns the entry unchanged when the {@code ivyforms/mcp/redact_pii} filter is
     * disabled.
     *
     * @param array<int|string, mixed> $entry
     * @return array<int|string, mixed>
     */
    public static function redactEntry(array $entry): array
    {
        if (!self::shouldRedactPii()) {
            return $entry;
        }

        return self::walkAndMaskPii($entry);
    }

    /**
     * Redact each entry in a list response.
     *
     * @param array<int, mixed> $entries
     * @return array<int, mixed>
     */
    public static function redactEntryList(array $entries): array
    {
        if (!self::shouldRedactPii()) {
            return $entries;
        }

        return array_map(static function ($entry) {
            return is_array($entry) ? self::redactEntry($entry) : $entry;
        }, $entries);
    }

    private static function shouldRedactPii(): bool
    {
        return (bool) apply_filters(self::FILTER_REDACT_PII, true);
    }

    private static function isSensitiveKey(string $key): bool
    {
        return (bool) preg_match(self::SENSITIVE_KEY_PATTERN, $key);
    }

    /**
     * @param array<int, mixed> $fields
     * @return array<int, mixed>
     */
    private static function redactEntryFields(array $fields): array
    {
        $result = [];

        foreach ($fields as $field) {
            if (!is_array($field)) {
                $result[] = $field;
                continue;
            }

            if (array_key_exists('fieldValue', $field)) {
                $field['fieldValue'] = self::maskSubmissionValue($field['fieldValue']);
            }

            $result[] = $field;
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function maskSubmissionValue($value)
    {
        if ($value === '' || $value === null) {
            return $value;
        }

        if (is_array($value)) {
            return self::walkAndMaskPii($value);
        }

        if (!is_string($value)) {
            return $value;
        }

        $masked = McpPiiMasker::maskString($value);

        if ($masked !== $value) {
            return $masked;
        }

        return McpPiiMasker::REDACTED_PLACEHOLDER;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function maskScalar($value)
    {
        if ($value === '' || $value === null) {
            return $value;
        }

        if (is_array($value)) {
            return McpPiiMasker::REDACTED_PLACEHOLDER;
        }

        return McpPiiMasker::REDACTED_PLACEHOLDER;
    }

    /**
     * @param array<int|string, mixed> $payload
     * @return array<int|string, mixed>
     */
    private static function walkAndMaskPii(array $payload): array
    {
        $result = [];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                if ($key === 'fields') {
                    $result[$key] = self::redactEntryFields($value);
                    continue;
                }

                $result[$key] = self::walkAndMaskPii($value);
                continue;
            }

            if (in_array((string) $key, self::ENTRY_TRACKING_KEYS, true)) {
                $result[$key] = $value === '' || $value === null ? $value : McpPiiMasker::REDACTED_PLACEHOLDER;
                continue;
            }

            if (is_string($value) && $value !== '') {
                $result[$key] = McpPiiMasker::maskString($value);
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}
