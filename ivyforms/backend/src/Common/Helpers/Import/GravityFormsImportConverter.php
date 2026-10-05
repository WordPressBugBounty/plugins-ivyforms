<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Convert a Gravity Forms JSON export into the IvyForms import payload structure.
 */
class GravityFormsImportConverter
{
    /**
     * Gravity Forms field types that have IvyForms equivalents.
     *
     * CAPTCHA maps to reCAPTCHA because Gravity's math/simple/Google widgets
     * have no matching IvyForms UI; the native security field is used instead.
     *
     * @var array<string, string>
     */
    private const TYPE_MAP = [
        'text'         => 'text',
        'username'     => 'text',
        'textarea'     => 'textarea',
        'email'        => 'email',
        'number'       => 'number',
        'select'       => 'select',
        'multiselect'  => 'multi-select',
        'radio'        => 'radio',
        'multi_choice' => 'radio',
        'image_choice' => 'radio',
        'checkbox'     => 'checkbox',
        'consent'      => 'gdpr',
        'phone'        => 'phone',
        'website'      => 'website',
        'date'         => 'date',
        'time'         => 'time',
        'fileupload'   => 'file-upload',
        'html'         => 'html',
        'captcha'      => 'recaptcha',
        'section'      => 'section',
        'product'      => 'product',
        'quantity'     => 'quantity',
        'total'        => 'total',
    ];

    /**
     * Convert one or more exported Gravity Forms forms.
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
        if (isset($rawData['title'])) {
            return [$rawData];
        }

        unset($rawData['version']);

        return array_values($rawData);
    }

    /**
     * @param array<string, mixed> $formRaw
     * @return array<string, mixed>
     */
    private static function convertSingleForm(array $formRaw): array
    {
        $description = sanitize_textarea_field((string) ($formRaw['description'] ?? ''));

        return [
            'form' => [
                'name'              => sanitize_text_field((string) ($formRaw['title'] ?? '')),
                'description'       => $description,
                'published'         => true,
                'showTitle'         => true,
                'showDescription'   => ($description !== ''),
                'storeEntries'      => true,
                'formActionButtons' => [
                    'submitButtonSettings' => [
                        'label'    => GravityFormsSubmitLabelMapper::extract($formRaw),
                        'position' => 'default',
                    ],
                ],
            ],
            'fields'        => self::extractFields($formRaw),
            'notifications' => [],
            'confirmation'  => self::extractConfirmation($formRaw),
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

        foreach (($formRaw['fields'] ?? []) as $rawField) {
            if (!is_array($rawField)) {
                continue;
            }

            if (($rawField['type'] ?? '') === 'name') {
                GravityFormsNameFieldMapper::append($rawField, $fields, $context);
                continue;
            }
            if (($rawField['type'] ?? '') === 'address') {
                GravityFormsAddressFieldMapper::append($rawField, $fields, $context);
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
        $gravityType = (string) ($rawField['type'] ?? '');
        $ivyType = self::TYPE_MAP[$gravityType] ?? null;
        if ($ivyType === null) {
            return null;
        }
        $ivyType = GravityFormsFieldSettingsMapper::resolveType($gravityType, $ivyType, $rawField);

        $htmlContent = $ivyType === 'html'
            ? wp_kses_post((string) ($rawField['content'] ?? ''))
            : '';
        if ($ivyType === 'html' && $htmlContent === '') {
            return null;
        }

        $field = self::buildFieldBase($rawField, $ivyType, $context);
        if ($ivyType === 'html') {
            $field['htmlContent'] = $htmlContent;
        }

        $field['confirmFieldEnabled'] = !empty($rawField['emailConfirmEnabled']);

        $options = self::extractOptions($rawField);
        if (!empty($options)) {
            $field['fieldOptions'] = $options;
        }
        GravityFormsFieldSettingsMapper::apply($field, $rawField, $gravityType, $ivyType, $options);

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
        $defaultValue = $rawField['defaultValue'] ?? '';

        return [
            'id'              => $context['id']++,
            'fieldIndex'      => $context['fieldIndex']++,
            'type'            => $ivyType,
            'label'           => sanitize_text_field((string) ($rawField['label'] ?? '')),
            'placeholder'     => sanitize_text_field((string) ($rawField['placeholder'] ?? '')),
            'description'     => sanitize_text_field((string) ($rawField['description'] ?? '')),
            'required'        => !empty($rawField['isRequired']),
            'requiredMessage' => sanitize_text_field((string) ($rawField['errorMessage'] ?? '')),
            'defaultValue'    => is_scalar($defaultValue) ? sanitize_text_field((string) $defaultValue) : '',
            'hideLabel'       => (($rawField['labelPlacement'] ?? '') === 'hidden_label'),
            'parentId'        => null,
            'position'        => $context['position']++,
            'rowIndex'        => $context['rowIndex']++,
            'columnIndex'     => 0,
            'width'           => 100,
        ];
    }

    /**
     * @param array<string, mixed> $rawField
     * @return array<int, array<string, mixed>>
     */
    private static function extractOptions(array $rawField): array
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

            $label = sanitize_text_field((string) ($rawOption['text'] ?? ''));
            $value = sanitize_text_field((string) ($rawOption['value'] ?? $label));
            if ($label === '' && $value === '') {
                continue;
            }

            $position = count($options) + 1;
            $options[] = [
                'id'        => $position,
                'label'     => $label !== '' ? $label : $value,
                'value'     => $value !== '' ? $value : $label,
                'isDefault' => !empty($rawOption['isSelected']),
                'position'  => $position,
            ];
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $formRaw
     * @return array<string, mixed>
     */
    private static function extractConfirmation(array $formRaw): array
    {
        $confirmations = $formRaw['confirmations'] ?? [];
        if (!is_array($confirmations)) {
            return [];
        }

        $confirmation = GravityFormsFieldSettingsMapper::findDefaultConfirmation($confirmations);
        if ($confirmation === null) {
            return [];
        }

        $type = (string) ($confirmation['type'] ?? 'message');
        if ($type === 'redirect' && !empty($confirmation['url'])) {
            return self::redirectConfirmation(
                'redirectToCustomUrl',
                'url',
                esc_url_raw((string) $confirmation['url'])
            );
        }

        if ($type === 'page' && !empty($confirmation['pageId'])) {
            return self::redirectConfirmation('redirectToPage', 'page', (string) $confirmation['pageId']);
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
}
