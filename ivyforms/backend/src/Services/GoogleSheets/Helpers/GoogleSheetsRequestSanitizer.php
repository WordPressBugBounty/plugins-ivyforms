<?php

namespace IvyForms\Services\GoogleSheets\Helpers;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;

/**
 * Sanitizes Google Sheets integration REST request data.
 */
class GoogleSheetsRequestSanitizer
{
    private const DISABLED_SMART_LOGIC = [
        'enabled' => false,
        'match'   => 'any',
        'rules'   => [],
    ];

    /**
     * Preserve OAuth token characters; sanitize_text_field can alter bearer/refresh tokens.
     *
     * @param mixed $value
     */
    public static function sanitizeOAuthToken($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return trim(wp_unslash((string) $value));
    }

    /**
     * @param mixed $fieldMapping
     * @return list<array{column: string, value: string}>
     */
    public function sanitizeFieldMapping($fieldMapping): array
    {
        if (!is_array($fieldMapping)) {
            return [];
        }

        $sanitized = [];

        foreach ($fieldMapping as $item) {
            if (!is_array($item) || empty($item['column'])) {
                continue;
            }

            $column = Sanitizer::sanitizeText((string) $item['column']);
            $value = Sanitizer::sanitizeText((string) ($item['value'] ?? ''));

            if ($column === '') {
                continue;
            }

            $sanitized[] = [
                'column' => $column,
                'value'  => $value,
            ];
        }

        return $sanitized;
    }

    /**
     * @param mixed $smartLogic
     * @return array<string, mixed>
     */
    public function sanitizeSmartLogic($smartLogic): array
    {
        if (!$this->isSmartLogicAllowed()) {
            return self::DISABLED_SMART_LOGIC;
        }

        return Sanitizer::sanitizeNotificationSmartLogic($smartLogic);
    }

    /**
     * @return array<string, callable>
     */
    private function fieldSanitizers(): array
    {
        return [
            'spreadsheet_id'   => fn ($var) => Sanitizer::sanitizeText($var),
            'spreadsheet_name' => fn ($var) => Sanitizer::sanitizeText($var),
            'worksheet_name'   => fn ($var) => Sanitizer::sanitizeText($var),
            'field_mapping'    => fn ($var) => $this->sanitizeFieldMapping($var),
            'smart_logic'      => fn ($var) => $this->sanitizeSmartLogic($var),
            'enabled'          => fn ($var) => filter_var($var, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @return array{
     *     form_id: int,
     *     spreadsheet_id: string,
     *     spreadsheet_name: string,
     *     worksheet_name: string,
     *     field_mapping: list<array{column: string, value: string}>,
     *     smart_logic: array<string, mixed>,
     *     enabled: bool
     * }
     * @throws InvalidArgumentException
     */
    public function sanitizeCreateData(array $params): array
    {
        $sanitizers = $this->fieldSanitizers();

        return [
            'form_id'          => Sanitizer::sanitizeId((int) ($params['form_id'] ?? 0)),
            'spreadsheet_id'   => $sanitizers['spreadsheet_id']($params['spreadsheet_id'] ?? ''),
            'spreadsheet_name' => $sanitizers['spreadsheet_name']($params['spreadsheet_name'] ?? ''),
            'worksheet_name'   => $sanitizers['worksheet_name']($params['worksheet_name'] ?? ''),
            'field_mapping'    => $sanitizers['field_mapping']($params['field_mapping'] ?? []),
            'smart_logic'      => $sanitizers['smart_logic']($params['smart_logic'] ?? []),
            'enabled'          => $sanitizers['enabled']($params['enabled'] ?? true),
        ];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function buildUpdateData(array $params): array
    {
        $updateData = [];

        foreach ($this->fieldSanitizers() as $field => $sanitizer) {
            if (isset($params[$field])) {
                $updateData[$field] = $sanitizer($params[$field]);
            }
        }

        // Close the hole when Pro has not enabled smart logic: never persist rules.
        if (!$this->isSmartLogicAllowed()) {
            $updateData['smart_logic'] = self::DISABLED_SMART_LOGIC;
        }

        return $updateData;
    }

    private function isSmartLogicAllowed(): bool
    {
        /**
         * Whether Google Sheets smart logic may be persisted.
         * Closed by default; Pro enables when licensed.
         *
         * @param bool $allowed
         */
        return (bool) apply_filters('ivyforms/google_sheets/allow_smart_logic', false);
    }
}
