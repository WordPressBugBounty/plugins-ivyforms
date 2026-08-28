<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Type-specific default field settings for IvyForms MCP field creation.
 */
class McpFieldTypeDefaultsHelper
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function defaultsForType(string $type, array $input): array
    {
        if (in_array($type, ['product', 'quantity', 'total'], true)) {
            return self::paymentFieldDefaults($type);
        }

        if (in_array($type, ['slider', 'file-upload', 'gdpr'], true)) {
            return self::uploadAndSliderDefaults($type);
        }

        if (in_array($type, ['date', 'time', 'date_time'], true)) {
            return self::dateTimeFieldDefaults($type);
        }

        if (in_array($type, ['likert', 'nps', 'rich_text', 'rating', 'radio', 'select', 'phone', 'address'], true)) {
            return self::choiceAndLayoutDefaults($type, $input);
        }

        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private static function paymentFieldDefaults(string $type): array
    {
        $componentStrings = self::componentStrings();

        if ($type === 'product') {
            $productOptions = self::defaultProductOptions($componentStrings);

            return [
                'defaultValue' => 'product1',
                'fieldOptions' => $productOptions,
                'showValues'   => true,
                'htmlContent'  => wp_json_encode([
                    'configVersion'             => 1,
                    'productType'               => 'radio',
                    'paymentAmount'             => '',
                    'amountLabel'               => '',
                    'showPriceAfterItemLabel'   => false,
                    'prices'                    => [
                        'product1' => '29.99',
                        'product2' => '19.99',
                        'product3' => '9.99',
                    ],
                    'choiceLayout'              => 'inline',
                    'columnsCount'              => 3,
                ], JSON_UNESCAPED_UNICODE) ?: '',
            ];
        }

        if ($type === 'quantity') {
            return [
                'minValue'     => 1,
                'maxValue'     => null,
                'step'         => 1,
                'limitRange'   => true,
                'numberFormat' => 'us_int',
                'htmlContent'  => wp_json_encode([
                    'quantityConfigVersion'   => 1,
                    'linkedProductFieldIndex' => null,
                ], JSON_UNESCAPED_UNICODE) ?: '',
            ];
        }

        return [
            'readOnly'        => true,
            'requiredMessage' => $componentStrings['total_default_required_message']
                ?? __('A total greater than zero is required.', 'ivyforms'),
            'htmlContent'     => wp_json_encode([
                'totalConfigVersion'            => 1,
                'enablePaymentSummary'          => false,
                'showPaymentSummaryCloseButton' => true,
                'emptyPaymentMessageHtml'       => '',
                'enableAutocomplete'            => false,
            ], JSON_UNESCAPED_UNICODE) ?: '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function uploadAndSliderDefaults(string $type): array
    {
        $commonStrings = self::commonStrings();

        if ($type === 'slider') {
            return [
                'minValue'     => 0,
                'maxValue'     => 100,
                'step'         => 1,
                'defaultValue' => '50',
            ];
        }

        if ($type === 'file-upload') {
            return [
                'placeholder'             => $commonStrings['file_upload_placeholder_drag_here']
                    ?? __('Drag file here or click to upload', 'ivyforms'),
                'maxFileSizeMb'           => 999,
                'saveUploadsTo'           => 'ivyforms',
                'allowedFileExtensions'   => [],
                'allowMultiple'           => false,
                'maxUploadCount'          => 0,
            ];
        }

        return [
            'agreementText' => __('I agree to the terms and conditions.', 'ivyforms'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function dateTimeFieldDefaults(string $type): array
    {
        $builderStrings = self::builderStrings();

        if ($type === 'date_time') {
            return [
                'readOnly'          => false,
                'requiredMessage'   => $builderStrings['this_field_is_required']
                    ?? __('This field is required', 'ivyforms'),
                'dateTimeFieldType' => 'picker',
                'dateTimeMode'      => 'single',
                'defaultValueMode'  => 'none',
                'dateFormat'        => 'MM/DD/YYYY',
                'timeFormat'        => '24h',
                'enableSeconds'     => false,
            ];
        }

        if ($type === 'date') {
            return [
                'dateFieldType' => 'date-picker',
                'dateFormat'    => 'MM/DD/YYYY',
            ];
        }

        return [
            'timeFieldType' => 'time-picker',
            'timeFormat'    => '24h',
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private static function choiceAndLayoutDefaults(string $type, array $input): array
    {
        $builderStrings = self::builderStrings();

        switch ($type) {
            case 'nps':
                return [
                    'negativeStatementLabel' => '',
                    'positiveStatementLabel' => '',
                ];
            case 'rich_text':
                return [
                    'defaultValue' => sanitize_text_field($input['defaultValue'] ?? ''),
                ];
            case 'likert':
                return [
                    'likertChoiceType'           => 'radio',
                    'likertSingleRow'            => false,
                    'likertRepeatColumnHeadings' => false,
                    'showValues'                 => true,
                    'likertColumns'              => self::defaultLikertColumns(),
                ];
            case 'rating':
                return [
                    'fieldOptions' => self::defaultRatingOptions($builderStrings),
                    'showValues'   => false,
                ];
            case 'radio':
            case 'select':
                return [
                    'defaultValue' => 'option1',
                ];
            case 'phone':
                return [
                    'phoneAutoDetect' => true,
                ];
            case 'address':
                return [
                    'labelPosition' => 'top',
                ];
            default:
                return [];
        }
    }

    /**
     * @param array<string, string> $componentStrings
     * @return array<int, array<string, mixed>>
     */
    private static function defaultProductOptions(array $componentStrings): array
    {
        $productLabel = $componentStrings['product'] ?? __('Product', 'ivyforms');

        return [
            [
                'id'        => 0,
                'fieldId'   => 0,
                'label'     => $productLabel . ' 1',
                'value'     => 'product1',
                'isDefault' => true,
                'position'  => 1,
            ],
            [
                'id'        => 1,
                'fieldId'   => 0,
                'label'     => $productLabel . ' 2',
                'value'     => 'product2',
                'isDefault' => false,
                'position'  => 2,
            ],
            [
                'id'        => 2,
                'fieldId'   => 0,
                'label'     => $productLabel . ' 3',
                'value'     => 'product3',
                'isDefault' => false,
                'position'  => 3,
            ],
        ];
    }

    /**
     * @param array<string, string> $builderStrings
     * @return array<int, array<string, mixed>>
     */
    private static function defaultRatingOptions(array $builderStrings): array
    {
        $labels = [
            1 => $builderStrings['good'] ?? __('Good', 'ivyforms'),
            2 => $builderStrings['nice'] ?? __('Nice', 'ivyforms'),
            3 => $builderStrings['very_good'] ?? __('Very good', 'ivyforms'),
            4 => $builderStrings['awesome'] ?? __('Awesome', 'ivyforms'),
            5 => $builderStrings['amazing'] ?? __('Amazing', 'ivyforms'),
        ];

        $options = [];
        foreach ($labels as $value => $optionLabel) {
            $options[] = [
                'id'        => $value,
                'fieldId'   => 0,
                'label'     => $optionLabel,
                'value'     => (string) $value,
                'isDefault' => false,
                'position'  => $value,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    private static function defaultLikertColumns(): array
    {
        $strings = self::growthFieldOptionsStrings();

        return [
            [
                'label' => $strings['strongly_disagree'] ?? __('Strongly disagree', 'ivyforms'),
                'value' => '1',
            ],
            [
                'label' => $strings['disagree'] ?? __('Disagree', 'ivyforms'),
                'value' => '2',
            ],
            [
                'label' => $strings['neutral'] ?? __('Neutral', 'ivyforms'),
                'value' => '3',
            ],
            [
                'label' => $strings['agree'] ?? __('Agree', 'ivyforms'),
                'value' => '4',
            ],
            [
                'label' => $strings['strongly_agree'] ?? __('Strongly agree', 'ivyforms'),
                'value' => '5',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function growthFieldOptionsStrings(): array
    {
        if (!class_exists(\IvyFormsPro\Plans\Growth\Services\Translations\BackendStrings::class)) {
            return [];
        }

        /** @var array<string, string> */
        return \IvyFormsPro\Plans\Growth\Services\Translations\BackendStrings::getFieldOptionsStrings();
    }

    /**
     * @return array<string, string>
     */
    private static function builderStrings(): array
    {
        /** @var array<string, string> */
        return BackendStrings::getNewFormStrings();
    }

    /**
     * @return array<string, string>
     */
    private static function commonStrings(): array
    {
        /** @var array<string, string> */
        return BackendStrings::getCommonStrings();
    }

    /**
     * @return array<string, string>
     */
    private static function componentStrings(): array
    {
        /** @var array<string, string> */
        return BackendStrings::getComponentsStrings();
    }
}
