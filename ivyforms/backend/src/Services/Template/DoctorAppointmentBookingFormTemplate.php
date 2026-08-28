<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class DoctorAppointmentBookingFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'doctor_appointment_booking_form',
            'name' => BackendStrings::getTemplateStrings()['doctor_appointment_booking_form'],
            'description' => BackendStrings::getTemplateStrings()['doctor_appointment_booking_form_desc'],
            'category' => 'booking-forms',
            'subcategory' => 'appointment-booking-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'doctor-appointment-booking-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['doctor_appointment_booking_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getPatientInformationFields(),
                    self::getAppointmentDetailsFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get patient information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPatientInformationFields(): array
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
            self::getDoctorSpecialistField(),
            self::getPreferredAppointmentDateField(),
            self::getPreferredTimeSlotField()
        );
    }

    /**
     * Get doctor/specialist field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDoctorSpecialistField(): array
    {
        return [
            [
                'id' => 11,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['doctor_specialist'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_doctor_or_specialist'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['general_practitioner'],
                        'value' => 'general_practitioner',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['dermatologist'],
                        'value' => 'dermatologist',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['pediatrician'],
                        'value' => 'pediatrician',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['cardiologist'],
                        'value' => 'cardiologist',
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
     * Get preferred appointment date field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPreferredAppointmentDateField(): array
    {
        return [
            [
                'id' => 12,
                'fieldIndex' => 5,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['preferred_appointment_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
        ];
    }

    /**
     * Get preferred time slot field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPreferredTimeSlotField(): array
    {
        return [
            [
                'id' => 13,
                'fieldIndex' => 6,
                'type' => 'time',
                'label' => BackendStrings::getTemplateStrings()['preferred_time_slot'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_time'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'timeFieldType' => 'time-picker',
                'timeFormat' => 'ampm',
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
        return array_merge(
            self::getReasonForVisitField(),
            self::getNeedTelemedicineField(),
            self::getInsuranceProviderField()
        );
    }

    /**
     * Get reason for visit field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getReasonForVisitField(): array
    {
        return [
            [
                'id' => 14,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['reason_for_visit'],
                'placeholder' => BackendStrings::getTemplateStrings()['describe_symptoms_or_reason'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'required' => true,
                'rows' => 4,
            ],
        ];
    }

    /**
     * Get need telemedicine field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getNeedTelemedicineField(): array
    {
        return [
            [
                'id' => 17,
                'fieldIndex' => 8,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['need_telemedicine'],
                'parentId' => null,
                'position' => 8,
                'required' => false,
                'defaultValue' => '',
                'placeholder' => '',
                'fieldOptions' => [
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
        ];
    }

    /**
     * Get insurance provider field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getInsuranceProviderField(): array
    {
        return [
            [
                'id' => 21,
                'fieldIndex' => 9,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['insurance_provider'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_insurance_provider'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'fieldOptions' => [
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['provider_1'],
                        'value' => 'provider_1',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['provider_2'],
                        'value' => 'provider_2',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
        ];
    }
}
