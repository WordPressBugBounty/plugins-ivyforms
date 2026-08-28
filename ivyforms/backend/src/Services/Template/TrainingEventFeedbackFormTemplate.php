<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class TrainingEventFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'training_event_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['training_event_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['training_event_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'event-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'training-event-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['training_event_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getTrainingTopicDropdownField(),
                    self::getTrainingQualityRadioField(),
                    self::getInstructorQualityRatingFields(),
                    self::getSkillsGainedCheckboxField(),
                    self::getAdditionalCommentsTextareaField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Training topic dropdown field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTrainingTopicDropdownField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 1,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['training_topic'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_training_topic'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['technical_skills'],
                    'value' => 'technical_skills',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['soft_skills'],
                    'value' => 'soft_skills',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['leadership'],
                    'value' => 'leadership',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['compliance'],
                    'value' => 'compliance',
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
     * Training quality radio field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTrainingQualityRadioField(): array
    {
        return [[
            'id' => 10,
            'fieldIndex' => 2,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['training_level'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'fieldOptions' => [
                [
                    'id' => 7,
                    'label' => BackendStrings::getTemplateStrings()['too_basic'],
                    'value' => 'too_basic',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['just_right'],
                    'value' => 'just_right',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['too_advanced'],
                    'value' => 'too_advanced',
                    'isDefault' => false,
                    'position' => 3
                ],
            ]
        ]];
    }

    /**
     * Instructor quality rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getInstructorQualityRatingFields(): array
    {
        return [
            [
                'id' => 11,
                'fieldIndex' => 3,
                'type' => 'rating',
                'label' => BackendStrings::getTemplateStrings()['training_quality'],
                'required' => true,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 3,
                'labelPosition' => 'default',
                'ratingIcon' => 'star',
                'fieldOptions' => self::getStandardRatingOptions(9),
                'showValues' => false,
                'showRatingText' => true,
            ],
            [
                'id' => 12,
                'fieldIndex' => 4,
                'type' => 'rating',
                'label' => BackendStrings::getTemplateStrings()['training_knowledge'],
                'required' => true,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 4,
                'labelPosition' => 'default',
                'ratingIcon' => 'star',
                'fieldOptions' => self::getStandardRatingOptions(14),
                'showValues' => false,
                'showRatingText' => true,
            ]
        ];
    }

    /**
     * Skills gained checkbox field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSkillsGainedCheckboxField(): array
    {
        return [[
            'id' => 18,
            'fieldIndex' => 5,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['training_gained_skills'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'fieldOptions' => [
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['new_skills'],
                    'value' => 'new_skills',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 14,
                    'label' => BackendStrings::getTemplateStrings()['practical_knowledge'],
                    'value' => 'practical_knowledge',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 15,
                    'label' => BackendStrings::getTemplateStrings()['certification_or_credits'],
                    'value' => 'certification_or_credits',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 16,
                    'label' => BackendStrings::getTemplateStrings()['better_undestranding'],
                    'value' => 'better_undestranding',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 17,
                    'label' => BackendStrings::getTemplateStrings()['networking_opportunities'],
                    'value' => 'networking_opportunities',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Additional comments textarea field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalCommentsTextareaField(): array
    {
        return [[
            'id' => 19,
            'fieldIndex' => 5,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['additional_comments'],
            'placeholder' => BackendStrings::getTemplateStrings()['additional_comments_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 5,
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
