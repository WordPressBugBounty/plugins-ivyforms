<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ClientRelationshipCommunicationFeedbackTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'client_relationship_communication_feedback',
            'name' => BackendStrings::getTemplateStrings()['client_relationship_communication_feedback'],
            'description' => BackendStrings::getTemplateStrings()['client_relationship_communication_feedback_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'client-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'client-relationship-communication-feedback.svg',
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
                'name' => BackendStrings::getTemplateStrings()['client_relationship_communication_feedback'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getContactRoleRadioField(),
                    self::getCommunicationClarityRatingField(),
                    self::getAvailabilityResponsivenessRatingField(),
                    self::getPreferredChannelsCheckboxField(),
                    self::getCommunicationImprovementTextareaField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Primary contact role radio field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContactRoleRadioField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 1,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['primary_contact_role'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['founder_ceo'],
                    'value' => 'founder_ceo',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['manager'],
                    'value' => 'manager',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['team_lead'],
                    'value' => 'team_lead',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['individual_contributor'],
                    'value' => 'individual_contributor',
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
     * Communication clarity rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCommunicationClarityRatingField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 2,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['communication_clarity'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(6),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Availability & responsiveness rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAvailabilityResponsivenessRatingField(): array
    {
        return [[
            'id' => 8,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['availability_responsiveness'],
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
     * Preferred communication channels checkbox field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPreferredChannelsCheckboxField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 4,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['preferred_communication_channels'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'fieldOptions' => [
                [
                    'id' => 9,
                    'label' => BackendStrings::getNewFormStrings()['email'],
                    'value' => 'email',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['phone'],
                    'value' => 'phone',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['video_calls'],
                    'value' => 'video_calls',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 12,
                    'label' => BackendStrings::getTemplateStrings()['chat_messaging_tools'],
                    'value' => 'chat_messaging_tools',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['project_management_tools'],
                    'value' => 'project_management_tools',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Communication improvement suggestions textarea field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCommunicationImprovementTextareaField(): array
    {
        return [[
            'id' => 15,
            'fieldIndex' => 5,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['communication_improvement_suggestions'],
            'required' => false,
            'defaultValue' => '',
            'placeholder' => BackendStrings::getTemplateStrings()['communication_improvement_suggestions_placeholder'],
            'position' => 5,
            'labelPosition' => 'default',
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
