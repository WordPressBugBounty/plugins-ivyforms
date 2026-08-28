<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class SpaAppointmentBookingFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'spa_appointment_booking_form',
            'name' => BackendStrings::getTemplateStrings()['spa_appointment_booking_form'],
            'description' => BackendStrings::getTemplateStrings()['spa_appointment_booking_form_desc'],
            'category' => 'booking-forms',
            'subcategory' => 'appointment-booking-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'spa-appointment-booking-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['spa_appointment_booking_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getClientInformationFields(),
                    self::getAppointmentDetailsFields(),
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
     * Get appointment details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAppointmentDetailsFields(): array
    {
        return array_merge(
            self::getServiceTypeField(),
            self::getTherapistPreferenceField(),
            self::getPreferredDateField(),
            self::getPreferredTimeField()
        );
    }

    /**
     * Get service type field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getServiceTypeField(): array
    {
        return [
            [
                'id' => 11,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['service_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_service_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['massage'],
                        'value' => 'massage',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['facial'],
                        'value' => 'facial',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['body_treatment'],
                        'value' => 'body_treatment',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['aromatherapy'],
                        'value' => 'aromatherapy',
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
     * Get therapist preference field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTherapistPreferenceField(): array
    {
        return [
            [
                'id' => 15,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['preferred_therapist'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_therapist'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['therapist_a'],
                        'value' => 'therapist_a',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['therapist_b'],
                        'value' => 'therapist_b',
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
        return [
            [
                'id' => 18,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['special_instructions_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['specify_preferences_health_conditions'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'required' => false,
                'rows' => 4,
            ],
        ];
    }
}
