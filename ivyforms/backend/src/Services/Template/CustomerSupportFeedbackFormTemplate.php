<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CustomerSupportFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'customer_support_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['customer_support_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['customer_support_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'customer-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'customer-support-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['customer_support_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getIssueResolvedField(),
                    self::getAgentHelpfulnessRatingField(),
                    self::getResponseTimeRatingField(),
                    self::getImprovementsCheckboxField(),
                    self::getAdditionalCommentsTextareaField(),
                    self::getFollowUpEmailField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Was your issue resolved? (radio, required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getIssueResolvedField(): array
    {
        return [[
            'id' => 4,
            'fieldIndex' => 1,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['was_issue_resolved'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'label' => BackendStrings::getTemplateStrings()['yes_fully'],
                    'value' => 'yes_fully',
                    'position' => 1,
                ],
                [
                    'label' => BackendStrings::getTemplateStrings()['partially'],
                    'value' => 'partially',
                    'position' => 2,
                ],
                [
                    'label' => BackendStrings::getComponentsStrings()['no'],
                    'value' => 'no',
                    'position' => 3,
                ],
            ]
        ]];
    }

    /**
     * Support agent helpfulness rating (required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAgentHelpfulnessRatingField(): array
    {
        return [[
            'id' => 5,
            'fieldIndex' => 2,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['support_agent_helpfulness'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(1),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Response time rating (required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getResponseTimeRatingField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['response_time'],
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
     * What could be improved? (checkbox, optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getImprovementsCheckboxField(): array
    {
        return [[
            'id' => 12,
            'fieldIndex' => 4,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['what_could_be_improved'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'fieldOptions' => [
                [
                    'id' => 7,
                    'label' => BackendStrings::getTemplateStrings()['faster_response'],
                    'value' => 'faster_response',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['clearer_communication'],
                    'value' => 'clearer_communication',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['better_problem_resolution'],
                    'value' => 'better_problem_resolution',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['friendliness'],
                    'value' => 'friendliness',
                    'isDefault' => false,
                    'position' => 4,
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['no_improvements_needed'],
                    'value' => 'no_improvements_needed',
                    'isDefault' => false,
                    'position' => 5,
                ],
            ]
        ]];
    }

    /**
     * Additional comments (textarea, optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalCommentsTextareaField(): array
    {
        return [[
            'id' => 13,
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
     * Follow-up email (email, optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getFollowUpEmailField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 6,
            'type' => 'email',
            'label' => BackendStrings::getTemplateStrings()['follow_up_email'],
            'placeholder' => BackendStrings::getTemplateStrings()['follow_up_email_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
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
