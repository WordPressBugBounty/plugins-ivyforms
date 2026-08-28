<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class PrivateDiningReservationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'private_dining_reservation_form',
            'name' => BackendStrings::getTemplateStrings()['private_dining_reservation_form'],
            'description' => BackendStrings::getTemplateStrings()['private_dining_reservation_form_desc'],
            'category' => 'booking-forms',
            'subcategory' => 'reservation-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'private-dining-reservation-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['private_dining_reservation_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getGuestInformationFields(),
                    self::getReservationDetailsFields(),
                    self::getAdditionalFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Guest information fields (name, email, phone)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getGuestInformationFields(): array
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
                'label' => BackendStrings::getTemplateStrings()['first_name'],
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
                'label' => BackendStrings::getTemplateStrings()['last_name'],
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
                'placeholder' => BackendStrings::getTemplateStrings()['name_placeholder'],
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
                'placeholder' => BackendStrings::getTemplateStrings()['phone_placeholder'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Reservation detail fields (guests, room preference, date, time)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getReservationDetailsFields(): array
    {
        return [
            // Number of Guests
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['number_of_guests'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_total_guests'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'minValue' => 1,
            ],
            // Room Preference
            [
                'id' => 10,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['room_preference'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_room_preference'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['private_room_a'],
                        'value' => 'private_room_a',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['private_room_b'],
                        'value' => 'private_room_b',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['no_preference'],
                        'value' => 'no_preference',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Reservation Date
            [
                'id' => 11,
                'fieldIndex' => 6,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['reservation_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_reservation_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Reservation Time
            [
                'id' => 12,
                'fieldIndex' => 7,
                'type' => 'time',
                'label' => BackendStrings::getTemplateStrings()['reservation_time'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_reservation_time'],
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
     * Additional information fields (special instructions)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalFields(): array
    {
        return [
            // Special Instructions
            [
                'id' => 13,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['special_instructions'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'private_dining_special_instructions_placeholder'
                ],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'required' => false,
                'rows' => 4,
            ],
        ];
    }
}
