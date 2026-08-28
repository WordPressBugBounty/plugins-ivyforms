<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ServiceSpecificClientFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'service_specific_client_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['service_specific_client_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['service_specific_client_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'client-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'service-specific-client-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['service_specific_client_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getServiceReceivedDropdownField(),
                    self::getServiceMeetNeedsRadioField(),
                    self::getServiceQualityRatingField(),
                    self::getValueForMoneyRatingField(),
                    self::getServiceStrengthsCheckboxField(),
                    self::getAdditionalCommentsParagraphField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Service received dropdown field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getServiceReceivedDropdownField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 1,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['service_received'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_service'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['consulting'],
                    'value' => 'consulting',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['design'],
                    'value' => 'design',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['development'],
                    'value' => 'development',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['marketing'],
                    'value' => 'marketing',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 5,
                    'label' => BackendStrings::getTemplateStrings()['support'],
                    'value' => 'support',
                    'isDefault' => false,
                    'position' => 5
                ],
                [
                    'id' => 6,
                    'label' => BackendStrings::getTemplateStrings()['other'],
                    'value' => 'other',
                    'isDefault' => false,
                    'position' => 6
                ],
            ]
        ]];
    }

    /**
     * Service meet needs radio field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getServiceMeetNeedsRadioField(): array
    {
        return [[
            'id' => 12,
            'fieldIndex' => 2,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['did_service_meet_needs'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'fieldOptions' => [
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['fully'],
                    'value' => 'fully',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['mostly'],
                    'value' => 'mostly',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['partially'],
                    'value' => 'partially',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['not_at_all'],
                    'value' => 'not_at_all',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
        ]];
    }

    /**
     * Service quality rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getServiceQualityRatingField(): array
    {
        return [[
            'id' => 13,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['service_quality'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(11),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Value for money rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getValueForMoneyRatingField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['value_for_money'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(16),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Service strengths checkbox field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getServiceStrengthsCheckboxField(): array
    {
        return [[
            'id' => 20,
            'fieldIndex' => 5,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['strengths_of_service'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'fieldOptions' => [
                [
                    'id' => 15,
                    'label' => BackendStrings::getTemplateStrings()['expertise'],
                    'value' => 'expertise',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 16,
                    'label' => BackendStrings::getTemplateStrings()['communication'],
                    'value' => 'communication',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 17,
                    'label' => BackendStrings::getTemplateStrings()['speed'],
                    'value' => 'speed',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 18,
                    'label' => BackendStrings::getTemplateStrings()['flexibility'],
                    'value' => 'flexibility',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 19,
                    'label' => BackendStrings::getTemplateStrings()['results'],
                    'value' => 'results',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Additional comments paragraph field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalCommentsParagraphField(): array
    {
        return [[
            'id' => 21,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['additional_comments'],
            'placeholder' => BackendStrings::getTemplateStrings()['additional_comments_placeholder'],
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
