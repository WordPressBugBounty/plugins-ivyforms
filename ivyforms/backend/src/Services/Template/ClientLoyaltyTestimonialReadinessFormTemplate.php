<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ClientLoyaltyTestimonialReadinessFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'client_loyalty_testimonial_readiness_form',
            'name' => BackendStrings::getTemplateStrings()['client_loyalty_testimonial_readiness_form'],
            'description' => BackendStrings::getTemplateStrings()['client_loyalty_testimonial_readiness_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'client-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'client-loyalty-testimonial-readiness-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['client_loyalty_testimonial_readiness_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getLoyaltyLikelihoodRatingField(),
                    self::getOverallExperienceRatingField(),
                    self::getRecommendRadioField(),
                    self::getValueMostCheckboxField(),
                    self::getLoyaltyParagraphField(),
                    self::getTestimonialCheckboxField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Likelihood to continue working rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getLoyaltyLikelihoodRatingField(): array
    {
        return [[
            'id' => 1,
            'fieldIndex' => 1,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['likelihood_continue_working'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 1,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getLoyaltyRatingOptions(1),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Overall experience rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOverallExperienceRatingField(): array
    {
        return [[
            'id' => 2,
            'fieldIndex' => 2,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['overall_experience'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(6),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Recommendation radio field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRecommendRadioField(): array
    {
        return [[
            'id' => 8,
            'fieldIndex' => 3,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['would_you_recommend_us'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'fieldOptions' => [
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['definitely'],
                    'value' => 'definitely',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['probably'],
                    'value' => 'probably',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 5,
                    'label' => BackendStrings::getTemplateStrings()['not_sure'],
                    'value' => 'not_sure',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 6,
                    'label' => BackendStrings::getTemplateStrings()['probably_not'],
                    'value' => 'probably_not',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 7,
                    'label' => BackendStrings::getTemplateStrings()['definitely_not'],
                    'value' => 'definitely_not',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Value most checkbox field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getValueMostCheckboxField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 4,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['value_most_about_working'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'fieldOptions' => [
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['quality_of_work'],
                    'value' => 'quality_of_work',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['reliability'],
                    'value' => 'reliability',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['communication'],
                    'value' => 'communication',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 12,
                    'label' => BackendStrings::getTemplateStrings()['expertise'],
                    'value' => 'expertise',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['results'],
                    'value' => 'results',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Loyalty improvement paragraph
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getLoyaltyParagraphField(): array
    {
        return [[
            'id' => 15,
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
     * Testimonial readiness checkbox field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTestimonialCheckboxField(): array
    {
        return [[
            'id' => 20,
            'fieldIndex' => 6,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['open_to_providing_testimonial'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 6,
            'fieldOptions' => [
                [
                    'id' => 16,
                    'label' => BackendStrings::getTemplateStrings()['yes_written_testimonial'],
                    'value' => 'yes_written_testimonial',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 17,
                    'label' => BackendStrings::getTemplateStrings()['yes_video_testimonial'],
                    'value' => 'yes_video_testimonial',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 18,
                    'label' => BackendStrings::getTemplateStrings()['yes_case_study'],
                    'value' => 'yes_case_study',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 19,
                    'label' => BackendStrings::getTemplateStrings()['not_at_this_time'],
                    'value' => 'not_at_this_time',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
        ]];
    }

    /**
     * Likelihood rating options (Very unlikely is 1 star)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getLoyaltyRatingOptions(int $startId): array
    {
        return [
            [
                'id' => $startId,
                'label' => BackendStrings::getTemplateStrings()['very_unlikely'],
                'value' => 'very_unlikely',
                'isDefault' => false,
                'position' => 1
            ],
            [
                'id' => $startId + 1,
                'label' => BackendStrings::getTemplateStrings()['unlikely'],
                'value' => 'unlikely',
                'isDefault' => false,
                'position' => 2
            ],
            [
                'id' => $startId + 2,
                'label' => BackendStrings::getTemplateStrings()['not_sure'],
                'value' => 'not_sure',
                'isDefault' => false,
                'position' => 3
            ],
            [
                'id' => $startId + 3,
                'label' => BackendStrings::getTemplateStrings()['likely'],
                'value' => 'likely',
                'isDefault' => false,
                'position' => 4
            ],
            [
                'id' => $startId + 4,
                'label' => BackendStrings::getTemplateStrings()['very_likely'],
                'value' => 'very_likely',
                'isDefault' => false,
                'position' => 5
            ],
        ];
    }

    /**
     * Standard rating options
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
