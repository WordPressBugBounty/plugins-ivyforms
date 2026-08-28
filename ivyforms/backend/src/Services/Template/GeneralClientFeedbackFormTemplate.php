<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class GeneralClientFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'general_client_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['general_client_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['general_client_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'client-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'general-client-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['general_client_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getEngagementTypeDropdownField(),
                    self::getCollaborationRadioField(),
                    self::getOverallSatisfactionRatingField(),
                    self::getAreasStoodOutCheckboxField(),
                    self::getAdditionalFeedbackTextareaField(),
                    self::getEmailField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Type of engagement dropdown field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEngagementTypeDropdownField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 1,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['type_of_engagement'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_engagement_type'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['one_time_project'],
                    'value' => 'one_time_project',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['ongoing_retainer'],
                    'value' => 'ongoing_retainer',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['consulting'],
                    'value' => 'consulting',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['support_services'],
                    'value' => 'support_services',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 5,
                    'label' => BackendStrings::getTemplateStrings()['other'],
                    'value' => 'other',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Collaboration description rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCollaborationRadioField(): array
    {
        return [[
            'id' => 11,
            'fieldIndex' => 2,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['how_describe_collaboration'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => [
                [
                    'id' => 7,
                    'label' => BackendStrings::getTemplateStrings()['poor'],
                    'value' => 'poor',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['fair'],
                    'value' => 'fair',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getNewFormStrings()['good'],
                    'value' => 'good',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['excellent'],
                    'value' => 'excellent',
                    'isDefault' => false,
                    'position' => 4
                ],
            ],
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Overall client satisfaction rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOverallSatisfactionRatingField(): array
    {
        return [[
            'id' => 12,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['overall_client_satisfaction'],
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
     * Areas that stood out checkbox field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAreasStoodOutCheckboxField(): array
    {
        return [[
            'id' => 18,
            'fieldIndex' => 4,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['what_areas_stood_out'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'fieldOptions' => [
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['communication'],
                    'value' => 'communication',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 14,
                    'label' => BackendStrings::getTemplateStrings()['expertise'],
                    'value' => 'expertise',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 15,
                    'label' => BackendStrings::getTemplateStrings()['timeliness'],
                    'value' => 'timeliness',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 16,
                    'label' => BackendStrings::getTemplateStrings()['quality_of_deliverables'],
                    'value' => 'quality_of_deliverables',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 17,
                    'label' => BackendStrings::getTemplateStrings()['flexibility'],
                    'value' => 'flexibility',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Additional feedback textarea field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalFeedbackTextareaField(): array
    {
        return [[
            'id' => 19,
            'fieldIndex' => 5,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['additional_feedback'],
            'placeholder' => BackendStrings::getTemplateStrings()['additional_feedback_placeholder'],
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
            'label' => BackendStrings::getNewFormStrings()['email'],
            'placeholder' => BackendStrings::getTemplateStrings()['enter_email_address'],
            'description' => BackendStrings::getTemplateStrings()['email_follow_up_description'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
            'labelPosition' => 'default',
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
