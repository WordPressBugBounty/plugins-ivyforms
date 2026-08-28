<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class EmployeeTrainingFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'employee_training_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['employee_training_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['employee_training_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'training-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'employee-training-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['employee_training_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getDepartmentField(),
                    self::getTrainingRelevanceField(),
                    self::getTrainingUsefulnessRatingField(),
                    self::getApplicabilityRatingField(),
                    self::getSkillsImprovedField(),
                    self::getFutureTrainingSuggestionsField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Department dropdown
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDepartmentField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 1,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['department'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_department'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['engineering'],
                    'value' => 'engineering',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['sales'],
                    'value' => 'sales',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['marketing'],
                    'value' => 'marketing',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['hr'],
                    'value' => 'hr',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 5,
                    'label' => BackendStrings::getTemplateStrings()['operations'],
                    'value' => 'operations',
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
     * Training relevance radio
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTrainingRelevanceField(): array
    {
        return [[
            'id' => 12,
            'fieldIndex' => 2,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['training_relevance_role'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'fieldOptions' => [
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['highly_relevant'],
                    'value' => 'highly_relevant',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['somewhat_relevant'],
                    'value' => 'somewhat_relevant',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['slightly_relevant'],
                    'value' => 'slightly_relevant',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['not_relevant'],
                    'value' => 'not_relevant',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
        ]];
    }

    /**
     * Training usefulness rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTrainingUsefulnessRatingField(): array
    {
        return [[
            'id' => 13,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['training_usefulness'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(11),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Applicability rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getApplicabilityRatingField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['applicability_daily_work'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(16),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Skills improved checkbox
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSkillsImprovedField(): array
    {
        return [[
            'id' => 20,
            'fieldIndex' => 5,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['skills_improved'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'fieldOptions' => [
                [
                    'id' => 15,
                    'label' => BackendStrings::getTemplateStrings()['technical_skills'],
                    'value' => 'technical_skills',
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
                    'label' => BackendStrings::getTemplateStrings()['leadership'],
                    'value' => 'leadership',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 18,
                    'label' => BackendStrings::getTemplateStrings()['problem_solving'],
                    'value' => 'problem_solving',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 19,
                    'label' => BackendStrings::getTemplateStrings()['time_management'],
                    'value' => 'time_management',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Suggestions for future trainings textarea
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getFutureTrainingSuggestionsField(): array
    {
        return [[
            'id' => 21,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['suggestions_for_future_trainings'],
            'placeholder' => BackendStrings::getTemplateStrings()['suggestions_for_future_trainings_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
            'rows' => 3,
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
