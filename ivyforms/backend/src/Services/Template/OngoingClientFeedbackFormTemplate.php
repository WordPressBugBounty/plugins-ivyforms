<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class OngoingClientFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'ongoing_client_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['ongoing_client_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['ongoing_client_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'client-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'ongoing-client-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['ongoing_client_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getLengthOfCollaborationRadioField(),
                    self::getOverallSatisfactionRatingField(),
                    self::getCommunicationResponsivenessRatingField(),
                    self::getServicesUsedCheckboxField(),
                    self::getSuggestionsForImprovementParagraphField(),
                    self::getPhoneNumberField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Length of collaboration radio field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getLengthOfCollaborationRadioField(): array
    {
        return [[
            'id' => 5,
            'fieldIndex' => 1,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['length_of_collaboration'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['less_than_3_months'],
                    'value' => 'less_than_3_months',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['three_to_six_months'],
                    'value' => '3_6_months',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['six_to_twelve_months'],
                    'value' => '6_12_months',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['more_than_1_year'],
                    'value' => 'more_than_1_year',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
        ]];
    }

    /**
     * Overall satisfaction rating field
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
            'fieldOptions' => self::getStandardRatingOptions(5),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Communication & responsiveness rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCommunicationResponsivenessRatingField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['communication_responsiveness'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(10),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Services used checkbox field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getServicesUsedCheckboxField(): array
    {
        return [[
            'id' => 13,
            'fieldIndex' => 4,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['services_used'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'fieldOptions' => [
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['strategy_planning'],
                    'value' => 'strategy_planning',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['ongoing_support'],
                    'value' => 'ongoing_support',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['maintenance'],
                    'value' => 'maintenance',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['consulting'],
                    'value' => 'consulting',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 12,
                    'label' => BackendStrings::getTemplateStrings()['reporting_analytics'],
                    'value' => 'reporting_analytics',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Suggestions for improvement paragraph field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSuggestionsForImprovementParagraphField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 5,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['suggestions_for_improvement'],
            'placeholder' => BackendStrings::getTemplateStrings()['suggestions_for_improvement_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 5,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * Phone number field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPhoneNumberField(): array
    {
        return [[
            'id' => 15,
            'fieldIndex' => 6,
            'type' => 'phone',
            'label' => BackendStrings::getTemplateStrings()['phone_number'],
            'placeholder' => '+1 (_) _-_',
            'description' => BackendStrings::getTemplateStrings()['phone_follow_up_description'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
            'labelPosition' => 'default',
            'phoneAutoDetect' => true,
        ]];
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
