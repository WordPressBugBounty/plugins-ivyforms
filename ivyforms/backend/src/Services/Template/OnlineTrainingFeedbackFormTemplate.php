<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class OnlineTrainingFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'online_training_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['online_training_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['online_training_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'training-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'online-training-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['online_training_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getDeviceUsedField(),
                    self::getPlatformUsabilityField(),
                    self::getOverallExperienceRatingField(),
                    self::getMaterialsQualityRatingField(),
                    self::getTechnicalIssuesField(),
                    self::getTechnicalCommentsField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Device used dropdown
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDeviceUsedField(): array
    {
        return [[
            'id' => 5,
            'fieldIndex' => 1,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['device_used'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_device_used'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['desktop'],
                    'value' => 'desktop',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['laptop'],
                    'value' => 'laptop',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['tablet'],
                    'value' => 'tablet',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['mobile_phone'],
                    'value' => 'mobile_phone',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
        ]];
    }

    /**
     * Platform usability radio
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPlatformUsabilityField(): array
    {
        return [[
            'id' => 10,
            'fieldIndex' => 2,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['platform_usability'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'fieldOptions' => [
                [
                    'id' => 6,
                    'label' => BackendStrings::getTemplateStrings()['very_easy_to_use'],
                    'value' => 'very_easy_to_use',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 7,
                    'label' => BackendStrings::getTemplateStrings()['easy_to_use'],
                    'value' => 'easy_to_use',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['neutral'],
                    'value' => 'neutral',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getNewFormStrings()['difficult_to_use'],
                    'value' => 'difficult_to_use',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
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
            'id' => 11,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['overall_online_training_experience'],
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
     * Training materials quality rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getMaterialsQualityRatingField(): array
    {
        return [[
            'id' => 12,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['training_materials_quality'],
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
     * Technical issues checkbox
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalIssuesField(): array
    {
        return [[
            'id' => 18,
            'fieldIndex' => 5,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['technical_issues_experienced'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'fieldOptions' => [
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['audio_issues'],
                    'value' => 'audio_issues',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 14,
                    'label' => BackendStrings::getTemplateStrings()['video_issues'],
                    'value' => 'video_issues',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 15,
                    'label' => BackendStrings::getTemplateStrings()['login_problems'],
                    'value' => 'login_problems',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 16,
                    'label' => BackendStrings::getTemplateStrings()['slow_loading'],
                    'value' => 'slow_loading',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 17,
                    'label' => BackendStrings::getTemplateStrings()['no_issues'],
                    'value' => 'no_issues',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Technical comments textarea
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalCommentsField(): array
    {
        return [[
            'id' => 19,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['technical_usability_comments'],
            'placeholder' => BackendStrings::getTemplateStrings()['technical_usability_comments_placeholder'],
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
