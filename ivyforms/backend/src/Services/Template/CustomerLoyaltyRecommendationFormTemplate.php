<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CustomerLoyaltyRecommendationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'customer_loyalty_recommendation_form',
            'name' => BackendStrings::getTemplateStrings()['customer_loyalty_recommendation_form'],
            'description' => BackendStrings::getTemplateStrings()['customer_loyalty_recommendation_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'customer-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'customer-loyalty-recommendation-form.svg',
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
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['customer_loyalty_recommendation_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getUsageFrequencyField(),
                    self::getOverallSatisfactionRatingField(),
                    self::getRecommendationLikelihoodField(),
                    self::getWhatKeepsYouComingBackField(),
                    self::getWhatCouldMakeMoreLoyalTextareaField(),
                    self::getEmailField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * How often do you use our product/service? (radio)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getUsageFrequencyField(): array
    {
        return [[
            'id' => 5,
            'fieldIndex' => 1,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['how_often_use_product_service'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'label' => BackendStrings::getTemplateStrings()['usage_first_time'],
                    'value' => 'first_time',
                    'position' => 1,
                ],
                [
                    'label' => BackendStrings::getTemplateStrings()['usage_occasionally'],
                    'value' => 'occasionally',
                    'position' => 2,
                ],
                [
                    'label' => BackendStrings::getTemplateStrings()['usage_regularly'],
                    'value' => 'regularly',
                    'position' => 3,
                ],
                [
                    'label' => BackendStrings::getTemplateStrings()['usage_long_term_customer'],
                    'value' => 'long_term',
                    'position' => 4,
                ],
            ]
        ]];
    }

    /**
     * Overall satisfaction rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOverallSatisfactionRatingField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 2,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['overall_satisfaction'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(1),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * How likely are you to recommend us? (rating)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRecommendationLikelihoodField(): array
    {
        return [[
            'id' => 12,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['how_likely_recommend_us'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => [
                [
                    'id' => 7,
                    'label' => BackendStrings::getTemplateStrings()['recommendation_very_likely'],
                    'value' => '5',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['recommendation_likely'],
                    'value' => '4',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['recommendation_not_sure'],
                    'value' => '3',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['recommendation_unlikely'],
                    'value' => '2',
                    'isDefault' => false,
                    'position' => 4,
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['recommendation_very_unlikely'],
                    'value' => '1',
                    'isDefault' => false,
                    'position' => 5,
                ],
            ],
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * What keeps you coming back? (checkbox)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWhatKeepsYouComingBackField(): array
    {
        return [[
            'id' => 18,
            'fieldIndex' => 4,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['what_keeps_you_coming_back'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'fieldOptions' => [
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['keeps_product_quality'],
                    'value' => 'product_quality',
                    'position' => 1,
                ],
                [
                    'id' => 14,
                    'label' => BackendStrings::getTemplateStrings()['keeps_pricing'],
                    'value' => 'pricing',
                    'position' => 2,
                ],
                [
                    'id' => 15,
                    'label' => BackendStrings::getTemplateStrings()['keeps_customer_support'],
                    'value' => 'customer_support',
                    'position' => 3,
                ],
                [
                    'id' => 16,
                    'label' => BackendStrings::getTemplateStrings()['keeps_ease_of_use'],
                    'value' => 'ease_of_use',
                    'position' => 4,
                ],
                [
                    'id' => 17,
                    'label' => BackendStrings::getTemplateStrings()['keeps_trust_in_brand'],
                    'value' => 'trust_in_brand',
                    'position' => 5,
                ],
            ]
        ]];
    }

    /**
     * What could make you more loyal? (textarea)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWhatCouldMakeMoreLoyalTextareaField(): array
    {
        return [[
            'id' => 19,
            'fieldIndex' => 5,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['what_could_make_you_more_loyal'],
            'placeholder' => BackendStrings::getTemplateStrings()['what_could_make_you_more_loyal_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 5,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * Email field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEmailField(): array
    {
        return [[
            'id' => 20,
            'fieldIndex' => 6,
            'type' => 'email',
            'label' => BackendStrings::getTemplateStrings()['email_address'],
            'placeholder' => 'name@example.com',
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
            'description' => BackendStrings::getTemplateStrings()['follow_up_email_description'],
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
                'position' => 1,
            ],
            [
                'id' => $startId + 1,
                'label' => BackendStrings::getNewFormStrings()['nice'],
                'value' => '2',
                'isDefault' => false,
                'position' => 2,
            ],
            [
                'id' => $startId + 2,
                'label' => BackendStrings::getNewFormStrings()['very_good'],
                'value' => '3',
                'isDefault' => false,
                'position' => 3,
            ],
            [
                'id' => $startId + 3,
                'label' => BackendStrings::getNewFormStrings()['awesome'],
                'value' => '4',
                'isDefault' => false,
                'position' => 4,
            ],
            [
                'id' => $startId + 4,
                'label' => BackendStrings::getNewFormStrings()['amazing'],
                'value' => '5',
                'isDefault' => false,
                'position' => 5,
            ],
        ];
    }
}
