<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class MedicalConferenceRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'medical_conference_registration_form',
            'name' => BackendStrings::getTemplateStrings()['medical_conference_registration_form'],
            'description' => BackendStrings::getTemplateStrings()['medical_conference_registration_form_desc'],
            'category' => 'event-registration-forms',
            'subcategory' => 'conference-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'medical-conference-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['medical_conference_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getParticipantInformationFields(),
                    self::getMedicalPreferencesFields(),
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
            // Phone Number
            [
                'id' => 3,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => '+1 (_) _-_',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Get medical preferences fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getMedicalPreferencesFields(): array
    {
        return [
            // Specialization Dropdown
            [
                'id' => 9,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['specialization'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_specialization'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 4,
                        'label' => BackendStrings::getTemplateStrings()['cardiology'],
                        'value' => 'cardiology',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['neurology'],
                        'value' => 'neurology',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['pediatrics'],
                        'value' => 'pediatrics',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['oncology'],
                        'value' => 'oncology',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 5,
                    ],
                ],
            ],
            // Seminars to Attend (Checkbox)
            [
                'id' => 13,
                'fieldIndex' => 5,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['seminars_to_attend'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['seminar_1'],
                        'value' => 'seminar_1',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['seminar_2'],
                        'value' => 'seminar_2',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['seminar_3'],
                        'value' => 'seminar_3',
                        'isDefault' => false,
                        'position' => 3,
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
            // Additional Notes
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['additional_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['write_additional_notes'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'rows' => 4,
            ],
        ];
    }
}
