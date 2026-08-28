<?php

declare(strict_types=1);

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Product Feedback Form Template
 *
 * Collect feedback about a specific product to improve quality, features, and usability.
 */
class ProductFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id'          => 'product_feedback_form',
            'name'        => BackendStrings::getTemplateStrings()['product_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['product_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'customer-feedback-forms',
            'is_pro'      => false,
            'screenshot'  => IVYFORMS_TEMPLATES_IMAGES_URL . 'product-feedback-form.svg',
        ];
    }

    /**
     * Get the full template including form data
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return array_merge(
            self::getTemplateMeta(),
            [
            'form_data'   => [
                'name'                => BackendStrings::getTemplateStrings()['product_feedback_form'],
                'published'           => 1,
                'showTitle'           => 1,
                'storeEntries'        => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields'              => array_merge(
                    self::getProductNameField(),
                    self::getIssuesExperiencedCheckboxField(),
                    self::getProductQualityRatingField(),
                    self::getValueForMoneyRatingField(),
                    self::getWhatDidYouLikeMostTextareaField(),
                    self::getWhatCanBeImprovedTextareaField()
                ),
                'settings'            => []
            ]
            ]
        );
    }

    /**
     * Product name dropdown (required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProductNameField(): array
    {
        return [[
            'id'           => 5,
            'fieldIndex'   => 1,
            'type'         => 'select',
            'label'        => BackendStrings::getTemplateStrings()['product_name'],
            'required'     => true,
            'parentId'     => null,
            'defaultValue' => '',
            'placeholder'  => BackendStrings::getTemplateStrings()['product_name_placeholder'],
            'position'     => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label'     => BackendStrings::getTemplateStrings()['product_a'],
                    'value'     => 'product_a',
                    'isDefault' => false,
                    'position'  => 1,
                ],
                [
                    'id' => 2,
                    'label'     => BackendStrings::getTemplateStrings()['product_b'],
                    'value'     => 'product_b',
                    'isDefault' => false,
                    'position'  => 2,
                ],
                [
                    'id' => 3,
                    'label'     => BackendStrings::getTemplateStrings()['product_c'],
                    'value'     => 'product_c',
                    'isDefault' => false,
                    'position'  => 3,
                ],
                [
                    'id' => 4,
                    'label'     => BackendStrings::getTemplateStrings()['other'],
                    'value'     => 'other',
                    'isDefault' => false,
                    'position'  => 4,
                ],
            ]
        ]];
    }

    /**
     * Issues experienced checkbox (optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getIssuesExperiencedCheckboxField(): array
    {
        return [[
            'id'           => 11,
            'fieldIndex'   => 2,
            'type'         => 'checkbox',
            'label'        => BackendStrings::getTemplateStrings()['issues_experienced'],
            'required'     => false,
            'parentId'     => null,
            'defaultValue' => '',
            'placeholder'  => '',
            'position'     => 2,
            'fieldOptions' => [
                [
                    'id'        => 6,
                    'label'     => BackendStrings::getNewFormStrings()['difficult_to_use'],
                    'value'     => 'difficult_to_use',
                    'isDefault' => false,
                    'position'  => 1,
                ],
                [
                    'id'        => 7,
                    'label'     => BackendStrings::getTemplateStrings()['missing_features'],
                    'value'     => 'missing_features',
                    'isDefault' => false,
                    'position'  => 2,
                ],
                [
                    'id'        => 8,
                    'label'     => BackendStrings::getTemplateStrings()['performance_issues'],
                    'value'     => 'performance_issues',
                    'isDefault' => false,
                    'position'  => 3,
                ],
                [
                    'id'        => 9,
                    'label'     => BackendStrings::getTemplateStrings()['quality_concerns'],
                    'value'     => 'quality_concerns',
                    'isDefault' => false,
                    'position'  => 4,
                ],
                [
                    'id'        => 10,
                    'label'     => BackendStrings::getTemplateStrings()['no_issues'],
                    'value'     => 'no_issues',
                    'isDefault' => false,
                    'position'  => 5,
                ],
            ]
        ]];
    }

    /**
     * Product quality rating (required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProductQualityRatingField(): array
    {
        return [[
            'id'             => 12,
            'fieldIndex'     => 3,
            'type'           => 'rating',
            'label'          => BackendStrings::getTemplateStrings()['product_quality'],
            'required'       => true,
            'defaultValue'   => '',
            'placeholder'    => '',
            'position'       => 3,
            'labelPosition'  => 'default',
            'ratingIcon'     => 'star',
            'fieldOptions'   => self::getStandardRatingOptions(5),
            'showValues'     => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Value for money rating (required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getValueForMoneyRatingField(): array
    {
        return [[
            'id'             => 13,
            'fieldIndex'     => 4,
            'type'           => 'rating',
            'label'          => BackendStrings::getTemplateStrings()['value_for_money'],
            'required'       => true,
            'defaultValue'   => '',
            'placeholder'    => '',
            'position'       => 4,
            'labelPosition'  => 'default',
            'ratingIcon'     => 'star',
            'fieldOptions'   => self::getStandardRatingOptions(10),
            'showValues'     => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * What did you like the most textarea (optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWhatDidYouLikeMostTextareaField(): array
    {
        return [[
            'id'            => 14,
            'fieldIndex'    => 5,
            'type'          => 'textarea',
            'label'         => BackendStrings::getTemplateStrings()['what_did_you_like_most'],
            'placeholder'   => BackendStrings::getTemplateStrings()['what_did_you_like_most_placeholder'],
            'required'      => false,
            'parentId'      => null,
            'defaultValue'  => '',
            'position'      => 5,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * What can be improved textarea (optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWhatCanBeImprovedTextareaField(): array
    {
        return [[
            'id'            => 15,
            'fieldIndex'    => 6,
            'type'          => 'textarea',
            'label'         => BackendStrings::getTemplateStrings()['what_can_be_improved'],
            'placeholder'   => BackendStrings::getTemplateStrings()['what_can_be_improved_placeholder'],
            'required'      => false,
            'parentId'      => null,
            'defaultValue'  => '',
            'position'      => 6,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * Standard rating options (1-5)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getStandardRatingOptions(int $startId): array
    {
        return [
            [
                'id' => $startId,
                'label' => BackendStrings::getNewFormStrings()['good'],
                'value' => '1',
                'isDefault' => false,
                'position' => 1
            ],
            [
                'id' => $startId + 1,
                'label' => BackendStrings::getNewFormStrings()['nice'],
                'value' => '2',
                'isDefault' => false,
                'position' => 2
            ],
            [
                'id' => $startId + 2,
                'label' => BackendStrings::getNewFormStrings()['very_good'],
                'value' => '3',
                'isDefault' => false,
                'position' => 3
            ],
            [
                'id' => $startId + 3,
                'label' => BackendStrings::getNewFormStrings()['awesome'],
                'value' => '4',
                'isDefault' => false,
                'position' => 4
            ],
            [
                'id' => $startId + 4,
                'label' => BackendStrings::getNewFormStrings()['amazing'],
                'value' => '5',
                'isDefault' => false,
                'position' => 5
            ],
        ];
    }
}
