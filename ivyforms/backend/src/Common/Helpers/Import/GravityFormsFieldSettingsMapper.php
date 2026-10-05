<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Apply Gravity Forms type-specific settings to mapped IvyForms fields.
 */
class GravityFormsFieldSettingsMapper
{
    /**
     * Resolve fields whose rendered input type is configurable.
     *
     * @param string $gravityType
     * @param string $ivyType
     * @param array<string, mixed> $rawField
     * @return string
     */
    public static function resolveType(string $gravityType, string $ivyType, array $rawField): string
    {
        if (in_array($gravityType, ['multi_choice', 'image_choice'], true)) {
            return ($rawField['inputType'] ?? 'radio') === 'checkbox' ? 'checkbox' : 'radio';
        }

        return $ivyType;
    }

    /**
     * @param array<int, mixed> $confirmations
     * @return array<string, mixed>|null
     */
    public static function findDefaultConfirmation(array $confirmations): ?array
    {
        foreach ($confirmations as $confirmation) {
            if (
                is_array($confirmation)
                && !empty($confirmation['isDefault'])
                && empty($confirmation['event'])
            ) {
                return $confirmation;
            }
        }

        foreach ($confirmations as $confirmation) {
            if (is_array($confirmation) && empty($confirmation['event'])) {
                return $confirmation;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $rawField
     * @param string $gravityType
     * @param string $ivyType
     * @param array<int, array<string, mixed>> $options
     * @return void
     */
    public static function apply(
        array &$field,
        array $rawField,
        string $gravityType,
        string $ivyType,
        array $options
    ): void {
        if ($ivyType === 'gdpr') {
            $field['agreementText'] = sanitize_text_field((string) ($rawField['checkboxLabel'] ?? ''));
        }

        self::applyDateTimeSettings($field, $rawField, $ivyType);

        if ($ivyType === 'product') {
            $field['htmlContent'] = self::productSettings($rawField, $gravityType, $options);
        }

        if ($ivyType === 'quantity') {
            $field['minValue'] = 1;
            $field['step'] = 1;
            $field['limitRange'] = true;
            $field['numberFormat'] = 'us_int';
            $field['htmlContent'] = wp_json_encode([
                'quantityConfigVersion'   => 1,
                'linkedProductFieldIndex' => null,
            ], JSON_UNESCAPED_UNICODE) ?: '';
        }

        if ($ivyType === 'total') {
            $field['readOnly'] = true;
            $field['htmlContent'] = wp_json_encode([
                'totalConfigVersion'            => 1,
                'enablePaymentSummary'          => false,
                'showPaymentSummaryCloseButton' => true,
                'emptyPaymentMessageHtml'       => '',
                'enableAutocomplete'            => false,
            ], JSON_UNESCAPED_UNICODE) ?: '';
        }
    }

    /**
     * @param array<string, mixed> $field
     * @param array<string, mixed> $rawField
     * @param string $ivyType
     * @return void
     */
    private static function applyDateTimeSettings(array &$field, array $rawField, string $ivyType): void
    {
        if ($ivyType === 'time') {
            $field['timeFieldType'] = 'time-picker';
            $field['timeFormat'] = (string) ($rawField['timeFormat'] ?? '12') === '24' ? '24h' : 'ampm';
        }

        if ($ivyType !== 'date') {
            return;
        }

        $dateFormats = [
            'mdy'      => 'MM/DD/YYYY',
            'dmy'      => 'DD/MM/YYYY',
            'dmy_dash' => 'DD-MM-YYYY',
            'dmy_dot'  => 'DD.MM.YYYY',
            'ymd_slash' => 'YYYY/MM/DD',
            'ymd_dash' => 'YYYY-MM-DD',
            'ymd_dot'  => 'YYYY.MM.DD',
        ];
        $field['dateFieldType'] = 'date-picker';
        $field['dateFormat'] = $dateFormats[(string) ($rawField['dateFormat'] ?? 'mdy')] ?? 'MM/DD/YYYY';
    }

    /**
     * @param array<string, mixed> $rawField
     * @param string $gravityType
     * @param array<int, array<string, mixed>> $options
     * @return string
     */
    private static function productSettings(array $rawField, string $gravityType, array $options): string
    {
        $inputType = (string) ($rawField['inputType'] ?? $gravityType);
        $productTypeMap = [
            'checkbox'      => 'checkbox',
            'radio'         => 'radio',
            'select'        => 'select',
            'donation'      => 'user_defined',
            'singleproduct' => 'single',
        ];
        $productType = $productTypeMap[$inputType] ?? 'single';
        $rawOptions = is_array($rawField['choices'] ?? null) ? array_values($rawField['choices']) : [];
        $prices = [];

        foreach ($options as $index => $option) {
            $rawOption = is_array($rawOptions[$index] ?? null) ? $rawOptions[$index] : [];
            $prices[(string) $option['value']] = sanitize_text_field((string) ($rawOption['price'] ?? ''));
        }

        return wp_json_encode([
            'configVersion'           => 1,
            'productType'             => $productType,
            'paymentAmount'           => sanitize_text_field((string) ($rawField['basePrice'] ?? '')),
            'amountLabel'             => '',
            'showPriceAfterItemLabel' => !empty($prices),
            'prices'                  => $prices,
            'choiceLayout'            => 'inline',
            'columnsCount'            => 3,
        ], JSON_UNESCAPED_UNICODE) ?: '';
    }
}
