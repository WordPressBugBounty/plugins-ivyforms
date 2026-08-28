<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class EventFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'event_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['event_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['event_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'event-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'event-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['event_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getEventTypeDropdownField(),
                    self::getExpectationsRadioField(),
                    self::getOverallSatisfactionRatingField(),
                    self::getAspectsCheckboxField(),
                    self::getAdditionalCommentsTextareaField(),
                    self::getEmailField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Event type dropdown field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEventTypeDropdownField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 1,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['event_type'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_event_type'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['conference'],
                    'value' => 'conference',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['workshop'],
                    'value' => 'workshop',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['webinar'],
                    'value' => 'webinar',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['networking_event'],
                    'value' => 'networking_event',
                    'isDefault' => false,
                    'position' => 4,
                ],
                [
                    'id' => 5,
                    'label' => BackendStrings::getTemplateStrings()['training'],
                    'value' => 'training',
                    'isDefault' => false,
                    'position' => 5,
                ],
                [
                    'id' => 6,
                    'label' => BackendStrings::getTemplateStrings()['other'],
                    'value' => 'other',
                    'isDefault' => false,
                    'position' => 6,
                ],
            ]
        ]];
    }

    /**
     * Expectations radio field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getExpectationsRadioField(): array
    {
        return [[
            'id' => 12,
            'fieldIndex' => 2,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['did_meet_expectations'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'fieldOptions' => [
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['exceeded_expectations'],
                    'value' => 'exceeded_expectations',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['met_expectations'],
                    'value' => 'met_expectations',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['partially_met_expectations'],
                    'value' => 'partially_met_expectations',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['did_not_meet_expectations'],
                    'value' => 'did_not_meet_expectations',
                    'isDefault' => false,
                    'position' => 4,
                ],
            ]
        ]];
    }

    /**
     * Overall satisfaction rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOverallSatisfactionRatingField(): array
    {
        return [[
            'id' => 13,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['overall_event_satisfaction'],
            'description' => '',
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(11),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Aspects checkbox field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAspectsCheckboxField(): array
    {
        return [[
            'id' => 19,
            'fieldIndex' => 4,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['aspects_stood_out'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'fieldOptions' => [
                [
                    'id' => 14,
                    'label' => BackendStrings::getTemplateStrings()['content_quality'],
                    'value' => 'content_quality',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 15,
                    'label' => BackendStrings::getTemplateStrings()['speakers_or_hosts'],
                    'value' => 'speakers_or_hosts',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 16,
                    'label' => BackendStrings::getTemplateStrings()['organization'],
                    'value' => 'organization',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 17,
                    'label' => BackendStrings::getTemplateStrings()['venue_or_platform'],
                    'value' => 'venue_or_platform',
                    'isDefault' => false,
                    'position' => 4,
                ],
                [
                    'id' => 18,
                    'label' => BackendStrings::getTemplateStrings()['networking_opportunities'],
                    'value' => 'networking_opportunities',
                    'isDefault' => false,
                    'position' => 5,
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
            'id' => 20,
            'fieldIndex' => 5,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['additional_comments'],
            'placeholder' => BackendStrings::getTemplateStrings()['share_thoughts_about_event'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 5,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * Email field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEmailField(): array
    {
        return [[
            'id' => 21,
            'fieldIndex' => 6,
            'type' => 'email',
            'label' => BackendStrings::getTemplateStrings()['email_address'],
            'placeholder' => 'name@example.com',
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
            'description' => BackendStrings::getTemplateStrings()['follow_up_email_description'],
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
