<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Sanitizer;

use IvyForms\Common\Constants\SupportedCurrencyCodes;
use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\Field\FieldType;
use IvyForms\Services\Form\Style\StyleConstants;
use IvyForms\Services\Template\TemplateDefaults;
use IvyForms\Services\Translations\BackendStrings;

class Sanitizer
{
    public const FORM_TYPE_CLASSIC = 'classic';
    public const FORM_TYPE_CONVERSATIONAL = 'conversational';

    /**
     * Sanitize a field default value based on field type.
     * Pro field types can override via ivyforms/sanitizer/default_value.
     *
     * @param string $fieldType
     * @param mixed $value
     *
     * @return string
     */
    private static function sanitizeDefaultValue(string $fieldType, $value): string
    {
        if (is_array($value)) {
            $sanitized = array_map(function ($item) {
                return sanitize_text_field(is_scalar($item) ? (string) $item : '');
            }, array_slice(array_values($value), 0, 2));

            return wp_json_encode($sanitized) ?: '';
        }

        $value = is_scalar($value) ? (string) $value : '';

        if ($fieldType === 'rich_text') {
            /**
             * @param string|null $sanitized
             * @param string $type
             * @param string $rawValue
             */
            $filtered = apply_filters('ivyforms/sanitizer/default_value', null, $fieldType, $value);
            if ($filtered !== null) {
                return (string) $filtered;
            }

            return HtmlSanitizer::sanitizeEditorContent($value);
        }

        /**
         * @param string|null $sanitized
         * @param string $type
         * @param string $rawValue
         */
        $filtered = apply_filters('ivyforms/sanitizer/default_value', null, $fieldType, $value);
        if ($filtered !== null) {
            return (string) $filtered;
        }

        return sanitize_text_field($value);
    }

    /**
     * Sanitize a submission value for an unknown or extension field type.
     * Pro/add-ons register ivyforms/sanitizer/submission_field_value.
     *
     * @param string $fieldType
     * @param mixed $value
     *
     * @return mixed|null Filtered value, or null to use lite defaults.
     */
    private static function filterSubmissionFieldValue(string $fieldType, $value)
    {
        /**
         * @param mixed|null $sanitized
         * @param string $type
         * @param mixed $rawValue
         */
        return apply_filters('ivyforms/sanitizer/submission_field_value', null, $fieldType, $value);
    }

    /**
     * Sanitize form action buttons settings
     *
     * @param mixed[] $settings
     *
     * @return mixed[]
     */
    public static function sanitizeFormActionButtons(array $settings): array
    {
        $submitButtonSettings = $settings['submitButtonSettings'] ?? [];
        $validPositions = ['default', 'left', 'center', 'right'];
        $position = $submitButtonSettings['position'] ?? 'default';

        // Validate position
        if (!in_array($position, $validPositions, true)) {
            $position = 'default';
        }

        $sanitized = [
            'submitButtonSettings' => [
                'label' => sanitize_text_field(
                    $submitButtonSettings['label'] ?? BackendStrings::getCommonStrings()['submit']
                ),
                'position' => $position
            ]
        ];

        // Allow extensions (e.g. Pro) to merge pageNavigationSettings and other keys.
        // Always call apply_filters; do not gate on has_filter (extensions register at plugins_loaded).
        return apply_filters('ivyforms/sanitize/form_action_buttons', $sanitized, $settings);
    }

    /**
     * Sanitize payment settings payload (inner shape stored in form settings / API).
     * Mirrors {@see self::sanitizeIntegrationSettings()}: invalid input normalizes to defaults.
     *
     * @param mixed $paymentSettings
     *
     * @return array{currency:string}
     */
    public static function sanitizePaymentSettings($paymentSettings): array
    {
        if (!is_array($paymentSettings)) {
            return self::sanitizePaymentSettingsInner([]);
        }

        return self::sanitizePaymentSettingsInner($paymentSettings);
    }

    /**
     * Whitelist known payment setting keys and coerce types.
     *
     * @param array<string, mixed> $paymentSettings
     *
     * @return array{currency:string}
     */
    private static function sanitizePaymentSettingsInner(array $paymentSettings): array
    {
        $code = strtoupper(trim(sanitize_text_field($paymentSettings['currency'] ??
        SupportedCurrencyCodes::DEFAULT_CODE)));
        if (!SupportedCurrencyCodes::isAllowed($code)) {
            $code = SupportedCurrencyCodes::DEFAULT_CODE;
        }

        return [
            'currency' => $code,
        ];
    }

    /**
     * Normalize form presentation type.
     *
     * The default allowed types are 'classic' (always) and 'conversational' (only when the Pro
     * gate filter `ivyforms/form/can_use_form_type_conversational` returns true). Additional
     * types (e.g. quizzes, polls, user-defined types) can be registered via the
     * `ivyforms/form/allowed_form_types` filter, which receives the resolved allowlist and
     * must return an array of lowercase string identifiers.
     *
     * @param mixed $raw
     * @return string
     */
    public static function sanitizeFormType($raw): string
    {
        $value = strtolower(sanitize_text_field(is_scalar($raw) ? (string) $raw : ''));

        if (in_array($value, self::getAllowedFormTypes(), true)) {
            return $value;
        }

        return self::FORM_TYPE_CLASSIC;
    }

    /**
     * Build the allowlist of form types accepted by sanitizeFormType().
     *
     * @return string[]
     */
    public static function getAllowedFormTypes(): array
    {
        $types = [self::FORM_TYPE_CLASSIC];

        if (apply_filters('ivyforms/form/can_use_form_type_conversational', false)) {
            $types[] = self::FORM_TYPE_CONVERSATIONAL;
        }

        $filtered = apply_filters('ivyforms/form/allowed_form_types', $types);

        if (!is_array($filtered) || $filtered === []) {
            return [self::FORM_TYPE_CLASSIC];
        }

        $normalized = array_values(array_unique(array_filter(array_map(
            static function ($type) {
                return is_string($type) ? strtolower(trim($type)) : '';
            },
            $filtered
        ))));

        if (!in_array(self::FORM_TYPE_CLASSIC, $normalized, true)) {
            $normalized[] = self::FORM_TYPE_CLASSIC;
        }

        return $normalized;
    }

