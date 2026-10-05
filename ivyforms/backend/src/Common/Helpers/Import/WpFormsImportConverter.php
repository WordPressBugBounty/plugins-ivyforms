<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Convert a WPForms JSON export into the IvyForms import payload structure.
 */
class WpFormsImportConverter
{
    /**
     * WPForms field types that have direct IvyForms equivalents.
     *
     * @var array<string, string>
     */
    private const TYPE_MAP = [
        'text'         => 'text',
        'textarea'     => 'textarea',
        'richtext'     => 'textarea',
        'email'        => 'email',
        'number'       => 'number',
        'number-slider' => 'slider',
        'select'       => 'select',
        'radio'        => 'radio',
        'checkbox'     => 'checkbox',
        'gdpr-checkbox' => 'gdpr',
        'phone'        => 'phone',
        'url'          => 'website',
        'date-time'    => 'date',
        'rating'       => 'rating',
        'file-upload'  => 'file-upload',
        'camera'       => 'file-upload',
        'html'         => 'html',
        'content'      => 'html',
        'divider'      => 'section',
        'pagebreak'    => 'page',
        'payment-single'   => 'product',
        'payment-checkbox' => 'product',
        'payment-multiple' => 'product',
        'payment-select'   => 'product',
        'payment-total'    => 'total',
    ];

    /**
     * Convert one or more exported WPForms forms.
     *
     * @param array<string|int, mixed> $rawData
     * @return array<string, mixed>
     */
    public static function convert(array $rawData): array
    {
        $forms = [];
        foreach (self::normalizeToList($rawData) as $formRaw) {
            if (is_array($formRaw)) {
                $forms[] = self::convertSingleForm($formRaw);
            }
        }

        return ['forms' => $forms];
    }

    /**
     * @param array<string|int, mixed> $rawData
     * @return array<int, mixed>
     */
    private static function normalizeToList(array $rawData): array
    {
        return isset($rawData['fields']) ? [$rawData] : array_values($rawData);
    }

    /**
     * @param array<string, mixed> $formRaw
     * @return array<string, mixed>
     */
    private static function convertSingleForm(array $formRaw): array
    {
        $settings = self::child($formRaw, 'settings');
        $description = sanitize_textarea_field((string) ($settings['form_desc'] ?? ''));
        $submitLabel = sanitize_text_field((string) ($settings['submit_text'] ?? ''));

        return [
            'form' => [
                'name'              => sanitize_text_field((string) ($settings['form_title'] ?? '')),
                'description'       => $description,
                'published'         => true,
                'showTitle'         => true,
                'showDescription'   => ($description !== ''),
                'storeEntries'      => empty($settings['disable_entries']),
                'formActionButtons' => [
                    'submitButtonSettings' => [
                        'label'    => $submitLabel,
                        'position' => 'default',
                    ],
                ],
            ],
            'fields'        => self::extractFields($formRaw),
            'notifications' => [],
            'confirmation'  => self::extractConfirmation($settings),
        ];
    }

