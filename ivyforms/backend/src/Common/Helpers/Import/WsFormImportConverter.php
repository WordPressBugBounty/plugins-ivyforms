<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Convert a WS Form JSON export into the IvyForms import payload structure.
 *
 * Unsupported WS Form field types and platform-specific settings are skipped
 * gracefully so the import never fails because of them.
 */
class WsFormImportConverter
{
    /**
     * Map WS Form field types to IvyForms field types. Types absent from this
     * map (submit, signature, captcha, etc.) are skipped during import.
     *
     * @var array<string, string>
     */
    private const TYPE_MAP = [
        'text'      => 'text',
        'email'     => 'email',
        'tel'       => 'phone',
        'url'       => 'website',
        'number'    => 'number',
        'textarea'  => 'textarea',
        'select'    => 'select',
        'radio'     => 'radio',
        'checkbox'  => 'checkbox',
        'date'      => 'date',
        'datetime'  => 'date',
        'time'      => 'time',
        'range'     => 'slider',
        'rating'    => 'rating',
        'file'      => 'file-upload',
        'html'      => 'html',
        'message'   => 'html',
    ];

    /**
     * Field types that carry selectable options.
     *
     * @var array<int, string>
     */
    private const OPTION_TYPES = ['select', 'radio', 'checkbox', 'multi-select'];

    /**
     * Convert a raw WS Form export array into an IvyForms import payload.
     *
     * @param array<string, mixed> $rawData
     * @return array<string, mixed>
     */
    public static function convert(array $rawData): array
    {
        $name = isset($rawData['label']) ? sanitize_text_field((string) $rawData['label']) : '';
        $published = isset($rawData['status']) && $rawData['status'] === 'publish';

        return [
            'forms' => [
                [
                    'form' => [
                        'name'            => $name,
                        'published'       => $published,
                        'showTitle'       => true,
                        'showDescription' => false,
                        'storeEntries'    => true,
                    ],
                    'fields'        => self::extractFields($rawData),
                    'notifications' => [],
                    'confirmation'  => self::extractConfirmation($rawData),
                ],
            ],
        ];
    }

    /**
     * Walk the groups/sections/fields tree and build IvyForms fields.
     *
     * @param array<string, mixed> $rawData
     * @return array<int, array<string, mixed>>
     */
    private static function extractFields(array $rawData): array
    {
        $context = ['id' => 1, 'fieldIndex' => 1, 'position' => 1, 'rowIndex' => 0];
        $wsFields = self::flattenFields($rawData);
        $total = count($wsFields);
        $fields = [];

        for ($index = 0; $index < $total; $index++) {
            // WS Form splits names across text fields; merge them back into a
            // single IvyForms name field when the parts can be identified.
            $nameRun = WsFormNameFieldExtractor::collectRun($wsFields, $index);
            if ($nameRun !== null) {
                WsFormNameFieldExtractor::append($nameRun, $fields, $context);
                $index += count($nameRun) - 1;
                continue;
            }

            self::appendField($wsFields[$index], $fields, $context);
        }

        return $fields;
    }

    /**
     * Flatten the groups/sections/fields tree into an ordered list of fields.
     *
     * @param array<string, mixed> $rawData
     * @return array<int, array<string, mixed>>
     */
    private static function flattenFields(array $rawData): array
    {
        $wsFields = [];

        foreach (($rawData['groups'] ?? []) as $group) {
            if (!is_array($group)) {
                continue;
            }
            foreach (($group['sections'] ?? []) as $section) {
                if (!is_array($section)) {
                    continue;
                }
                foreach (($section['fields'] ?? []) as $wsField) {
                    if (is_array($wsField)) {
                        $wsFields[] = $wsField;
                    }
                }
            }
        }

        return $wsFields;
    }

    /**
     * Map a single WS Form field and append it to the running fields list.
     *
     * @param array<string, mixed> $wsField
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @return void
     */
    private static function appendField(array $wsField, array &$fields, array &$context): void
    {
        $wsType = isset($wsField['type']) ? (string) $wsField['type'] : '';
        $ivyType = self::TYPE_MAP[$wsType] ?? null;
        if ($ivyType === null) {
            return;
        }

        $meta = is_array($wsField['meta'] ?? null) ? $wsField['meta'] : [];
        $ivyType = self::resolveType($wsType, $ivyType, $meta);
        $htmlContent = self::resolveHtmlContent($ivyType, $meta, $wsType);
        if ($ivyType === 'html' && $htmlContent === null) {
            return;
        }

        $field = self::buildFieldBase($wsField, $meta, $ivyType, $context);
        if ($htmlContent !== null) {
            $field['htmlContent'] = $htmlContent;
        }

        if (WsFormGdprFieldMapper::matches($wsType, $meta)) {
            WsFormGdprFieldMapper::apply($field, $meta);
            $fields[] = $field;
            return;
        }

        self::attachOptions($field, $meta, $wsType, $ivyType);
        $fields[] = $field;
    }