    /**
     * Sanitize all form data
     *
     * @param mixed[] $data
     * @return mixed[]
     * @throws InvalidArgumentException
     */
    public static function sanitizeFormData(array $data): array
    {
        $fields = $data['fields'] ?? [];
        $formTypeRaw = $data['formType'] ?? 'classic';

        $sanitized = [
            'id'                    => (int)($data['id'] ?? 0),
            'name'                  => sanitize_text_field($data['name'] ?? ''),
            'description'           => sanitize_text_field($data['description'] ?? ''),
            'formType'              => self::sanitizeFormType($formTypeRaw),
            'showTitle'             => (bool)($data['showTitle'] ?? false),
            'published'             => (bool)($data['published'] ?? false),
            'showDescription'       => (bool)($data['showDescription'] ?? false),
            'storeEntries'          => (bool)($data['storeEntries'] ?? false),
            'fields'                => self::sanitizeFields($fields),
            'integrationSettings'   => self::sanitizeIntegrationSettings(
                $data['integrationSettings'] ?? []
            ),
            'styleSettings'         => self::sanitizeStyleSettings($data['styleSettings'] ?? null),
            'formActionButtons'     => self::sanitizeFormActionButtons(
                $data['formActionButtons'] ?? []
            ),
            'paymentSettings'       => self::sanitizePaymentSettings(
                $data['paymentSettings'] ?? []
            ),
        ];

        /**
         * Allows Pro conversational hooks to enrich sanitized form data.
         *
         * @param array<string, mixed> $sanitized The sanitized form data
         * @param array<string, mixed> $data The raw form data
         * @return array<string, mixed> The modified sanitized data
         */
        $defaults = $sanitized;
        $sanitized = apply_filters('ivyforms/form/sanitized_form_data', $sanitized, $data);
        if (!is_array($sanitized)) {
            $sanitized = $defaults;
        }

        /**
         * Allows Pro plugin to add additional form data properties (pages, progressIndicator, etc.)
         *
         * @since 0.1.0
         *
         * @param array<string, mixed> $sanitized The sanitized form data
         * @param array<string, mixed> $data The raw form data
         * @return array<string, mixed> The modified sanitized data
         */
        $filtered = apply_filters('ivyforms/sanitize/form_data', $sanitized, $data);
        if (!is_array($filtered)) {
            $filtered = $sanitized;
        }

        if (!array_key_exists('name', $filtered)) {
            $filtered['name'] = $sanitized['name'] ?? $defaults['name'];
        }

        return $filtered;
    }
    /**
     * Sanitize all notification data
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public static function sanitizeNotificationData(array $data): array
    {
        return [
            'id'          => (int)($data['id'] ?? 0),
            'name'        => sanitize_text_field($data['name'] ?? ''),
            'sender'      => self::sanitizeEmailOrPlaceholder($data['sender'] ?? ''),
            'replyTo'     => self::sanitizeEmailOrPlaceholder($data['replyTo'] ?? ''),
            'receiver'    => self::sanitizeEmailOrPlaceholder($data['receiver'] ?? ''),
            'enabled'     => (bool)($data['enabled'] ?? true),
            'subject'     => sanitize_text_field($data['subject'] ?? ''),
            'message'         => HtmlSanitizer::sanitizeEditorContent($data['message'] ?? ''),
            'showEmptyFields' => (bool)($data['showEmptyFields'] ?? false),
            'smartLogic'      => self::sanitizeNotificationSmartLogic($data['smartLogic'] ?? false),
            'formId'      => (int)($data['formId'] ?? 0),
        ];
    }

    /**
     * Sanitize notification smart logic payload.
     *
     * @param mixed $smartLogic
     * @return array<string, mixed>
     */
    public static function sanitizeNotificationSmartLogic($smartLogic): array
    {
        if (!is_array($smartLogic)) {
            return [
                'enabled' => (bool)$smartLogic,
                'match' => 'any',
                'rules' => [],
            ];
        }

        $match = sanitize_text_field((string)($smartLogic['match'] ?? 'any'));
        if (!in_array($match, ['any', 'all'], true)) {
            $match = 'any';
        }

        $rules = [];
        $rawRules = $smartLogic['rules'] ?? [];
        if (is_array($rawRules)) {
            foreach ($rawRules as $rule) {
                if (!is_array($rule)) {
                    continue;
                }

                $operator = sanitize_text_field((string)($rule['operator'] ?? 'equals'));
                if (!in_array($operator, ['equals', 'not_equals'], true)) {
                    $operator = 'equals';
                }

                $field = isset($rule['field']) ? sanitize_text_field((string)$rule['field']) : null;

                $rules[] = [
                    'id' => isset($rule['id']) ? (int)$rule['id'] : 0,
                    'field' => $field === '' ? null : $field,
                    'operator' => $operator,
                    'value' => sanitize_text_field((string)($rule['value'] ?? '')),
                ];
            }
        }

        return [
            'enabled' => (bool)($smartLogic['enabled'] ?? false),
            'match' => $match,
            'rules' => $rules,
        ];
    }
    /**
     * Sanitize all confirmation data
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public static function sanitizeConfirmationData(array $data): array
    {
        return [
            'id'         => (int)($data['id'] ?? 0),
            'formId'     => (int)($data['formId'] ?? 0),
            'name'       => sanitize_text_field($data['name'] ?? ''),
            'type'       => sanitize_text_field($data['type'] ?? ''),
            'enabled'    => (bool)($data['enabled'] ?? true),
            'showForm'   => (bool)($data['showForm'] ?? false),
            'message'    => HtmlSanitizer::sanitizeEditorContent($data['message'] ?? ''),
            'url'        => sanitize_url(
                isset($data['url']) ? urldecode($data['url']) : ''
            ),
            'page'       => sanitize_text_field($data['page'] ?? ''),
            'position'   => (int)($data['position'] ?? 0),
            'isDefault'  => (bool)($data['isDefault'] ?? false),
            'smartLogic' => self::sanitizeNotificationSmartLogic($data['smartLogic'] ?? false),
        ];
    }

    /**
     * Sanitize confirmation reorder request payload.
     *
     * @param mixed[] $data
     *
     * @return array{formId: int, orderedIds: int[]}
     */
    public static function sanitizeConfirmationReorderData(array $data): array
    {
        $orderedIds = [];
        if (!empty($data['orderedIds']) && is_array($data['orderedIds'])) {
            foreach ($data['orderedIds'] as $id) {
                $orderedIds[] = (int) $id;
            }
        }

        return [
            'formId'     => (int) ($data['formId'] ?? 0),
            'orderedIds' => $orderedIds,
        ];
    }

