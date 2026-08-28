<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CourseContentFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'course_content_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['course_content_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['course_content_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'training-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'course-content-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['course_content_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getCourseTopicField(),
                    self::getContentDifficultyField(),
                    self::getContentQualityRatingField(),
                    self::getContentRelevanceRatingField(),
                    self::getLearningSupportField(),
                    self::getContentSuggestionsField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Course topic dropdown
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCourseTopicField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 1,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['course_topic'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_course_topic'],
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
                    'label' => BackendStrings::getTemplateStrings()['management_leadership'],
                    'value' => 'management_leadership',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['compliance_safety'],
                    'value' => 'compliance_safety',
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
     * Content difficulty radio
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContentDifficultyField(): array
    {
        return [[
            'id' => 10,
            'fieldIndex' => 2,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['content_difficulty_level'],
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
     * Content quality rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContentQualityRatingField(): array
    {
        return [[
            'id' => 11,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['content_quality'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(9),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Content relevance rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContentRelevanceRatingField(): array
    {
        return [[
            'id' => 12,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['content_relevance'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(14),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Learning support checkbox
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getLearningSupportField(): array
    {
        return [[
            'id' => 18,
            'fieldIndex' => 5,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['what_helped_your_learning'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'fieldOptions' => [
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['clear_explanations'],
                    'value' => 'clear_explanations',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 14,
                    'label' => BackendStrings::getTemplateStrings()['real_world_examples'],
                    'value' => 'real_world_examples',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 15,
                    'label' => BackendStrings::getTemplateStrings()['exercises_assignments'],
                    'value' => 'exercises_assignments',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 16,
                    'label' => BackendStrings::getTemplateStrings()['visual_materials'],
                    'value' => 'visual_materials',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 17,
                    'label' => BackendStrings::getTemplateStrings()['case_studies'],
                    'value' => 'case_studies',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Suggestions textarea
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContentSuggestionsField(): array
    {
        return [[
            'id' => 19,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['suggestions_for_content_improvement'],
            'placeholder' => BackendStrings::getTemplateStrings()['suggestions_for_content_improvement_placeholder'],
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
