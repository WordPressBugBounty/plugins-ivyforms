<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class WebinarFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'webinar_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['webinar_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['webinar_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'event-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'webinar-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['webinar_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getWebinarPlatformDropdownField(),
                    self::getAudioVideoQualityRadioField(),
                    self::getOverallExperienceRatingField(),
                    self::getContentValueRatingField(),
                    self::getTechnicalIssuesCheckboxField(),
                    self::getAdditionalFeedbackTextareaField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Dropdown: Webinar platform
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWebinarPlatformDropdownField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 1,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['webinar_platform'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_webinar_platform'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1, 'label' => BackendStrings::getTemplateStrings()['platform_zoom'],
                    'value' => 'zoom',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 2, 'label' => BackendStrings::getTemplateStrings()['platform_google_meet'],
                    'value' => 'google_meet',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['platform_ms_teams'],
                    'value' => 'ms_teams',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['platform_webex'],
                    'value' => 'webex',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 5,
                    'label' => BackendStrings::getTemplateStrings()['platform_other'],
                    'value' => 'other',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Rating: Audio & video quality
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAudioVideoQualityRadioField(): array
    {
        return [[
            'id' => 11,
            'fieldIndex' => 2,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['audio_video_quality'],
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
                    'label' => BackendStrings::getTemplateStrings()['audio_quality_poor'],
                    'value' => 'poor',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['audio_quality_fair'],
                    'value' => 'fair',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['audio_quality_good'],
                    'value' => 'good',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['audio_quality_excellent'],
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
     * Rating: Overall webinar experience
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOverallExperienceRatingField(): array
    {
        return [[
            'id' => 12,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['overall_webinar_experience'],
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
     * Rating: Content value
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContentValueRatingField(): array
    {
        return [[
            'id' => 13,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['content_value'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(15),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Checkbox: Technical issues experienced
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalIssuesCheckboxField(): array
    {
        return [[
            'id' => 19,
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
                    'id' => 14,
                    'label' => BackendStrings::getTemplateStrings()['audio_issues'],
                    'value' => 'audio_issues',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 15,
                    'label' => BackendStrings::getTemplateStrings()['video_issues'],
                    'value' => 'video_issues',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 16,
                    'label' => BackendStrings::getTemplateStrings()['connectivity_problems'],
                    'value' => 'connectivity_problems',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 17,
                    'label' => BackendStrings::getTemplateStrings()['screen_sharing_issues'],
                    'value' => 'screen_sharing_issues',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 18,
                    'label' => BackendStrings::getTemplateStrings()['no_issues'],
                    'value' => 'no_issues',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Paragraph: Additional feedback
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalFeedbackTextareaField(): array
    {
        return [[
            'id' => 20,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['additional_feedback'],
            'placeholder' => BackendStrings::getTemplateStrings()['share_additional_feedback'],
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
                'id' => $startId, 'label' => BackendStrings::getNewFormStrings()['good'],
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
