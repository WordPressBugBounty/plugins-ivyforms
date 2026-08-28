<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ConsultationAppointmentFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'consultation_appointment_form',
            'name' => BackendStrings::getTemplateStrings()['consultation_appointment_form'],
            'description' => BackendStrings::getTemplateStrings()['consultation_appointment_form_desc'],
            'category' => 'booking-forms',
            'subcategory' => 'appointment-booking-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'consultation-appointment-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['consultation_appointment_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getClientInformationFields(),
                    self::getConsultationDetailsFields(),
                    self::getAdditionalPreferencesFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get client information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getClientInformationFields(): array
    {
        return [
            // Name Field - Parent
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['full_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'required' => true,
                'readonly' => false,
                'position' => 1,
            ],
            // First Name - Child of Name Field
            [
                'id' => 2,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['first_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_first_name'],
                'required' => true,
                'parentId' => 1,
                'fieldIndex' => 1,
                'defaultValue' => '',
                'position' => 1,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            // Last Name - Child of Name Field
            [
                'id' => 3,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['last_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_last_name'],
                'required' => true,
                'parentId' => 1,
                'fieldIndex' => 1,
                'defaultValue' => '',
                'position' => 2,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
            ],
            // Email Address
            [
                'id' => 4,
                'fieldIndex' => 2,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => 'name@example.com',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
            ],
            // Phone Number
            [
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => '+1 (_) _-_',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Get consultation details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getConsultationDetailsFields(): array
    {
        return array_merge(
            self::getConsultationTypeField(),
            self::getAdvisorPreferenceField(),
            self::getPreferredDateField(),
            self::getPreferredTimeField()
        );
    }

    /**
     * Get consultation type field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getConsultationTypeField(): array
    {
        return [
            [
                'id' => 11,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['consultation_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_consultation_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['business'],
                        'value' => 'business',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['legal'],
                        'value' => 'legal',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['financial'],
                        'value' => 'financial',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['personal'],
                        'value' => 'personal',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ]
            ],
        ];
    }

    /**
     * Get advisor preference field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdvisorPreferenceField(): array
    {
        return [
            [
                'id' => 15,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['preferred_advisor'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_advisor'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['advisor_a'],
                        'value' => 'advisor_a',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['advisor_b'],
                        'value' => 'advisor_b',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['no_preference'],
                        'value' => 'no_preference',
                        'isDefault' => true,
                        'position' => 3
                    ],
                ]
            ],
        ];
    }

    /**
     * Get preferred date field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPreferredDateField(): array
    {
        return [
            [
                'id' => 16,
                'fieldIndex' => 6,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['preferred_appointment_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
        ];
    }

    /**
     * Get preferred time field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPreferredTimeField(): array
    {
        return [
            [
                'id' => 17,
                'fieldIndex' => 7,
                'type' => 'time',
                'label' => BackendStrings::getTemplateStrings()['preferred_time_slot'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_time'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'timeFieldType' => 'time-picker',
                'timeFormat' => 'ampm',
            ],
        ];
    }

    /**
     * Get additional preferences fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalPreferencesFields(): array
    {
        return array_merge(
            self::getTopicsQuestionsField(),
            self::getVirtualMeetingField()
        );
    }

    /**
     * Get topics/questions field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTopicsQuestionsField(): array
    {
        return [
            [
                'id' => 18,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['topics_questions_to_discuss'],
                'placeholder' => BackendStrings::getTemplateStrings()['list_topics_questions_consultation'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'required' => false,
                'rows' => 4,
            ],
        ];
    }

    /**
     * Get virtual meeting field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getVirtualMeetingField(): array
    {
        return [
            [
                'id' => 21,
                'fieldIndex' => 9,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['virtual_meeting'],
                'parentId' => null,
                'position' => 9,
                'required' => false,
                'defaultValue' => '',
                'placeholder' => '',
                'fieldOptions' => [
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
        ];
    }
}
