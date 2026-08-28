<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class WebsiteExperienceFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'website_experience_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['website_experience_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['website_experience_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'customer-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'website-experience-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['website_experience_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getWebsiteUrlField(),
                    self::getDeviceUsedField(),
                    self::getNavigationEaseRatingField(),
                    self::getVisualDesignRatingField(),
                    self::getIssuesEncounteredCheckboxField(),
                    self::getSuggestionsCommentsTextareaField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Website / URL - Page URL (required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWebsiteUrlField(): array
    {
        return [[
            'id' => 1,
            'fieldIndex' => 1,
            'type' => 'website',
            'label' => BackendStrings::getTemplateStrings()['website_page_url'],
            'placeholder' => BackendStrings::getTemplateStrings()['current_page_url'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
        ]];
    }

    /**
     * Device used (dropdown, required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDeviceUsedField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 2,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['device_used'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => BackendStrings::getTemplateStrings()['select_device_used'],
            'position' => 2,
            'fieldOptions' => [
                [
                    'label' => BackendStrings::getTemplateStrings()['desktop'],
                    'value' => 'desktop',
                    'position' => 1,
                ],
                [
                    'label' => BackendStrings::getTemplateStrings()['laptop'],
                    'value' => 'laptop',
                    'position' => 2,
                ],
                [
                    'label' => BackendStrings::getTemplateStrings()['tablet'],
                    'value' => 'tablet',
                    'position' => 3,
                ],
                [
                    'label' => BackendStrings::getTemplateStrings()['mobile_phone'],
                    'value' => 'mobile_phone',
                    'position' => 4,
                ],
            ]
        ]];
    }

    /**
     * Ease of navigation rating (required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getNavigationEaseRatingField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['ease_of_navigation'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(1),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Visual design rating (required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getVisualDesignRatingField(): array
    {
        return [[
            'id' => 8,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['visual_design'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(6),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Issues encountered (checkbox, optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getIssuesEncounteredCheckboxField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 5,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['issues_encountered'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'fieldOptions' => [
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['slow_loading'],
                    'value' => 'slow_loading',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['broken_links'],
                    'value' => 'broken_links',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['confusing_navigation'],
                    'value' => 'confusing_navigation',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 12,
                    'label' => BackendStrings::getTemplateStrings()['layout_issues'],
                    'value' => 'layout_issues',
                    'isDefault' => false,
                    'position' => 4,
                ],
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['no_issues'],
                    'value' => 'no_issues',
                    'isDefault' => false,
                    'position' => 5,
                ],
            ]
        ]];
    }

    /**
     * Suggestions or comments (textarea, optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSuggestionsCommentsTextareaField(): array
    {
        return [[
            'id' => 15,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['suggestions_or_comments'],
            'placeholder' => BackendStrings::getTemplateStrings()['suggestions_comments_placeholder'],
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
