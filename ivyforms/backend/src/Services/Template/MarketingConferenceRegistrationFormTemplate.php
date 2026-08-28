<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class MarketingConferenceRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'marketing_conference_registration_form',
            'name' => BackendStrings::getTemplateStrings()['marketing_conference_registration_form'],
            'description' => BackendStrings::getTemplateStrings()['marketing_conference_registration_form_desc'],
            'category' => 'event-registration-forms',
            'subcategory' => 'conference-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'marketing-conference-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['marketing_conference_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getParticipantInformationFields(),
                    self::getConferencePreferencesFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get participant information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getParticipantInformationFields(): array
    {
        return [
            // Full Name
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['full_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_full_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 1,
                'validation' => [
                    'required' => true,
                ],
            ],
            // Email Address
            [
                'id' => 2,
                'fieldIndex' => 2,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => 'name@example.com',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
                'validation' => [
                    'required' => true,
                    'email' => true
                ],
            ],
        ];
    }

    /**
     * Get conference preferences fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getConferencePreferencesFields(): array
    {
        return [
            // Role/Position Dropdown
            [
                'id' => 7,
                'fieldIndex' => 3,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['role_position'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_role_position'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 3,
                        'label' => BackendStrings::getTemplateStrings()['marketer'],
                        'value' => 'marketer',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 4,
                        'label' => BackendStrings::getTemplateStrings()['manager'],
                        'value' => 'manager',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['ceo'],
                        'value' => 'ceo',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                ],
            ],
            // Workshops to Attend (Checkbox - multi-select alternative)
            [
                'id' => 11,
                'fieldIndex' => 4,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['workshops_to_attend'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['workshop_1'],
                        'value' => 'workshop_1',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['workshop_2'],
                        'value' => 'workshop_2',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['workshop_3'],
                        'value' => 'workshop_3',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                ],
            ],
            // Attendance Type (Radio)
            [
                'id' => 14,
                'fieldIndex' => 5,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['attendance_type'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['in_person'],
                        'value' => 'in_person',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['virtual'],
                        'value' => 'virtual',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get additional information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInformationFields(): array
    {
        return [
            // Questions or Comments (Paragraph)
            [
                'id' => 15,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['questions_comments'],
                'placeholder' => BackendStrings::getTemplateStrings()['write_questions_comments'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'rows' => 4,
            ],
        ];
    }
}
