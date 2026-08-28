<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ProjectCompletionFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'project_completion_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['project_completion_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['project_completion_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'client-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'project-completion-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['project_completion_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getProjectIdNumberField(),
                    self::getProjectTypeDropdownField(),
                    self::getProjectOutcomeRatingField(),
                    self::getTimelinesDeliveryRatingField(),
                    self::getExpectationsMetRadioField(),
                    self::getWhatWentWellTextareaField(),
                    self::getImprovementSuggestionsTextareaField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Project ID / Reference number field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProjectIdNumberField(): array
    {
        return [[
            'id' => 1,
            'fieldIndex' => 1,
            'type' => 'number',
            'label' => BackendStrings::getTemplateStrings()['project_id_reference'],
            'placeholder' => BackendStrings::getTemplateStrings()['project_id_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * Project type dropdown field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProjectTypeDropdownField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 2,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['project_type'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_project_type'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 2,
            'fieldOptions' => [
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['design'],
                    'value' => 'design',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['development'],
                    'value' => 'development',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['marketing'],
                    'value' => 'marketing',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 5,
                    'label' => BackendStrings::getTemplateStrings()['consulting'],
                    'value' => 'consulting',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 6,
                    'label' => BackendStrings::getTemplateStrings()['other'],
                    'value' => 'other',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Project outcome rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProjectOutcomeRatingField(): array
    {
        return [[
            'id' => 8,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['project_outcome'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(6),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Timeliness & delivery rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTimelinesDeliveryRatingField(): array
    {
        return [[
            'id' => 9,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['timeliness_delivery'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(11),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Did the project meet expectations radio field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getExpectationsMetRadioField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 5,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['meet_expectations'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'fieldOptions' => [
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['exceeded_expectations'],
                    'value' => 'exceeded_expectations',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['met_expectations'],
                    'value' => 'met_expectations',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 12,
                    'label' => BackendStrings::getTemplateStrings()['partially_met_expectations'],
                    'value' => 'partially_met_expectations',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['did_not_meet_expectations'],
                    'value' => 'did_not_meet_expectations',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
        ]];
    }

    /**
     * What went well textarea field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWhatWentWellTextareaField(): array
    {
        return [[
            'id' => 15,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['what_went_well'],
            'placeholder' => BackendStrings::getTemplateStrings()['what_went_well_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * What could be improved textarea field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getImprovementSuggestionsTextareaField(): array
    {
        return [[
            'id' => 16,
            'fieldIndex' => 7,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['what_could_be_improved'],
            'placeholder' => BackendStrings::getTemplateStrings()['what_could_be_improved_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 7,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * Standard rating options (nice, good, very good, awesome, amazing)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getStandardRatingOptions(int $startId): array
    {
        return [
            [
                'id' => $startId,
                'label' => BackendStrings::getNewFormStrings()['nice'],
                'value' => '1',
                'isDefault' => false,
                'position' => 1
            ],
            [
                'id' => $startId + 1,
                'label' => BackendStrings::getNewFormStrings()['good'],
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
