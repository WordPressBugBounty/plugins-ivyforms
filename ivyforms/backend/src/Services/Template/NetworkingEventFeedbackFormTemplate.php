<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class NetworkingEventFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'networking_event_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['networking_event_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['networking_event_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'event-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'networking-event-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['networking_event_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getConnectionsRadioField(),
                    self::getNetworkingValueRatingField(),
                    self::getWhatHelpedCheckboxField(),
                    self::getImprovementTextareaField(),
                    self::getNameField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Radio: Did you make valuable connections?
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getConnectionsRadioField(): array
    {
        return [[
            'id' => 5,
            'fieldIndex' => 1,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['did_make_valuable_connections'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['yes_many'],
                    'value' => 'yes_many',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['yes_a_few'],
                    'value' => 'yes_few',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['not_really'],
                    'value' => 'not_really',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['no_connections'],
                    'value' => 'no',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
        ]];
    }

    /**
     * Rating: Networking value
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getNetworkingValueRatingField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 2,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['networking_value'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(5),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Checkbox: What helped with networking?
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWhatHelpedCheckboxField(): array
    {
        return [[
            'id' => 12,
            'fieldIndex' => 3,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['what_helped_with_networking'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'fieldOptions' => [
                [
                    'id' => 7,
                    'label' => BackendStrings::getTemplateStrings()['event_format'],
                    'value' => 'event_format',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['attendee_quality'],
                    'value' => 'attendee_quality',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['structured_activities'],
                    'value' => 'structured_activities',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['open_networking_time'],
                    'value' => 'open_networking_time',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['venue_or_atmosphere'],
                    'value' => 'venue_atmosphere',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Paragraph: How could networking be improved?
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getImprovementTextareaField(): array
    {
        return [[
            'id' => 13,
            'fieldIndex' => 4,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['how_could_networking_be_improved'],
            'placeholder' => BackendStrings::getTemplateStrings()['share_suggestions_to_improve_networking'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 4,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * Name field (optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getNameField(): array
    {
        return [
            // Parent Name field
            [
                'id' => 14,
                'fieldIndex' => 5,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['full_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'required' => false,
                'readonly' => false,
                'position' => 5,
            ],
            // First name - child
            [
                'id' => 15,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['first_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_first_name'],
                'required' => false,
                'parentId' => 5,
                'fieldIndex' => 5,
                'defaultValue' => '',
                'position' => 1,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            // Last name - child
            [
                'id' => 16,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['last_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_last_name'],
                'required' => false,
                'parentId' => 5,
                'fieldIndex' => 5,
                'defaultValue' => '',
                'position' => 2,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
            ],
        ];
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
