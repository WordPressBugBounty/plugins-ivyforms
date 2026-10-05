<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Apply WPForms type-specific settings to mapped IvyForms fields.
 */
class WpFormsFieldSettingsMapper
{
    /**
     * Resolve source settings that refine the direct type map.
     *
     * @param string $wpFormsType
     * @param string $ivyType
     * @param array<string, mixed> $rawField
     * @return string
     */
    public static function resolveType(string $wpFormsType, string $ivyType, array $rawField): string
    {
        if ($wpFormsType === 'select' && !empty($rawField['multiple'])) {
            return 'multi-select';
        }

        if ($wpFormsType === 'date-time' && ($rawField['format'] ?? '') === 'time') {
            return 'time';
        }

        return $ivyType;
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $rawField
     * @param string $wpFormsType
     * @param string $ivyType
     * @param array<int, array<string, mixed>> $options
     * @return void
     */
    public static function apply(
        array &$field,
        array $rawField,
        string $wpFormsType,
        string $ivyType,
        array $options
    ): void {
        self::applySliderSettings($field, $rawField, $ivyType);
        self::applyGdprSettings($field, $rawField, $ivyType);
        self::applyDateTimeSettings($field, $rawField, $wpFormsType, $ivyType);
        self::applyFileSettings($field, $rawField, $ivyType);
        self::applyRichTextSettings($field, $rawField, $wpFormsType);
        self::applyPaymentSettings($field, $rawField, $wpFormsType, $ivyType, $options);
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $rawField
     * @param string $wpFormsType
     * @param string $ivyType
     * @return void
     */
    private static function applyDateTimeSettings(
        array &$field,
        array $rawField,
        string $wpFormsType,
        string $ivyType
    ): void {
        if ($wpFormsType !== 'date-time') {
            return;
        }

        $isTime = $ivyType === 'time';
        $field['placeholder'] = sanitize_text_field(
            (string) ($rawField[$isTime ? 'time_placeholder' : 'date_placeholder'] ?? '')
        );
        if ($isTime) {
            $sourceFormat = (string) ($rawField['time_format'] ?? '');
            $field['timeFieldType'] = 'time-picker';
            $field['timeFormat'] = preg_match('/[aA]/', $sourceFormat) === 1 ? 'ampm' : '24h';
            return;
        }

        $dateFormats = [
            'm/d/Y' => 'MM/DD/YYYY',
            'd/m/Y' => 'DD/MM/YYYY',
            'Y-m-d' => 'YYYY-MM-DD',
            'm.d.Y' => 'MM.DD.YYYY',
            'd.m.Y' => 'DD.MM.YYYY',
        ];
        $sourceFormat = (string) ($rawField['date_format'] ?? 'm/d/Y');
        $field['dateFieldType'] = 'date-picker';
        $field['dateFormat'] = $dateFormats[$sourceFormat] ?? 'MM/DD/YYYY';
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $rawField
     * @param string $ivyType
     * @return void
     */
    private static function applyFileSettings(array &$field, array $rawField, string $ivyType): void
    {
        if ($ivyType !== 'file-upload') {
            return;
        }

        $extensions = preg_split('/[\s,]+/', (string) ($rawField['extensions'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $field['allowedFileExtensions'] = is_array($extensions) ? array_values($extensions) : [];
        $field['maxFileSizeMb'] = is_numeric($rawField['max_size'] ?? null) ? (int) $rawField['max_size'] : 0;
        $field['allowMultiple'] = !empty($rawField['max_file_number']);
        $field['maxUploadCount'] = is_numeric($rawField['max_file_number'] ?? null)
            ? (int) $rawField['max_file_number']
            : 0;
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $rawField
     * @param string $wpFormsType
     * @return void
     */
    private static function applyRichTextSettings(array &$field, array $rawField, string $wpFormsType): void
    {
        if ($wpFormsType !== 'richtext') {
            return;
        }

        $field['defaultValue'] = wp_kses_post((string) ($rawField['content'] ?? ''));
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $rawField
     * @param string $ivyType
     * @return void
     */
    private static function applySliderSettings(array &$field, array $rawField, string $ivyType): void
    {
        if ($ivyType !== 'slider') {
            return;
        }

        $field['minValue'] = is_numeric($rawField['min'] ?? null) ? (float) $rawField['min'] : 0;
        $field['maxValue'] = is_numeric($rawField['max'] ?? null) ? (float) $rawField['max'] : 100;
        $field['step'] = is_numeric($rawField['step'] ?? null) ? (float) $rawField['step'] : 1;
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $rawField
     * @param string $ivyType
     * @return void
     */
    private static function applyGdprSettings(array &$field, array $rawField, string $ivyType): void
    {
        if ($ivyType !== 'gdpr') {
            return;
        }

        $rawOptions = $rawField['choices'] ?? [];
        $agreement = is_array($rawOptions) ? reset($rawOptions) : null;
        $field['agreementText'] = is_array($agreement)
            ? sanitize_text_field((string) ($agreement['label'] ?? ''))
            : '';
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $rawField
     * @param string $wpFormsType
     * @param string $ivyType
     * @param array<int, array<string, mixed>> $options
     * @return void
     */
    private static function applyPaymentSettings(
        array &$field,
        array $rawField,
        string $wpFormsType,
        string $ivyType,
        array $options
    ): void {
        if ($ivyType === 'product') {
            $field['htmlContent'] = self::buildProductSettings($rawField, $wpFormsType, $options);
        }

        if ($ivyType === 'total') {
            $field['readOnly'] = true;
            $field['htmlContent'] = self::totalSettings();
        }
    }

    /**
     * @param array<string, mixed> $rawField
     * @param string $wpFormsType
     * @param array<int, array<string, mixed>> $options
     * @return string
     */
    private static function buildProductSettings(array $rawField, string $wpFormsType, array $options): string
    {
        $productTypeMap = [
            'payment-checkbox' => 'checkbox',
            'payment-multiple' => 'radio',
            'payment-select'   => 'select',
        ];
        $productType = $productTypeMap[$wpFormsType] ?? 'single';
        if ($wpFormsType === 'payment-single' && ($rawField['format'] ?? '') === 'user_defined') {
            $productType = 'user_defined';
        }

        $rawOptions = is_array($rawField['choices'] ?? null) ? array_values($rawField['choices']) : [];
        $prices = [];
        foreach ($options as $index => $option) {
            $rawOption = is_array($rawOptions[$index] ?? null) ? $rawOptions[$index] : [];
            $prices[(string) $option['value']] = sanitize_text_field((string) ($rawOption['value'] ?? ''));
        }

        return wp_json_encode([
            'configVersion'           => 1,
            'productType'             => $productType,
            'paymentAmount'           => sanitize_text_field((string) ($rawField['price'] ?? '')),
            'amountLabel'             => sanitize_text_field((string) ($rawField['price_label'] ?? '')),
            'showPriceAfterItemLabel' => !empty($prices),
            'prices'                  => $prices,
            'choiceLayout'            => 'inline',
            'columnsCount'            => 3,
        ], JSON_UNESCAPED_UNICODE) ?: '';
    }

    /**
     * @return string
     */
    private static function totalSettings(): string
    {
        return wp_json_encode([
            'totalConfigVersion'            => 1,
            'enablePaymentSummary'          => false,
            'showPaymentSummaryCloseButton' => true,
            'emptyPaymentMessageHtml'       => '',
            'enableAutocomplete'            => false,
        ], JSON_UNESCAPED_UNICODE) ?: '';
    }
}