    /**
     * @param array<string, mixed> $formRaw
     * @return array<int, array<string, mixed>>
     */
    private static function extractFields(array $formRaw): array
    {
        $context = ['id' => 1, 'fieldIndex' => 1, 'position' => 1, 'rowIndex' => 0];
        $fields = [];

        foreach (self::child($formRaw, 'fields') as $rawField) {
            if (!is_array($rawField)) {
                continue;
            }

            if (($rawField['type'] ?? '') === 'name') {
                WpFormsNameFieldMapper::append($rawField, $fields, $context);
                continue;
            }
            if (($rawField['type'] ?? '') === 'address') {
                WpFormsAddressFieldMapper::append($rawField, $fields, $context);
                continue;
            }

            $field = self::convertField($rawField, $context);
            if ($field !== null) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param array<string, mixed> $rawField
     * @param array<string, int> $context
     * @return array<string, mixed>|null
     */
    private static function convertField(array $rawField, array &$context): ?array
    {
        $wpFormsType = (string) ($rawField['type'] ?? '');
        $ivyType = self::TYPE_MAP[$wpFormsType] ?? null;
        if ($ivyType === null) {
            return null;
        }
        $ivyType = WpFormsFieldSettingsMapper::resolveType($wpFormsType, $ivyType, $rawField);

        $htmlContent = self::extractHtml($rawField);
        if ($ivyType === 'html' && $htmlContent === '') {
            return null;
        }

        $field = self::buildFieldBase($rawField, $ivyType, $context);
        if ($ivyType === 'html') {
            $field['htmlContent'] = $htmlContent;
        }

        $options = self::extractOptions($rawField, $wpFormsType);
        if (!empty($options)) {
            $field['fieldOptions'] = $options;
        }
        WpFormsFieldSettingsMapper::apply($field, $rawField, $wpFormsType, $ivyType, $options);

        return $field;
    }

    /**
     * @param array<string, mixed> $rawField
     * @param string $ivyType
     * @param array<string, int> $context
     * @return array<string, mixed>
     */
    private static function buildFieldBase(array $rawField, string $ivyType, array &$context): array
    {
        $defaultValue = $rawField['default_value'] ?? '';
        $label = (string) ($rawField['label'] ?? '');
        if ($label === '' && $ivyType === 'html') {
            $label = (string) ($rawField['name'] ?? '');
        }

        return [
            'id'              => $context['id']++,
            'fieldIndex'      => $context['fieldIndex']++,
            'type'            => $ivyType,
            'label'           => sanitize_text_field($label),
            'placeholder'     => sanitize_text_field((string) ($rawField['placeholder'] ?? '')),
            'description'     => sanitize_text_field((string) ($rawField['description'] ?? '')),
            'required'        => !empty($rawField['required']),
            'requiredMessage' => '',
            'defaultValue'    => is_scalar($defaultValue) ? sanitize_text_field((string) $defaultValue) : '',
            'hideLabel'       => !empty($rawField['label_hide']) || !empty($rawField['label_disable']),
            'parentId'        => null,
            'position'        => $context['position']++,
            'rowIndex'        => $context['rowIndex']++,
            'columnIndex'     => 0,
            'width'           => 100,
        ];
    }

    /**
     * @param array<string, mixed> $rawField
     * @param string $wpFormsType
     * @return array<int, array<string, mixed>>
     */
    private static function extractOptions(array $rawField, string $wpFormsType): array
    {
        $rawOptions = $rawField['choices'] ?? [];
        if (!is_array($rawOptions)) {
            return [];
        }

        $options = [];
        foreach ($rawOptions as $rawOption) {
            if (!is_array($rawOption)) {
                continue;
            }

            $label = sanitize_text_field((string) ($rawOption['label'] ?? ''));
            $rawValue = strpos($wpFormsType, 'payment-') === 0
                ? $label
                : ($rawOption['value'] ?? $label);
            $value = sanitize_text_field((string) $rawValue);
            if ($label === '' && $value === '') {
                continue;
            }

            $position = count($options) + 1;
            $options[] = [
                'id'        => $position,
                'label'     => $label !== '' ? $label : $value,
                'value'     => $value !== '' ? $value : $label,
                'isDefault' => !empty($rawOption['default']),
                'position'  => $position,
            ];
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $rawField
     * @return string
     */
    private static function extractHtml(array $rawField): string
    {
        foreach (['code', 'content'] as $key) {
            if (!empty($rawField[$key]) && is_string($rawField[$key])) {
                return wp_kses_post($rawField[$key]);
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private static function extractConfirmation(array $settings): array
    {
        $confirmations = self::child($settings, 'confirmations');
        $confirmation = reset($confirmations);
        if (!is_array($confirmation)) {
            return [];
        }

        $type = (string) ($confirmation['type'] ?? 'message');
        if ($type === 'redirect' && !empty($confirmation['redirect'])) {
            return self::redirectConfirmation(
                'redirectToCustomUrl',
                'url',
                esc_url_raw((string) $confirmation['redirect'])
            );
        }

        if ($type === 'page' && !empty($confirmation['page'])) {
            return self::redirectConfirmation('redirectToPage', 'page', (string) $confirmation['page']);
        }

        $message = (string) ($confirmation['message'] ?? '');

        return $message === '' ? [] : [
            'type'     => 'successMessage',
            'enabled'  => true,
            'showForm' => false,
            'message'  => wp_kses_post($message),
            'url'      => '',
            'page'     => '',
        ];
    }

    /**
     * @param string $type
     * @param string $targetKey
     * @param string $targetValue
     * @return array<string, mixed>
     */
    private static function redirectConfirmation(string $type, string $targetKey, string $targetValue): array
    {
        return array_merge(
            [
                'type'     => $type,
                'enabled'  => true,
                'showForm' => false,
                'message'  => '',
                'url'      => '',
                'page'     => '',
            ],
            [$targetKey => $targetValue]
        );
    }

    /**
     * @param array<string, mixed> $source
     * @param string $key
     * @return array<string, mixed>
     */
    private static function child(array $source, string $key): array
    {
        return is_array($source[$key] ?? null) ? $source[$key] : [];
    }
}
