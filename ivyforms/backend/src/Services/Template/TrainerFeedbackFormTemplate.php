<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class TrainerFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'trainer_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['trainer_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['trainer_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'training-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'trainer-feedback-form.svg',
            'showRatingText' => true,
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
                'name' => BackendStrings::getTemplateStrings()['trainer_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getTrainerClarityRatingField(),
                    self::getTrainerExpertiseRatingField(),
                    self::getEngagementInteractionRatingField(),
                    self::getTrainerStrengthsField(),
                    self::getAreasForImprovementField(),
                    self::getNameField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Trainer clarity rating with custom options
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTrainerClarityRatingField(): array
    {
        return [[
            'id' => 5,
            'fieldIndex' => 1,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['trainer_clarity_communication'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 1,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['poor'],
                    'value' => 'poor',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['fair'],
                    'value' => 'fair',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getNewFormStrings()['good'],
                    'value' => 'good',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
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
     * Trainer expertise rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTrainerExpertiseRatingField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 2,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['trainer_expertise'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(5),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Engagement and interaction rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEngagementInteractionRatingField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['engagement_interaction'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(10),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Trainer strengths checkbox
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTrainerStrengthsField(): array
    {
        return [[
            'id' => 13,
            'fieldIndex' => 4,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['trainer_strengths'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'fieldOptions' => [
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['subject_knowledge'],
                    'value' => 'subject_knowledge',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['clear_explanations'],
                    'value' => 'clear_explanations',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['approachability'],
                    'value' => 'approachability',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['encouraged_participation'],
                    'value' => 'encouraged_participation',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 12,
                    'label' => BackendStrings::getTemplateStrings()['time_management'],
                    'value' => 'time_management',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Areas for improvement textarea
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAreasForImprovementField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 5,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['areas_for_improvement'],
            'placeholder' => BackendStrings::getTemplateStrings()['areas_for_improvement_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 5,
            'rows' => 3,
        ]];
    }

    /**
     * Optional name field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getNameField(): array
    {
        return [
            [
                'id' => 15,
                'fieldIndex' => 6,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['name_optional'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'required' => false,
                'readonly' => false,
                'position' => 6,
            ],
            [
                'id' => 16,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['first_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_first_name'],
                'required' => false,
                'parentId' => 6,
                'fieldIndex' => 6,
                'defaultValue' => '',
                'position' => 1,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            [
                'id' => 17,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['last_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_last_name'],
                'required' => false,
                'parentId' => 6,
                'fieldIndex' => 6,
                'defaultValue' => '',
                'position' => 2,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
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