    /**
     * Sanitize form fields
     *
     * @param mixed[] $fields
     *
     * @return mixed[]
     * @throws InvalidArgumentException
     */
    private static function sanitizeFields(array $fields): array
    {
        return array_map(function ($field) {
            // Validate field type
            $fieldType = sanitize_text_field($field['type'] ?? '');
            if (!FieldType::isValid($fieldType)) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_field_type']
                );
            }

            $sanitizedField = [
                'id'                        => (int)($field['id'] ?? 0),
                'fieldIndex'                => (int)($field['fieldIndex'] ?? 0),
                'type'                      => $fieldType,
                'label'                     => sanitize_text_field($field['label'] ?? ''),
                'required'                  => (bool)($field['required'] ?? false),
                'defaultValue'              => self::sanitizeDefaultValue(
                    $fieldType,
                    $field['defaultValue'] ?? ''
                ),
                'placeholder'               => sanitize_text_field($field['placeholder'] ?? ''),
                'position'                  => (int)($field['position'] ?? 0),
                'rowIndex'                  => max(0, (int)($field['rowIndex'] ?? 0)),
                'columnIndex'               => max(0, min(4, (int)($field['columnIndex'] ?? 0))),
                'width'                     => max(20, min(100, (int)($field['width'] ?? 100))),
                'parentId'                  => isset($field['parentId']) ? max(0, (int)$field['parentId']) : null,
                'hideLabel'                 => (bool)($field['hideLabel'] ?? false),
                'readOnly'                  => (bool)($field['readOnly'] ?? false),
                'description'               => HtmlSanitizer::sanitizeFieldDescription($field['description'] ?? ''),
                'requiredMessage'           => sanitize_text_field($field['requiredMessage'] ?? ''),
                'cssClasses'                => HtmlSanitizer::sanitizeCssClasses($field['cssClasses'] ?? ''),
                'fieldOptions'              => self::sanitizeFieldOptions($field['fieldOptions'] ?? []),
                'shuffleOptions'            => (bool)($field['shuffleOptions'] ?? false),
                'showValues'                => (bool)($field['showValues'] ?? false),
                'enableSearch'              => (bool)($field['enableSearch'] ?? false),
                'rows'                      => (int)($field['rows'] ?? 0),
                'confirmFieldEnabled'       => (bool)($field['confirmFieldEnabled'] ?? false),
                'confirmFieldLabel'         => sanitize_text_field($field['confirmFieldLabel'] ?? ''),
                'confirmFieldPlaceholder'   => sanitize_text_field($field['confirmFieldPlaceholder'] ?? ''),
                'confirmFieldHideLabel'     => (bool)($field['confirmFieldHideLabel'] ?? false),
                'phoneFormat'               => self::sanitizePhoneFormat($field['phoneFormat'] ?? ''),
                'phoneAutoDetect'           => (bool)($field['phoneAutoDetect'] ?? false),
                'minValue'                  => self::sanitizeNumericValue($field['minValue'] ?? null),
                'maxValue'                  => self::sanitizeNumericValue($field['maxValue'] ?? null),
                'step'                      => (float)($field['step'] ?? 1.0),
                'showGrid'                  => (bool)($field['showGrid'] ?? false),
                'numberFormat'              => sanitize_text_field($field['numberFormat'] ?? ''),
                'visible'                   => (bool)($field['visible'] ?? true),
                'limitMaxLength'            => (bool)($field['limitMaxLength'] ?? false),
                'limitRange'                => (bool)($field['limitRange'] ?? false),
                'maxLength'                 => (int)($field['maxLength'] ?? 255),
                'customValidationMessage'   => sanitize_text_field($field['customValidationMessage'] ?? ''),
                'labelPosition'             => sanitize_text_field($field['labelPosition'] ?? 'default'),
                'noDuplicates'              => (bool)($field['noDuplicates'] ?? false),
                'inputPrefix'               => sanitize_text_field($field['inputPrefix'] ?? ''),
                'inputSuffix'               => sanitize_text_field($field['inputSuffix'] ?? ''),
                'timeFieldType'             => sanitize_text_field($field['timeFieldType'] ?? ''),
                'timeFormat'                => sanitize_text_field($field['timeFormat'] ?? ''),
                'dateFieldType'             => sanitize_text_field($field['dateFieldType'] ?? ''),
                'dateFormat'                => sanitize_text_field($field['dateFormat'] ?? ''),
                'minDateValue'              => sanitize_text_field($field['minDateValue'] ?? ''),
                'maxDateValue'              => sanitize_text_field($field['maxDateValue'] ?? ''),
                'showRatingText'            => (bool)($field['showRatingText'] ?? false),
                'ratingIcon'                => sanitize_text_field($field['ratingIcon'] ?? 'star'),
                'htmlContent'               => self::sanitizeFieldHtmlContentByType(
                    $fieldType,
                    (string)($field['htmlContent'] ?? '')
                ),
                'agreementText'             => HtmlSanitizer::sanitizeAgreementText($field['agreementText'] ?? ''),
                'limitDateRange'            => (bool)($field['limitDateRange'] ?? false),
                'minTimeValue'              => sanitize_text_field($field['minTimeValue'] ?? ''),
                'maxTimeValue'              => sanitize_text_field($field['maxTimeValue'] ?? ''),
                'limitTimeRange'            => (bool)($field['limitTimeRange'] ?? false),
                'maxFileSizeMb'             => max(0, min(9999, (int)($field['maxFileSizeMb'] ?? 0))),
                'saveUploadsTo'             => self::sanitizeSaveUploadsTo($field['saveUploadsTo'] ?? 'ivyforms'),
                'allowedFileExtensions'     => self::sanitizeAllowedFileExtensionsInput(
                    $field['allowedFileExtensions'] ?? []
                ),
                'pageId'                    => ($pageId = sanitize_text_field($field['pageId'] ?? ''))
                                            !== '' ? $pageId : null,
                'nameFieldType'             => sanitize_text_field(
                    $field['nameFieldType']
                        ?? (is_array($field['settings'] ?? null)
                            ? ($field['settings']['nameFieldType'] ?? '')
                            : '')
                ),
                'subFieldIndex'             => isset($field['subFieldIndex'])
                    ? (int)$field['subFieldIndex']
                    : (is_array($field['settings'] ?? null) && isset($field['settings']['subFieldIndex'])
                        ? (int)$field['settings']['subFieldIndex']
                        : null),
            ];

            /**
             * This filter allows to sanitize form fields properties
             * for fields that are not part of the core plugin.
             *
             * @since 0.1.0
             *
             * @param mixed[] $sanitizedField The sanitized field data so far.
             * @param mixed[] $field The original field data before sanitization.
             * @return mixed[] The modified sanitized field data.
             */
            return apply_filters('ivyforms/sanitizer/field_properties', $sanitizedField, $field);
        }, $fields);
    }

    /**
     * Sanitize field options
     *
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    private static function sanitizeFieldOptions(array $options): array
    {
        return array_map(function ($option) {
            return [
                'id'        => (int)($option['id'] ?? 0),
                'fieldId'   => (int)($option['fieldId'] ?? 0),
                'label'     => sanitize_text_field($option['label'] ?? ''),
                'value'     => sanitize_text_field($option['value'] ?? ''),
                'isDefault' => (bool)($option['isDefault'] ?? false),
                'position'  => (int)($option['position'] ?? 1),
            ];
        }, $options);
    }

    /**
     * Sanitize field htmlContent based on field type (JSON blobs vs rich HTML).
     *
     * @param string $fieldType
     * @param string $htmlContent
     * @return string
     */
    private static function sanitizeFieldHtmlContentByType(string $fieldType, string $htmlContent): string
    {
        if ($fieldType === 'quantity') {
            return self::sanitizeQuantityFieldHtmlContent($htmlContent);
        }

        if ($fieldType === 'total') {
            return self::sanitizeTotalFieldHtmlContent($htmlContent);
        }

        if ($fieldType === 'product') {
            return self::sanitizeProductFieldHtmlContent($htmlContent);
        }

        return HtmlSanitizer::sanitizeEditorContent($htmlContent);
    }

    /**
     * Decode htmlContent JSON object blob and re-encode via sanitizer callback.
     *
     * @param string $raw
     * @param callable $sanitizeConfig Receives decoded associative array (or empty for defaults).
     * @return string
     */
    private static function sanitizeJsonHtmlContentBlob(string $raw, callable $sanitizeConfig): string
    {
        $trimmed = trim($raw);
        if ($trimmed === '' || ($trimmed[0] ?? '') !== '{') {
            return wp_json_encode($sanitizeConfig([]), JSON_UNESCAPED_UNICODE) ?: '';
        }

        $decoded = json_decode($trimmed, true);
        if (!is_array($decoded)) {
            return wp_json_encode($sanitizeConfig([]), JSON_UNESCAPED_UNICODE) ?: '';
        }

        return wp_json_encode($sanitizeConfig($decoded), JSON_UNESCAPED_UNICODE) ?: '';
    }

    /**
     * Strict boolean for JSON field configs (only true or string "true").
     *
     * @param mixed $value
     * @return bool
     */
    private static function sanitizeJsonConfigBoolean($value): bool
    {
        return $value === true || $value === 'true';
    }

    /**
     * Sanitize quantity field linkage JSON stored in htmlContent.
     *
     * @param string $raw
     * @return string
     */
    private static function sanitizeQuantityFieldHtmlContent(string $raw): string
    {
        return self::sanitizeJsonHtmlContentBlob(
            $raw,
            static function (array $decoded): array {
                $linked = $decoded['linkedProductFieldIndex'] ?? null;
                $linkedIndex = null;
                if ($linked !== null && $linked !== '') {
                    $asInt = (int) $linked;
                    if ($asInt >= 0) {
                        $linkedIndex = $asInt;
                    }
                }

                return [
                    'quantityConfigVersion' => 1,
                    'linkedProductFieldIndex' => $linkedIndex,
                ];
            }
        );
    }

    /**
     * Sanitize total field JSON stored in htmlContent.
     *
     * @param string $raw
     * @return string
     */
    private static function sanitizeTotalFieldHtmlContent(string $raw): string
    {
        return self::sanitizeJsonHtmlContentBlob(
            $raw,
            static function (array $decoded): array {
                return self::sanitizeTotalFieldConfigArray($decoded);
            }
        );
    }

    /**
     * Sanitize product field JSON stored in htmlContent.
     *
     * @param string $raw
     * @return string
     */
    private static function sanitizeProductFieldHtmlContent(string $raw): string
    {
        return self::sanitizeJsonHtmlContentBlob(
            $raw,
            static function (array $decoded): array {
                return self::sanitizeProductFieldConfigArray($decoded);
            }
        );
    }

    /**
     * Normalize and sanitize total field configuration array (TotalFieldConfig).
     *
     * @param mixed[] $decoded
     * @return mixed[]
     */
    private static function sanitizeTotalFieldConfigArray(array $decoded): array
    {
        $emptyHtml = isset($decoded['emptyPaymentMessageHtml'])
            ? HtmlSanitizer::sanitizeEditorContent((string) $decoded['emptyPaymentMessageHtml'])
            : '';

        return [
            'totalConfigVersion' => 1,
            'enablePaymentSummary' => self::sanitizeJsonConfigBoolean($decoded['enablePaymentSummary'] ?? false),
            'showPaymentSummaryCloseButton' => self::sanitizeJsonConfigBoolean(
                $decoded['showPaymentSummaryCloseButton'] ?? true
            ),
            'emptyPaymentMessageHtml' => $emptyHtml,
            'enableAutocomplete' => self::sanitizeJsonConfigBoolean($decoded['enableAutocomplete'] ?? false),
        ];
    }

    /**
     * Sanitize product field configuration array (ProductFieldConfig).
     *
     * @param mixed[] $decoded
     * @return mixed[]
     */
    private static function sanitizeProductFieldConfigArray(array $decoded): array
    {
        $columnsCount = isset($decoded['columnsCount']) ? (int) $decoded['columnsCount'] : 3;
        $columnsCount = max(2, min(6, $columnsCount));

        return [
            'configVersion' => 1,
            'productType' => self::sanitizeProductFieldType($decoded['productType'] ?? null),
            'paymentAmount' => sanitize_text_field((string) ($decoded['paymentAmount'] ?? '')),
            'amountLabel' => sanitize_text_field((string) ($decoded['amountLabel'] ?? '')),
            'showPriceAfterItemLabel' => self::sanitizeJsonConfigBoolean(
                $decoded['showPriceAfterItemLabel'] ?? false
            ),
            'prices' => self::sanitizeProductPricesMap($decoded['prices'] ?? null),
            'choiceLayout' => self::sanitizeProductChoiceLayout($decoded['choiceLayout'] ?? null),
            'columnsCount' => $columnsCount,
        ];
    }

    /**
     * Whitelist productType (ProductFieldConfig.productType).
     *
     * @param mixed $raw
     * @return string
     */
    private static function sanitizeProductFieldType($raw): string
    {
        $allowed = ['single', 'checkbox', 'radio', 'select', 'user_defined'];
        $type = is_string($raw) ? sanitize_key($raw) : '';
        return in_array($type, $allowed, true) ? $type : 'radio';
    }

    /**
     * Whitelist choiceLayout (ProductFieldConfig.choiceLayout).
     *
     * @param mixed $raw
     * @return string
     */
    private static function sanitizeProductChoiceLayout($raw): string
    {
        $allowed = ['inline', 'columns', 'button'];
        $layout = is_string($raw) ? sanitize_key($raw) : '';
        return in_array($layout, $allowed, true) ? $layout : 'inline';
    }

    /**
     * Sanitize map of option value -> price string (ProductFieldConfig.prices).
     *
     * @param mixed $prices
     * @return array<string, string>
     */
    private static function sanitizeProductPricesMap($prices): array
    {
        if (!is_array($prices)) {
            return [];
        }

        $sanitized = [];
        foreach ($prices as $key => $value) {
            if (!is_scalar($key) || !is_scalar($value)) {
                continue;
            }
            $optionKey = sanitize_text_field((string) $key);
            if ($optionKey === '') {
                continue;
            }
            $sanitized[$optionKey] = sanitize_text_field((string) $value);
        }

        return $sanitized;
    }

    private static function sanitizePhoneFormat(string $format): string
    {
        $allowed = ['international','national','e164'];
        $formatLower = strtolower($format);
        return in_array($formatLower, $allowed, true) ? $formatLower : '';
    }

    /**
     * @param mixed $value
     */
    private static function sanitizeSaveUploadsTo($value): string
    {
        $v = sanitize_text_field((string) ($value ?? 'ivyforms'));
        return in_array($v, ['ivyforms', 'media_library'], true) ? $v : 'ivyforms';
    }

    /**
     * @param mixed $raw
     *
     * @return array<int, string>
     */
    private static function sanitizeAllowedFileExtensionsInput($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        $seen = [];
        foreach ($raw as $item) {
            if (!is_string($item)) {
                continue;
            }
            $ext = strtolower(ltrim(trim($item), '.'));
            if ($ext === '' || strlen($ext) > 20 || !preg_match('/^[a-z0-9]+$/', $ext)) {
                continue;
            }
            if (isset($seen[$ext])) {
                continue;
            }
            $seen[$ext] = true;
            $out[] = $ext;
            if (count($out) >= 50) {
                break;
            }
        }
        return $out;
    }

    /**
     * Sanitize entry data
     *
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public static function sanitizeEntryData(array $data): array
    {
        return [
            'id'            => (int)($data['id'] ?? 0),
            'formId'        => (int)($data['formId'] ?? 0),
            'userId'        => (int)($data['userId'] ?? 0),
            'status'        => sanitize_text_field($data['status'] ?? ''),
            'ipAddress'     => sanitize_text_field($data['ipAddress'] ?? ''),
            'userAgent'     => sanitize_text_field($data['userAgent'] ?? ''),
            'sourceURL'     => sanitize_text_field($data['sourceURL'] ?? ''),
            'dateCreated'   => sanitize_text_field($data['dateCreated'] ?? ''),
            'dateEdited'    => sanitize_text_field($data['dateEdited'] ?? ''),
            'starred'       => (bool)($data['starred'] ?? false),
        ];
    }

    /**
     * Sanitize ID
     *
     * @param int $id
     *
     * @return int
     *
     * @throws InvalidArgumentException
     */
    public static function sanitizeId(int $id): int
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_id']
            );
        }

        return $id;
    }

    /**
     * Sanitize multiple IDs
     *
     * @param int[] $ids
     *
     * @return int[]
     *
     */
    public static function sanitizeIds(array $ids): array
    {
        return array_map(function ($id) {
            return (int)$id;
        }, $ids);
    }

    /**
     * Sanitize search parameters
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function sanitizeSearchParams(array $params): array
    {
        $filters = [
            'starred'   => array_key_exists('starred', $params['filters'] ?? [])
                ? (int)filter_var($params['filters']['starred'], FILTER_VALIDATE_BOOLEAN)
                : null,
            'published' => array_key_exists('published', $params['filters'] ?? [])
                ? (int)filter_var($params['filters']['published'], FILTER_VALIDATE_BOOLEAN)
                : null,
            'status' => sanitize_text_field($params['filters']['read'] ?? ''),
        ];
        // Add formId to filters if present and valid
        if (
            isset($params['filters']['formId']) &&
            is_numeric($params['filters']['formId']) &&
            (int)$params['filters']['formId'] > 0
        ) {
            $filters['formId'] = self::sanitizeId((int)$params['filters']['formId']);
        }
        return [
            'page'      => max((int)($params['page'] ?? 1), 1),
            'perPage'   => max((int)($params['perPage'] ?? 10), 1),
            'search'    => sanitize_text_field($params['search'] ?? ''),
            'orderBy'   => sanitize_text_field($params['orderBy'] ?? 'id'),
            'order'     => strtolower(sanitize_text_field($params['order'] ?? 'asc')) === 'desc' ? 'desc' : 'asc',
            'filters'   => $filters,
            'dateRange' => [
                sanitize_text_field($params['dateRange'][0] ?? ''),
                sanitize_text_field($params['dateRange'][1] ?? ''),
            ],
            'shouldGetCount' => (bool)($params['shouldGetCount'] ?? false),
            'searchFieldValue' => filter_var($params['searchFieldValue'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    /**
     * Sanitize form submission data based on field types and IDs
     *
     * @param mixed[] $data Form submission data
     * @param mixed[] $formFields Form fields configuration
     *
     * @return mixed[] Sanitized submission data
     */
    public static function sanitizeFormSubmissionData(array $data, array $formFields): array
    {
        $sanitizedData = [];
        list($fieldIdMap, $typeIndexMap) = self::buildFieldMaps($formFields);

        foreach ($data['values'] as $key => $value) {
            if (self::isInternalField($key)) {
                $sanitizedData[$key] = $value;
                continue;
            }
            if (isset($fieldIdMap[$key])) {
                $sanitizedData[$key] = self::sanitizeByFieldType($fieldIdMap[$key], $value);
                continue;
            }
            if (isset($typeIndexMap[$key])) {
                $fieldId = $typeIndexMap[$key]['id'];
                $fieldType = $typeIndexMap[$key]['type'];
                $sanitizedData[$fieldId] = self::sanitizeByFieldType($fieldType, $value);
            }
        }

        // Add form ID to sanitized data if present
        if (isset($data['formId'])) {
            $sanitizedData['formId'] = (int)$data['formId'];
        }

        // Add post ID and referer if present (for placeholders)
        if (isset($data['postId'])) {
            $sanitizedData['postId'] = (int)($data['postId']);
        }
        if (isset($data['referer'])) {
            $sanitizedData['referer'] = sanitize_text_field($data['referer']);
        }

        return $sanitizedData;
    }

    /**
     * Build field ID and type-index maps from form fields
     *
     * @param mixed[] $formFields Form fields configuration
     * @return mixed[]
     */
    private static function buildFieldMaps(array $formFields): array
    {
        $fieldIdMap = [];
        $typeIndexMap = [];
        foreach ($formFields as $field) {
            $fieldId = $field->getId() ?? null;
            $fieldType = $field->getType() ?? null;
            $fieldIndex = $field->getIndex() ?? null;
            if ($fieldId !== null) {
                $fieldIdMap[$fieldId] = $fieldType;
                if ($fieldType !== null && $fieldIndex !== null) {
                    $typeIndexMap[$fieldType . '_' . $fieldIndex] = [
                        'id' => $fieldId,
                        'type' => $fieldType
                    ];
                }
            }
        }
        return [$fieldIdMap, $typeIndexMap];
    }

    /**
     * Check if a key is an internal field
     *
     * @param string $key
     * @return bool
     */
    private static function isInternalField(string $key): bool
    {
        return in_array($key, ['nonce', 'formId']);
    }

    /**
     * Sanitize a value based on its field type
     *
     * @param string $fieldType The type of field
     * @param mixed $value The value to sanitize
     *
     * @return mixed Sanitized value
     */
    private static function sanitizeByFieldType(string $fieldType, $value)
    {
        // File-upload values can arrive as arrays (JSON submit with zero/one/many files),
        // JSON-encoded strings, or single URL strings. Normalize them before the generic
        // array handling so the stored value is always a consistent scalar that mirrors the
        // multipart upload path (avoids "Array to string conversion" further down the flow).
        if ($fieldType === 'file-upload') {
            return self::sanitizeFileUploadEntryValue($value);
        }

        // Recursively sanitize arrays/objects (parent-child fields)
        if (is_array($value)) {
            return array_map(function ($v) use ($fieldType) {
                return self::sanitizeByFieldType($fieldType, $v);
            }, $value);
        }
        if (is_object($value)) {
            $sanitized = array_map(function ($v) use ($fieldType) {
                return self::sanitizeByFieldType($fieldType, $v);
            }, get_object_vars($value));
            return (object)$sanitized;
        }
        switch ($fieldType) {
            case 'email':
                return sanitize_email($value);

            case 'number':
            case 'quantity':
            case 'total':
                return is_numeric($value) ? (float)$value : null;

            case 'slider':
                return is_numeric($value) ? (float)$value : null;

            case 'phone':
                // Remove everything except digits, +, and spaces
                return preg_replace('/[^\d\s+]/', '', $value);

            case 'paragraph':
                return sanitize_textarea_field($value);

            case 'rich_text':
                $filtered = self::filterSubmissionFieldValue($fieldType, $value);
                if ($filtered !== null) {
                    return $filtered;
                }

                return HtmlSanitizer::sanitizeEditorContent((string) $value);

            case 'website':
                return sanitize_url($value);

            case 'time':
                $sanitizedTime = preg_replace('/[^0-9: ]/i', '', (string)$value);
                return sanitize_text_field($sanitizedTime);

            case 'date':
                return sanitize_text_field($value);

            case 'product':
                return self::sanitizeProductEntryValue($value);

            case 'gdpr':
                // GDPR checkbox: sanitize as boolean-like value
                return (bool)$value ? 'Yes' : 'No';

            case 'text':
                return self::sanitizeText($value);

            default:
                $filtered = self::filterSubmissionFieldValue($fieldType, $value);
                if ($filtered !== null) {
                    return $filtered;
                }

                return self::sanitizeText($value);
        }
    }

    /**
     * Normalize a file-upload submission value to a consistent scalar form.
     *
     * Accepts arrays (JSON submit), JSON-encoded strings, or single URL strings and always
     * returns either an empty string, a single sanitized URL, or a JSON-encoded list of
     * sanitized URLs (mirroring the multipart upload storage format).
     *
     * @param mixed $value
     * @return string
     */
    private static function sanitizeFileUploadEntryValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_array($value)) {
            return self::encodeFileUploadUrls($value);
        }

        $asString = (string) $value;
        if ($asString !== '' && $asString[0] === '[') {
            $decoded = json_decode($asString, true);
            if (!is_array($decoded)) {
                return sanitize_text_field($asString);
            }

            return self::encodeFileUploadUrls($decoded);
        }

        return esc_url_raw($asString);
    }

    /**
     * Sanitize a list of file-upload URLs into a single stored value.
     *
     * @param array<mixed> $rawUrls
     * @return string
     */
    private static function encodeFileUploadUrls(array $rawUrls): string
    {
        $urls = [];
        foreach ($rawUrls as $url) {
            if (is_string($url) && $url !== '') {
                $sanitizedUrl = esc_url_raw($url);
                if ($sanitizedUrl !== '') {
                    $urls[] = $sanitizedUrl;
                }
            }
        }

        if ($urls === []) {
            return '';
        }

        return count($urls) === 1 ? $urls[0] : (string) wp_json_encode($urls);
    }

    /**
     * Sanitize stored product field value (JSON snapshot or legacy plain text).
     *
     * @param mixed $value
     * @return string
     */
    private static function sanitizeProductEntryValue($value): string
    {
        if (!is_string($value) || $value === '') {
            return '';
        }
        $trimmed = trim($value);
        if ($trimmed === '' || $trimmed[0] !== '{') {
            return self::sanitizeText($value) ?? '';
        }
        $decoded = json_decode($value, true);
        if (
            !is_array($decoded) || (int) ($decoded['v'] ?? 0) !== 1 ||
            !isset($decoded['lines']) || !is_array($decoded['lines'])
        ) {
            return self::sanitizeText($value) ?? '';
        }
        $allowedTypes = ['single', 'checkbox', 'radio', 'select', 'user_defined'];
        $productType = sanitize_key((string) ($decoded['productType'] ?? ''));
        if (!in_array($productType, $allowedTypes, true)) {
            $productType = 'radio';
        }
        $linesOut = [];
        foreach ($decoded['lines'] as $line) {
            if (!is_array($line)) {
                continue;
            }
            $label = sanitize_text_field((string) ($line['label'] ?? ''));
            $price = isset($line['price']) ? sanitize_text_field((string) $line['price']) : '';
            $priceAmount = isset($line['priceAmount'])
                ? sanitize_text_field((string) $line['priceAmount'])
                : '';
            if ($label === '' && $priceAmount === '' && $price === '') {
                continue;
            }
            $entry = ['label' => $label];
            if ($priceAmount !== '') {
                $entry['priceAmount'] = $priceAmount;
            }
            if ($price !== '') {
                $entry['price'] = $price;
            }
            $linesOut[] = $entry;
        }
        $raw = sanitize_text_field((string) ($decoded['raw'] ?? ''));
        $summary = sanitize_text_field((string) ($decoded['summary'] ?? ''));

        return wp_json_encode(
            [
                'v' => 1,
                'productType' => $productType,
                'raw' => $raw,
                'lines' => $linesOut,
                'summary' => $summary,
            ],
            JSON_UNESCAPED_UNICODE
        ) ?: '';
    }

    /**
     * Verify nonce with custom error handling
     *
     * @param string|null $nonce
     *
     * @throws ForbiddenException
     */
    public static function verifyNonce(?string $nonce): void
    {
        $nonce = sanitize_text_field($nonce);

        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            throw new ForbiddenException(
                BackendStrings::getExceptionStrings()['invalid_nonce']
            );
        }
    }
    /**
     * Verify submission nonce with custom error handling
     *
     * @param string|null $nonce
     * @param int $formId
     *
     * @throws ForbiddenException
     */
    public static function verifySubmissionNonce(?string $nonce, int $formId): void
    {
        $nonce = sanitize_text_field($nonce);

        // Verify the form-specific nonce
        if (!wp_verify_nonce($nonce, 'ivyformsFrontSubmissionNonce_' . $formId)) {
            throw new ForbiddenException(
                BackendStrings::getExceptionStrings()['invalid_nonce']
            );
        }
    }

    /**
     * Sanitize text
     *
     * @param string|null $text
     *
     * @return string
     */
    public static function sanitizeText(?string $text): ?string
    {
        if (is_null($text)) {
            return '';
        }

        return wp_kses_post(sanitize_text_field($text));
    }

    /**
     * Sanitize settings fields
     *
     * @param mixed $value
     *
     * @return mixed Sanitized value
     */
    public static function sanitizeSettingsFields($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'sanitizeSettingsFields'], $value);
        }

        if (is_object($value)) {
            return (object)array_map([self::class, 'sanitizeSettingsFields'], (array)$value);
        }

        if (is_string($value)) {
            return self::sanitizeText($value);
        }

        if (is_int($value)) {
            return intval($value);
        }

        if (is_bool($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return '';
    }

    /**
     * Whether a value is a notification email placeholder token.
     *
     * Supports {{field_key}}, {{wp.admin_email}}, and single-brace {admin_email}.
     *
     * @param string $value
     * @return bool
     */
    public static function isEmailPlaceholderToken(string $value): bool
    {
        $value = trim($value);

        if ($value === '') {
            return false;
        }

        if (preg_match('/^{{[^}]+}}$/', $value)) {
            return true;
        }

        return (bool) preg_match('/^{[^{}]+}$/', $value);
    }

    /**
     * Whether a value contains CR/LF characters unsafe for email headers.
     *
     * @param string $value
     * @return bool
     */
    public static function containsEmailHeaderInjection(string $value): bool
    {
        return (bool) preg_match('/[\r\n]/', $value);
    }

    /**
     * Sanitize email or placeholder
     *
     * @param string $value
     *
     * @return string
     */
    public static function sanitizeEmailOrPlaceholder($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        if (self::isEmailPlaceholderToken($value)) {
            return $value;
        }

        return sanitize_email($value);
    }

    /**
     * Sanitize a comma-separated list of emails or placeholders.
     *
     * @param mixed $value
     * @return string
     */
    public static function sanitizeEmailListOrPlaceholders($value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $parts = array_map('trim', explode(',', $value));
        $sanitized = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if (self::isEmailPlaceholderToken($part)) {
                $sanitized[] = $part;
                continue;
            }

            if (!self::isValidEmail($part)) {
                continue;
            }

            $sanitizedValue = sanitize_email($part);
            if ($sanitizedValue === '') {
                continue;
            }

            $sanitized[] = $sanitizedValue;
        }

        return implode(', ', $sanitized);
    }

    /**
     * Sanitize numeric values for min/max fields
     * Convert empty/unselected to null, but allow 0 as a valid value
     *
     * @param mixed $value
     * @return float|null
     */
    private static function sanitizeNumericValue($value): ?float
    {
        // If value is null or empty string, return null (not selected)
        if ($value === null || $value === '') {
            return null;
        }

        // If value is numeric (including 0), convert to float
        if (is_numeric($value)) {
            return (float)$value;
        }

        // If not numeric, return null
        return null;
    }

    /**
     * Validate email address.
     *
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Normalize field value to string (comma-separated if array)
     * @param mixed $value
     * @return string
     */
    public static function normalizeFieldValue($value): string
    {
        if (is_array($value)) {
            return implode(', ', $value);
        }
        return (string)$value;
    }

    /**
     * Sanitize integration settings
     * Recursively handles nested structures and various data types.
     *
     * @param mixed $integrationSettings
     *
     * @return array<string, array<string, mixed>>
     */
    public static function sanitizeIntegrationSettings($integrationSettings): array
    {
        // Handle null or non-array inputs
        if (!is_array($integrationSettings)) {
            return [];
        }

        $cleanedSettings = [];

        foreach ($integrationSettings as $integration => $settings) {
            // Skip if not a valid integration name or settings structure
            if (!is_string($integration) || !is_array($settings)) {
                continue;
            }

            $cleanedSettings[sanitize_text_field($integration)] = self::sanitizeIntegrationSettingsRecursive($settings);
        }

        return $cleanedSettings;
    }

    /**
     * Recursively sanitize integration settings values
     *
     * @param mixed $value
     * @return mixed
     */
    public static function sanitizeIntegrationSettingsRecursive($value)
    {
        // Handle arrays recursively
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $val) {
                $sanitizedKey = is_string($key) ? sanitize_text_field($key) : $key;
                $sanitized[$sanitizedKey] = self::sanitizeIntegrationSettingsRecursive($val);
            }
            return $sanitized;
        }

        // Handle different scalar types
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return $value;
        }
        if (is_string($value)) {
            return sanitize_text_field($value);
        }

        return '';
    }

    /**
     * Sanitize style settings for form styling
     *
     * @param mixed $styleSettings
     * @return array<string, mixed>|null
     */
    public static function sanitizeStyleSettings($styleSettings): ?array
    {
        if (!is_array($styleSettings)) {
            return null;
        }

        // Merge with bundled defaults so partial payloads (or missing stylesEnabled) still persist.
        $merged = array_replace_recursive(TemplateDefaults::getDefaultFormStylesSafe(), $styleSettings);

        $sanitized = [];

        // Sanitize style mode flags
        $sanitized['stylesEnabled'] = (bool)($merged['stylesEnabled'] ?? true);
        $sanitized['selectedTheme'] = sanitize_text_field($merged['selectedTheme'] ?? 'ivy-default');
        $sanitized['styleMode'] = in_array($merged['styleMode'] ?? 'quick', ['quick', 'advanced'], true)
            ? $merged['styleMode']
            : 'quick';

        // Sanitize quick mode settings
        $sanitized['quickMode'] = self::sanitizeQuickStyleSettings($merged['quickMode'] ?? []);

        // Sanitize advanced mode settings
        $sanitized['advancedMode'] = self::sanitizeAdvancedStyleSettings($merged['advancedMode'] ?? []);

        // Sanitize custom CSS class
        $sanitized['customCssClass'] = CssSanitizer::sanitizeCssClass(
            $merged['customCssClass'] ?? ''
        );
        $sanitized['additionalCssClasses'] = CssSanitizer::sanitizeCssClassList(
            $merged['additionalCssClasses'] ?? ''
        );

        $filtered = apply_filters('ivyforms/form/sanitized_style_settings', $sanitized, $styleSettings);

        return is_array($filtered) ? $filtered : $sanitized;
    }

    /**
     * Sanitize quick style settings with basic color and unit validation
     *
     * @param mixed $quickModeSettings
     * @return array<string, mixed>
     */
    private static function sanitizeQuickStyleSettings($quickModeSettings): array
    {
        if (isset($quickModeSettings) && is_array($quickModeSettings)) {
            return [
                'formBackground' => self::sanitizeColor($quickModeSettings['formBackground'] ?? ''),
                'formBorderColor' => self::sanitizeColor($quickModeSettings['formBorderColor'] ?? ''),
                'formBorderWidth' => CssSanitizer::sanitizeCssUnit($quickModeSettings['formBorderWidth'] ?? ''),
                'verticalSpacing' => CssSanitizer::sanitizeCssUnit(
                    $quickModeSettings['verticalSpacing'] ?? ''
                ),
                'horizontalSpacing' => CssSanitizer::sanitizeCssUnit(
                    $quickModeSettings['horizontalSpacing'] ?? ''
                ),
                'formCorner' => self::sanitizeCornerSetting($quickModeSettings['formCorner'] ?? ''),
                'formTitleText' => self::sanitizeColor($quickModeSettings['formTitleText'] ?? ''),
                'fieldBackground' => self::sanitizeColor($quickModeSettings['fieldBackground'] ?? ''),
                'fieldText' => self::sanitizeColor(
                    $quickModeSettings['fieldText'] ?? ''
                ),
                'fieldBorderColor' => self::sanitizeColor(
                    $quickModeSettings['fieldBorderColor'] ?? ''
                ),
                'fieldCorner' => self::sanitizeCornerSetting(
                    $quickModeSettings['fieldCorner'] ?? ''
                ),
                'fieldBorderWidth' => CssSanitizer::sanitizeCssUnit(
                    $quickModeSettings['fieldBorderWidth'] ?? ''
                ),
                'fieldPaddingLeftRight' => CssSanitizer::sanitizeCssUnit(
                    $quickModeSettings['fieldPaddingLeftRight'] ?? ''
                ),
                'fieldPaddingTopBottom' => CssSanitizer::sanitizeCssUnit(
                    $quickModeSettings['fieldPaddingTopBottom'] ?? ''
                ),
                'labelFontColor' => self::sanitizeColor(
                    $quickModeSettings['labelFontColor'] ?? ''
                ),
                'buttonBackground' => self::sanitizeColor(
                    $quickModeSettings['buttonBackground'] ?? ''
                ),
                'buttonBorderColor' => self::sanitizeColor(
                    $quickModeSettings['buttonBorderColor'] ?? ''
                ),
                'buttonBorderWidth' => CssSanitizer::sanitizeCssUnit(
                    $quickModeSettings['buttonBorderWidth'] ?? ''
                ),
                'buttonPaddingLeftRight' => CssSanitizer::sanitizeCssUnit(
                    $quickModeSettings['buttonPaddingLeftRight'] ?? ''
                ),
                'buttonPaddingTopBottom' => CssSanitizer::sanitizeCssUnit(
                    $quickModeSettings['buttonPaddingTopBottom'] ?? ''
                ),
                'buttonWidth' => CssSanitizer::sanitizeCssUnitOrAuto(
                    $quickModeSettings['buttonWidth'] ?? ''
                ),
                'buttonHeight' => CssSanitizer::sanitizeCssUnitOrAuto(
                    $quickModeSettings['buttonHeight'] ?? ''
                ),
                'buttonText' => self::sanitizeColor(
                    $quickModeSettings['buttonText'] ?? ''
                ),
            ];
        }
        return [];
    }

    /**
     * Sanitize advanced style settings with basic color and unit validation
     *
     * @param mixed $advancedModeSettings
     * @return array<string, mixed>
     */
    private static function sanitizeAdvancedStyleSettings($advancedModeSettings): array
    {
        $advanced = [];

        if (isset($advancedModeSettings) && is_array($advancedModeSettings)) {
            if (isset($advancedModeSettings['general'])) {
                $advanced['general'] = [
                    'formBackground' => self::sanitizeColor(
                        $advancedModeSettings['general']['formBackground'] ?? ''
                    ),
                    'formBorderColor' => self::sanitizeColor(
                        $advancedModeSettings['general']['formBorderColor'] ?? ''
                    ),
                    'formBorderWidth' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['general']['formBorderWidth'] ?? ''
                    ),
                    'formBorderStyle' => self::sanitizeBorderStyle(
                        $advancedModeSettings['general']['formBorderStyle'] ?? 'solid'
                    ),
                    'formPaddingLeftRight' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['general']['formPaddingLeftRight'] ?? ''
                    ),
                    'formPaddingTopBottom' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['general']['formPaddingTopBottom'] ?? ''
                    ),
                    'verticalSpacing' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['general']['verticalSpacing'] ?? ''
                    ),
                    'horizontalSpacing' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['general']['horizontalSpacing'] ?? ''
                    ),
                    'alignment' => self::sanitizeAlignment(
                        $advancedModeSettings['general']['alignment'] ?? 'left'
                    ),
                    'formCorner' => self::sanitizeCornerSetting(
                        $advancedModeSettings['general']['formCorner'] ?? ''
                    ),
                ];
            }

            if (isset($advancedModeSettings['formTitle'])) {
                $advanced['formTitle'] = [
                    'fontColor' => self::sanitizeColor(
                        $advancedModeSettings['formTitle']['fontColor'] ?? ''
                    ),
                    'fontWeight' => self::sanitizeFontWeight(
                        $advancedModeSettings['formTitle']['fontWeight'] ?? ''
                    ),
                    'fontStyle' => self::sanitizeFontStyle(
                        $advancedModeSettings['formTitle']['fontStyle'] ?? 'normal'
                    ),
                    'fontSize' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['formTitle']['fontSize'] ?? ''
                    ),
                    'paddingLeftRight' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['formTitle']['paddingLeftRight'] ?? ''
                    ),
                    'paddingTopBottom' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['formTitle']['paddingTopBottom'] ?? ''
                    ),
                ];
            }

            if (isset($advancedModeSettings['formDescription'])) {
                $advanced['formDescription'] = [
                    'fontColor' => self::sanitizeColor(
                        $advancedModeSettings['formDescription']['fontColor'] ?? ''
                    ),
                    'fontWeight' => self::sanitizeFontWeight(
                        $advancedModeSettings['formDescription']['fontWeight'] ?? ''
                    ),
                    'fontStyle' => self::sanitizeFontStyle(
                        $advancedModeSettings['formDescription']['fontStyle'] ?? 'normal'
                    ),
                    'fontSize' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['formDescription']['fontSize'] ?? ''
                    ),
                    'paddingLeftRight' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['formDescription']['paddingLeftRight'] ?? ''
                    ),
                    'paddingTopBottom' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['formDescription']['paddingTopBottom'] ?? ''
                    ),
                ];
            }

            if (isset($advancedModeSettings['fieldLabels'])) {
                $labelFontColor = $advancedModeSettings['fieldLabels']['label']['fontColor']
                    ?? $advancedModeSettings['fieldLabels']['fontColor']
                    ?? '';
                $requiredIndicatorColor =
                    $advancedModeSettings['fieldLabels']['requiredIndicator']['fontColor']
                    ?? $advancedModeSettings['fieldLabels']['requiredIndicatorFontColor']
                    ?? '';
                $advanced['fieldLabels'] = [
                    'label' => [
                        'fontColor' => self::sanitizeColor($labelFontColor),
                        'fontWeight' => self::sanitizeFontWeight(
                            $advancedModeSettings['fieldLabels']['label']['fontWeight'] ?? ''
                        ),
                        'fontStyle' => self::sanitizeFontStyle(
                            $advancedModeSettings['fieldLabels']['label']['fontStyle'] ?? 'normal'
                        ),
                        'fontSize' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['fieldLabels']['label']['fontSize'] ?? ''
                        ),
                        'paddingLeftRight' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['fieldLabels']['label']['paddingLeftRight'] ?? ''
                        ),
                        'paddingTopBottom' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['fieldLabels']['label']['paddingTopBottom'] ?? ''
                        ),
                        'alignment' => self::sanitizeAlignment(
                            $advancedModeSettings['fieldLabels']['label']['alignment'] ?? 'left'
                        ),
                    ],
                    'requiredIndicator' => [
                        'fontColor' => self::sanitizeColor($requiredIndicatorColor),
                    ],
                ];
            }

            if (isset($advancedModeSettings['fieldPlaceholder'])) {
                $advanced['fieldPlaceholder'] = [
                    'fontColor' => self::sanitizeColor(
                        $advancedModeSettings['fieldPlaceholder']['fontColor'] ?? ''
                    ),
                    'fontWeight' => self::sanitizeFontWeight(
                        $advancedModeSettings['fieldPlaceholder']['fontWeight'] ?? ''
                    ),
                    'fontStyle' => self::sanitizeFontStyle(
                        $advancedModeSettings['fieldPlaceholder']['fontStyle'] ?? 'normal'
                    ),
                    'fontSize' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['fieldPlaceholder']['fontSize'] ?? ''
                    ),
                    'paddingLeftRight' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['fieldPlaceholder']['paddingLeftRight'] ?? ''
                    ),
                    'alignment' => self::sanitizeAlignment(
                        $advancedModeSettings['fieldPlaceholder']['alignment'] ?? 'left'
                    ),
                ];
            }

            if (isset($advancedModeSettings['fieldStates'])) {
                $advanced['fieldStates'] = [];
                if (isset($advancedModeSettings['fieldStates']['default'])) {
                    $advanced['fieldStates']['default'] = [
                        'background' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['default']['background']
                            ?? $advancedModeSettings['fieldStates']['default']['backgroundColor']
                            ?? ''
                        ),
                        'textColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['default']['textColor'] ?? ''
                        ),
                        'fontWeight' => self::sanitizeFontWeight(
                            $advancedModeSettings['fieldStates']['default']['fontWeight'] ?? ''
                        ),
                        'fontStyle' => self::sanitizeFontStyle(
                            $advancedModeSettings['fieldStates']['default']['fontStyle'] ?? 'normal'
                        ),
                        'fontSize' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['fieldStates']['default']['fontSize'] ?? ''
                        ),
                        'descriptionColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['default']['descriptionColor'] ?? ''
                        ),
                        'borderColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['default']['borderColor'] ?? ''
                        ),
                        'borderStyle' => self::sanitizeBorderStyle(
                            $advancedModeSettings['fieldStates']['default']['borderStyle'] ?? 'solid'
                        ),
                        'corner' => self::sanitizeCornerSetting(
                            $advancedModeSettings['fieldStates']['default']['corner'] ?? ''
                        ),
                        'borderWidth' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['fieldStates']['default']['borderWidth'] ?? ''
                        ),
                        'paddingLeftRight' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['fieldStates']['default']['paddingLeftRight'] ?? ''
                        ),
                        'paddingTopBottom' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['fieldStates']['default']['paddingTopBottom'] ?? ''
                        ),
                    ];
                }
                if (isset($advancedModeSettings['fieldStates']['active'])) {
                    $advanced['fieldStates']['active'] = [
                        'background' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['active']['background']
                            ?? $advancedModeSettings['fieldStates']['active']['backgroundColor']
                            ?? ''
                        ),
                        'textColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['active']['textColor'] ?? ''
                        ),
                        'descriptionColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['active']['descriptionColor'] ?? ''
                        ),
                        'borderColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['active']['borderColor'] ?? ''
                        ),
                        'borderStyle' => self::sanitizeBorderStyle(
                            $advancedModeSettings['fieldStates']['active']['borderStyle'] ?? 'solid'
                        ),
                    ];
                }
                if (isset($advancedModeSettings['fieldStates']['readOnly'])) {
                    $advanced['fieldStates']['readOnly'] = [
                        'background' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['readOnly']['background']
                            ?? $advancedModeSettings['fieldStates']['readOnly']['backgroundColor']
                            ?? ''
                        ),
                        'textColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['readOnly']['textColor'] ?? ''
                        ),
                        'descriptionColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['readOnly']['descriptionColor'] ?? ''
                        ),
                        'borderColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['readOnly']['borderColor'] ?? ''
                        ),
                        'borderStyle' => self::sanitizeBorderStyle(
                            $advancedModeSettings['fieldStates']['readOnly']['borderStyle'] ?? 'solid'
                        ),
                    ];
                }
                if (isset($advancedModeSettings['fieldStates']['error'])) {
                    $advanced['fieldStates']['error'] = [
                        'background' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['error']['background']
                            ?? $advancedModeSettings['fieldStates']['error']['backgroundColor']
                            ?? ''
                        ),
                        'textColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['error']['textColor'] ?? ''
                        ),
                        'descriptionColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['error']['descriptionColor'] ?? ''
                        ),
                        'borderColor' => self::sanitizeColor(
                            $advancedModeSettings['fieldStates']['error']['borderColor'] ?? ''
                        ),
                        'borderStyle' => self::sanitizeBorderStyle(
                            $advancedModeSettings['fieldStates']['error']['borderStyle'] ?? 'solid'
                        ),
                    ];
                }
            }

            if (isset($advancedModeSettings['checkboxRadio'])) {
                $advanced['checkboxRadio'] = [
                    'fontColor' => self::sanitizeColor(
                        $advancedModeSettings['checkboxRadio']['fontColor'] ?? ''
                    ),
                    'fontWeight' => self::sanitizeFontWeight(
                        $advancedModeSettings['checkboxRadio']['fontWeight'] ?? ''
                    ),
                    'fontStyle' => self::sanitizeFontStyle(
                        $advancedModeSettings['checkboxRadio']['fontStyle'] ?? 'normal'
                    ),
                    'fontSize' => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['checkboxRadio']['fontSize'] ?? ''
                    ),
                    'selectionColor' => self::sanitizeColor(
                        $advancedModeSettings['checkboxRadio']['selectionColor'] ?? ''
                    ),
                    'borderColor' => self::sanitizeColor(
                        $advancedModeSettings['checkboxRadio']['borderColor'] ?? ''
                    ),
                ];
            }

            if (isset($advancedModeSettings['buttonStates'])) {
                $advanced['buttonStates'] = [
                    'normal' => [
                        'background' => self::sanitizeColor(
                            $advancedModeSettings['buttonStates']['normal']['background']
                            ?? $advancedModeSettings['buttonStates']['normal']['backgroundColor']
                            ?? ''
                        ),
                        'borderColor' => self::sanitizeColor(
                            $advancedModeSettings['buttonStates']['normal']['borderColor'] ?? ''
                        ),
                        'textColor' => self::sanitizeColor(
                            $advancedModeSettings['buttonStates']['normal']['textColor']
                            ?? $advancedModeSettings['buttonStates']['normal']['fontColor']
                            ?? ''
                        ),
                        'fontWeight' => self::sanitizeFontWeight(
                            $advancedModeSettings['buttonStates']['normal']['fontWeight'] ?? ''
                        ),
                        'fontStyle' => self::sanitizeFontStyle(
                            $advancedModeSettings['buttonStates']['normal']['fontStyle'] ?? 'normal'
                        ),
                        'fontSize' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['buttonStates']['normal']['fontSize'] ?? ''
                        ),
                        'borderWidth' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['buttonStates']['normal']['borderWidth'] ?? ''
                        ),
                        'corner' => self::sanitizeCornerSetting(
                            $advancedModeSettings['buttonStates']['normal']['corner'] ?? ''
                        ),
                        'paddingLeftRight' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['buttonStates']['normal']['paddingLeftRight'] ?? ''
                        ),
                        'paddingTopBottom' => CssSanitizer::sanitizeCssUnit(
                            $advancedModeSettings['buttonStates']['normal']['paddingTopBottom'] ?? ''
                        ),
                        'width' => CssSanitizer::sanitizeCssUnitOrAuto(
                            $advancedModeSettings['buttonStates']['normal']['width'] ?? ''
                        ),
                        'height' => CssSanitizer::sanitizeCssUnitOrAuto(
                            $advancedModeSettings['buttonStates']['normal']['height'] ?? ''
                        ),
                    ],
                    'hover' => [
                        'background' => self::sanitizeColor(
                            $advancedModeSettings['buttonStates']['hover']['background']
                            ?? $advancedModeSettings['buttonStates']['hover']['backgroundColor']
                            ?? ''
                        ),
                        'borderColor' => self::sanitizeColor(
                            $advancedModeSettings['buttonStates']['hover']['borderColor'] ?? ''
                        ),
                        'textColor' => self::sanitizeColor(
                            $advancedModeSettings['buttonStates']['hover']['textColor']
                            ?? $advancedModeSettings['buttonStates']['hover']['fontColor']
                            ?? ''
                        ),
                        'corner' => self::sanitizeCornerSetting(
                            $advancedModeSettings['buttonStates']['hover']['corner']
                            ?? $advancedModeSettings['buttonStates']['normal']['corner']
                            ?? ''
                        ),
                    ],
                ];
            }

            if (
                isset($advancedModeSettings['confirmationMessage'])
                || isset($advancedModeSettings['confirmation'])
            ) {
                $confirmationMessage = $advancedModeSettings['confirmationMessage']
                    ?? $advancedModeSettings['confirmation'];
                $advanced['confirmationMessage'] = [
                    'borderColor' => self::sanitizeColor(
                        $confirmationMessage['borderColor'] ?? ''
                    ),
                    'borderStyle' => self::sanitizeBorderStyle(
                        $confirmationMessage['borderStyle'] ?? 'solid'
                    ),
                    'backgroundColor' => self::sanitizeColor(
                        $confirmationMessage['backgroundColor'] ?? ''
                    ),
                    'textColor' => self::sanitizeColor(
                        $confirmationMessage['textColor'] ?? ''
                    ),
                    'borderWidth' => CssSanitizer::sanitizeCssUnit(
                        $confirmationMessage['borderWidth'] ?? ''
                    ),
                    'corner' => self::sanitizeCornerSetting(
                        $confirmationMessage['corner'] ?? ''
                    ),
                ];
            }

            if (is_array($advancedModeSettings['rating'] ?? null)) {
                $advanced['rating'] = [
                    'iconSize'  => CssSanitizer::sanitizeCssUnit(
                        $advancedModeSettings['rating']['iconSize'] ?? ''
                    ),
                    'iconColor' => self::sanitizeColor(
                        $advancedModeSettings['rating']['iconColor'] ?? ''
                    ),
                ];
            }

            if (is_array($advancedModeSettings['slider'] ?? null)) {
                $advanced['slider'] = [
                    'handleColor' => self::sanitizeStrictColor(
                        $advancedModeSettings['slider']['handleColor'] ?? ''
                    ),
                    'highlightColor' => self::sanitizeStrictColor(
                        $advancedModeSettings['slider']['highlightColor'] ?? ''
                    ),
                    'borderColor' => self::sanitizeStrictColor(
                        $advancedModeSettings['slider']['borderColor'] ?? ''
                    ),
                    'trackColor' => self::sanitizeStrictColor(
                        $advancedModeSettings['slider']['trackColor']
                            ?? $advancedModeSettings['slider']['rangeColor']
                            ?? ''
                    ),
                    'remainingTrackColor' => self::sanitizeStrictColor(
                        $advancedModeSettings['slider']['remainingTrackColor'] ?? ''
                    ),
                ];
            }

            if (is_array($advancedModeSettings['dateTimePicker'] ?? null)) {
                $advanced['dateTimePicker'] = [
                    'calendarBackground' => self::sanitizeColor(
                        $advancedModeSettings['dateTimePicker']['calendarBackground'] ?? ''
                    ),
                    'numberTextColor'    => self::sanitizeColor(
                        $advancedModeSettings['dateTimePicker']['numberTextColor'] ?? ''
                    ),
                    'selectionColor'     => self::sanitizeColor(
                        $advancedModeSettings['dateTimePicker']['selectionColor'] ?? ''
                    ),
                ];
            }

            if (is_array($advancedModeSettings['select'] ?? null)) {
                $advanced['select'] = [
                    'tagColor'         => self::sanitizeColor(
                        $advancedModeSettings['select']['tagColor'] ?? ''
                    ),
                    'tagTextColor'     => self::sanitizeColor(
                        $advancedModeSettings['select']['tagTextColor'] ?? ''
                    ),
                    'optionHoverColor' => self::sanitizeColor(
                        $advancedModeSettings['select']['optionHoverColor'] ?? ''
                    ),
                    'selectionTextColor' => self::sanitizeColor(
                        $advancedModeSettings['select']['selectionTextColor'] ?? ''
                    ),
                    'selectionBackgroundColor' => self::sanitizeColor(
                        $advancedModeSettings['select']['selectionBackgroundColor'] ?? ''
                    ),
                ];
            }
        }

        /**
         * Allow Pro (and other extensions) to sanitize plan-specific advanced style sections.
         *
         * @param array<string, mixed> $advanced
         * @param array<string, mixed> $advancedModeSettings
         */
        return apply_filters('ivyforms/style/sanitize_advanced_mode', $advanced, $advancedModeSettings);
    }

    /**
     * Sanitize linked or per-corner border radius settings.
     *
     * @param mixed $cornerSetting
     * @return array<string, mixed>|string
     */
    private static function sanitizeCornerSetting($cornerSetting)
    {
        if (is_array($cornerSetting)) {
            return [
                'isLinked' => isset($cornerSetting['isLinked']) ? (bool)$cornerSetting['isLinked'] : true,
                'value' => CssSanitizer::sanitizeCssUnit($cornerSetting['value'] ?? ''),
                'topLeft' => CssSanitizer::sanitizeCssUnit($cornerSetting['topLeft'] ?? ''),
                'topRight' => CssSanitizer::sanitizeCssUnit($cornerSetting['topRight'] ?? ''),
                'bottomRight' => CssSanitizer::sanitizeCssUnit($cornerSetting['bottomRight'] ?? ''),
                'bottomLeft' => CssSanitizer::sanitizeCssUnit($cornerSetting['bottomLeft'] ?? ''),
            ];
        }

        if (is_string($cornerSetting)) {
            return CssSanitizer::sanitizeCssUnit($cornerSetting);
        }

        return '';
    }

    /**
     * Sanitize CSS border style value.
     *
     * @param string $value
     * @return string
     */
    private static function sanitizeBorderStyle(string $value): string
    {
        $normalizedValue = strtolower(trim($value));
        return in_array($normalizedValue, StyleConstants::ALLOWED_BORDER_STYLES, true) ? $normalizedValue : 'solid';
    }

    /**
     * Sanitize font weight value.
     *
     * @param string $value
     * @return string
     */
    private static function sanitizeFontWeight(string $value): string
    {
        $normalizedValue = trim($value);
        return in_array($normalizedValue, StyleConstants::ALLOWED_FONT_WEIGHTS, true) ? $normalizedValue : '';
    }

    private static function sanitizeFontStyle(string $value): string
    {
        $normalizedValue = strtolower(trim($value));
        return in_array($normalizedValue, StyleConstants::ALLOWED_FONT_STYLES, true) ? $normalizedValue : 'normal';
    }

    private static function sanitizeAlignment(string $value): string
    {
        $trimmed = trim($value);
        return in_array($trimmed, StyleConstants::ALLOWED_ALIGNMENTS, true) ? $trimmed : 'left';
    }

    /**
     * Sanitize color value (hex, rgb, rgba)
     *
     * @param string $color
     * @return string
     */
    private static function sanitizeColor(string $color): string
    {
        // Allow hex colors, rgb(), rgba(), color names
        $color = trim($color);

        // Check if it's a hex color
        if (preg_match('/^#[a-f0-9]{6}$/i', $color) || preg_match('/^#[a-f0-9]{3}$/i', $color)) {
            return $color;
        }

        // Check if it's rgb() or rgba()
        if (preg_match('/^rgba?\([0-9,.\s]+\)$/i', $color)) {
            return sanitize_text_field($color);
        }

        // Default safe color
        return sanitize_text_field($color);
    }

    /**
     * Sanitize color value for advanced style settings — rejects values unsafe for CSS output.
     *
     * @param string $color
     * @return string
     */
    private static function sanitizeStrictColor(string $color): string
    {
        return CssSanitizer::sanitizeStrictColor($color);
    }

    /**
     * Sanitize bulk update request data
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function sanitizeBulkUpdateData(array $data): array
    {
        $sanitized = [];

        // Sanitize column name (alphanumeric and underscore only)
        if (isset($data['column'])) {
            $sanitized['column'] = preg_replace('/[^a-zA-Z0-9_]/', '', $data['column']);
        }

        // Sanitize value (depends on column)
        if (isset($data['value'])) {
            $column = $sanitized['column'] ?? '';
            if ($column !== '') {
                if (in_array($column, ['starred', 'status'], true)) {
                    $sanitized['value'] = self::sanitizeEntryColumnValue($column, $data['value']);
                } else {
                    $sanitized['value'] = self::sanitizeFormColumnValue($column, $data['value']);
                }
            } else {
                $sanitized['value'] = sanitize_text_field($data['value']);
            }
        }

        // Sanitize IDs array
        if (isset($data['ids']) && is_array($data['ids'])) {
            $sanitized['ids'] = array_map('intval', $data['ids']);
            $sanitized['ids'] = array_filter($sanitized['ids'], fn($id) => $id > 0);
        }

        return $sanitized;
    }

    /**
     * Sanitize starred value (convert to boolean integer)
     *
     * @param mixed $value
     * @return int
     */
    public static function sanitizeStarredValue($value): int
    {
        return $value ? 1 : 0;
    }

    /**
     * Sanitize entry status value
     *
     * @param string $status
     * @return string
     */
    public static function sanitizeEntryStatus(string $status): string
    {
        $allowedStatuses = ['read', 'unread'];
        $status = sanitize_text_field($status);
        return in_array($status, $allowedStatuses, true) ? $status : 'unread';
    }

    /**
     * Sanitize form column value based on column type
     *
     * @param string $column
     * @param mixed $value
     * @return mixed
     */
    public static function sanitizeFormColumnValue(string $column, $value)
    {
        // Use FormRepository constants
        if ($column === 'starred') {
            return self::sanitizeStarredValue($value);
        }
        return sanitize_text_field($value);
    }

    /**
     * Sanitize entry column value based on column type
     *
     * @param string $column
     * @param mixed $value
     * @return mixed
     */
    public static function sanitizeEntryColumnValue(string $column, $value)
    {
        // Use EntryRepository constants
        if ($column === 'starred') {
            return self::sanitizeStarredValue($value);
        }
        if ($column === 'status') {
            return self::sanitizeEntryStatus($value);
        }
        return sanitize_text_field($value);
    }

    /**
     * Sanitize a single field
     *
     * @param mixed[] $field
     * @return mixed[]
     */
    public static function sanitizeField(array $field): array
    {
        return [
            'id' => (int)($field['id'] ?? 0),
            'fieldIndex' => (int)($field['fieldIndex'] ?? 0),
            'type' => sanitize_text_field($field['type'] ?? ''),
            'label' => sanitize_text_field($field['label'] ?? ''),
            'placeholder' => sanitize_text_field($field['placeholder'] ?? ''),
        ];
    }
}
