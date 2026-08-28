<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class WorkshopFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'workshop_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['workshop_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['workshop_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'event-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'workshop-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['workshop_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getWorkshopDateField(),
                    self::getFormatEffectivenessRadioField(),
                    self::getContentRelevanceRatingField(),
                    self::getInstructorEffectivenessRatingField(),
                    self::getWhatWorkedCheckboxField(),
                    self::getSuggestionsTextareaField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Date: Workshop date
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWorkshopDateField(): array
    {
        return [[
            'id' => 1,
            'fieldIndex' => 1,
            'type' => 'date',
            'label' => BackendStrings::getTemplateStrings()['workshop_date'],
            'placeholder' => BackendStrings::getNewFormStrings()['enter_date'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
            'dateFieldType' => 'picker',
            'dateFormat' => 'MM/DD/YYYY',
        ]];
    }

    /**
     * Radio: Workshop format effectiveness
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getFormatEffectivenessRadioField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 2,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['workshop_format_effectiveness'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'fieldOptions' => [
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['workshop_format_very_effective'],
                    'value' => 'very_effective',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['workshop_format_effective'],
                    'value' => 'effective',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['workshop_format_neutral'],
                    'value' => 'neutral',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 5,
                    'label' => BackendStrings::getTemplateStrings()['workshop_format_ineffective'],
                    'value' => 'ineffective',
                    'isDefault' => false,
                    'position' => 4,
                ],
            ]
        ]];
    }

    /**
     * Rating: Content relevance
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContentRelevanceRatingField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['content_relevance'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(5),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Rating: Instructor effectiveness
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getInstructorEffectivenessRatingField(): array
    {
        return [[
            'id' => 8,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['instructor_effectiveness'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(10),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Checkbox: What worked well?
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWhatWorkedCheckboxField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 5,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['what_worked_well'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'fieldOptions' => [
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['practical_exercises'],
                    'value' => 'practical_exercises',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['instructor_explanations'],
                    'value' => 'instructor_explanations',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['materials_provided'],
                    'value' => 'materials_provided',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 12,
                    'label' => BackendStrings::getTemplateStrings()['pace_of_workshop'],
                    'value' => 'pace_of_workshop',
                    'isDefault' => false,
                    'position' => 4,
                ],
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['group_interaction'],
                    'value' => 'group_interaction',
                    'isDefault' => false,
                    'position' => 5,
                ],
            ]
        ]];
    }

    /**
     * Paragraph: Suggestions for improvement
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSuggestionsTextareaField(): array
    {
        return [[
            'id' => 15,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['suggestions_for_improvement'],
            'placeholder' => BackendStrings::getTemplateStrings()['share_suggestions_for_workshop'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
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
