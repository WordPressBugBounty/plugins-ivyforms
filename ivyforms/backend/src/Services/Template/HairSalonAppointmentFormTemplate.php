<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class HairSalonAppointmentFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'hair_salon_appointment_form',
            'name' => BackendStrings::getTemplateStrings()['hair_salon_appointment_form'],
            'description' => BackendStrings::getTemplateStrings()['hair_salon_appointment_form_desc'],
            'category' => 'booking-forms',
            'subcategory' => 'appointment-booking-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'hair-salon-appointment-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['hair_salon_appointment_form'],
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
            self::getStylistPreferenceField(),
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
                'id' => 13,
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
                        'label' => BackendStrings::getTemplateStrings()['haircut'],
                        'value' => 'haircut',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['hair_coloring'],
                        'value' => 'hair_coloring',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['hair_styling'],
                        'value' => 'hair_styling',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['hair_treatment'],
                        'value' => 'hair_treatment',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['highlights'],
                        'value' => 'highlights',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['perm'],
                        'value' => 'perm',
                        'isDefault' => false,
                        'position' => 6
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 7
                    ],
                ]
            ],
        ];
    }

    /**
     * Get stylist preference field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getStylistPreferenceField(): array
    {
        return [
            [
                'id' => 18,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['stylist_preference'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_stylist_preference'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['no_preference'],
                        'value' => 'no_preference',
                        'isDefault' => true,
                        'position' => 1
                    ],
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['stylist_1'],
                        'value' => 'stylist_1',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['stylist_2'],
                        'value' => 'stylist_2',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['stylist_3'],
                        'value' => 'stylist_3',
                        'isDefault' => false,
                        'position' => 4
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
                'id' => 19,
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
                'id' => 20,
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
            self::getSpecialRequestsField(),
            self::getFirstTimeClientField()
        );
    }

    /**
     * Get special requests field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSpecialRequestsField(): array
    {
        return [
            [
                'id' => 21,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['special_requests'],
                'placeholder' => BackendStrings::getTemplateStrings()['any_special_requests_or_notes'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'required' => false,
                'rows' => 4,
            ],
        ];
    }

    /**
     * Get first time client field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getFirstTimeClientField(): array
    {
        return [
            [
                'id' => 24,
                'fieldIndex' => 9,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['first_time_client'],
                'parentId' => null,
                'position' => 9,
                'required' => true,
                'defaultValue' => '',
                'placeholder' => '',
                'fieldOptions' => [
                    [
                        'id' => 22,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 23,
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