    /**
     * Build the common IvyForms field properties from a WS Form field.
     *
     * @param array<string, mixed> $wsField
     * @param array<string, mixed> $meta
     * @param string $ivyType
     * @param array<string, int> $context
     * @return array<string, mixed>
     */
    private static function buildFieldBase(
        array $wsField,
        array $meta,
        string $ivyType,
        array &$context
    ): array {
        return [
            'id'              => $context['id']++,
            'fieldIndex'      => $context['fieldIndex']++,
            'type'            => $ivyType,
            'label'           => sanitize_text_field((string) ($wsField['label'] ?? '')),
            'placeholder'     => sanitize_text_field((string) ($meta['placeholder'] ?? '')),
            'description'     => sanitize_text_field((string) ($meta['help'] ?? '')),
            'required'        => (($meta['required'] ?? '') === 'on'),
            'requiredMessage' => sanitize_text_field((string) ($meta['invalid_feedback'] ?? '')),
            'defaultValue'    => sanitize_text_field((string) ($meta['default_value'] ?? '')),
            'hideLabel'       => isset($meta['label_render']) && $meta['label_render'] !== 'on',
            'parentId'        => null,
            'position'        => $context['position']++,
            'rowIndex'        => $context['rowIndex']++,
            'columnIndex'     => 0,
            'width'           => 100,
        ];
    }

    /**
     * Attach selectable options when the mapped type supports them.
     *
     * @param array<string, mixed> $field
     * @param array<string, mixed> $meta
     * @param string $wsType
     * @param string $ivyType
     * @return void
     */
    private static function attachOptions(array &$field, array $meta, string $wsType, string $ivyType): void
    {
        if (!in_array($ivyType, self::OPTION_TYPES, true)) {
            return;
        }

        $options = WsFormOptionsExtractor::extract($meta, $wsType);
        if (!empty($options)) {
            $field['fieldOptions'] = $options;
        }
    }

    /**
     * Resolve IvyForms type refinements (e.g. multi-select).
     *
     * @param string $wsType
     * @param string $ivyType
     * @param array<string, mixed> $meta
     * @return string
     */
    private static function resolveType(string $wsType, string $ivyType, array $meta): string
    {
        if ($wsType === 'select' && ($meta['multiple'] ?? '') === 'on') {
            return 'multi-select';
        }

        return $ivyType;
    }

    /**
     * Resolve HTML content for html-mapped fields, or null when empty/skipped.
     *
     * @param string $ivyType
     * @param array<string, mixed> $meta
     * @param string $wsType
     * @return string|null
     */
    private static function resolveHtmlContent(string $ivyType, array $meta, string $wsType): ?string
    {
        if ($ivyType !== 'html') {
            return null;
        }

        $htmlContent = self::extractHtmlContent($meta, $wsType);

        return $htmlContent !== '' ? $htmlContent : null;
    }

    /**
     * Extract sanitized HTML content from WS Form field meta.
     *
     * HTML fields store content in `html_editor_content`; message fields use
     * `text_editor`. Empty HTML fields are skipped (nothing useful to import).
     *
     * @param array<string, mixed> $meta
     * @param string $wsType
     * @return string
     */
    private static function extractHtmlContent(array $meta, string $wsType): string
    {
        $candidates = $wsType === 'message'
            ? ['text_editor', 'html_editor_content', 'html_content']
            : ['html_editor_content', 'html_content', 'text_editor'];

        foreach ($candidates as $key) {
            if (!empty($meta[$key]) && is_string($meta[$key])) {
                return wp_kses_post($meta[$key]);
            }
        }

        return '';
    }

    /**
     * Build the IvyForms confirmation from a WS Form "Show Message" action.
     *
     * @param array<string, mixed> $rawData
     * @return array<string, mixed>
     */
    private static function extractConfirmation(array $rawData): array
    {
        $actionGrid = $rawData['meta']['action'] ?? null;
        if (!is_array($actionGrid)) {
            return [];
        }

        foreach (($actionGrid['groups'] ?? []) as $actionGroup) {
            foreach (($actionGroup['rows'] ?? []) as $row) {
                $rowData = is_array($row['data'] ?? null) ? $row['data'] : [];
                if (!isset($rowData[1])) {
                    continue;
                }

                $config = json_decode((string) $rowData[1], true);
                if (!is_array($config) || ($config['id'] ?? '') !== 'message') {
                    continue;
                }

                $message = (string) ($config['meta']['action_message_message'] ?? '');
                if ($message === '') {
                    continue;
                }

                return [
                    'type'     => 'successMessage',
                    'enabled'  => true,
                    'showForm' => false,
                    'message'  => wp_kses_post($message),
                    'url'      => '',
                    'page'     => '',
                ];
            }
        }

        return [];
    }
}
