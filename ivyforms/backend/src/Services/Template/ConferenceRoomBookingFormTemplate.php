<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ConferenceRoomBookingFormTemplate
{
/**
 * Conference Room Booking Form Template
 */
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'conference_room_booking_form',
            'name' => BackendStrings::getTemplateStrings()['conference_room_booking_form'],
            'description' => BackendStrings::getTemplateStrings()[
                'conference_room_booking_form_description'
            ],
            'category' => 'booking-forms',
            'subcategory' => 'appointment-booking-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'conference-room-booking-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['conference_room_booking_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getContactInformationFields(),
                    self::getBookingDetailsFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Contact information fields (name, email, phone)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContactInformationFields(): array
    {
        return [
            // Full Name - Parent
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
                'placeholder' => BackendStrings::getTemplateStrings()['email_placeholder'],
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
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
            ],
        ];
    }

    /**
     * Booking details fields (department, date, time, attendees)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getBookingDetailsFields(): array
    {
        return [
            // Department/Organization
            [
                'id' => 10,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['department_organization'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_department_organization'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['hr'],
                        'value' => 'hr',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['marketing'],
                        'value' => 'marketing',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['it'],
                        'value' => 'it',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 4
                    ],
                ]
            ],
            // Booking Date
            [
                'id' => 11,
                'fieldIndex' => 5,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['booking_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_booking_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Start Time
            [
                'id' => 12,
                'fieldIndex' => 6,
                'type' => 'time',
                'label' => BackendStrings::getTemplateStrings()['start_time'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_start_time'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
            // End Time
            [
                'id' => 13,
                'fieldIndex' => 7,
                'type' => 'time',
                'label' => BackendStrings::getTemplateStrings()['end_time'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_end_time'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
            // Number of Attendees
            [
                'id' => 14,
                'fieldIndex' => 8,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['number_of_attendees'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_number_of_attendees'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'minValue' => 1,
                'maxValue' => 100,
            ],
        ];
    }

    /**
     * Additional information fields (equipment, special requests)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInformationFields(): array
    {
        return [
            // Equipment Needed
            [
                'id' => 18,
                'fieldIndex' => 9,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['equipment_needed'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'fieldOptions' => [
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['projector'],
                        'value' => 'projector',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['microphone'],
                        'value' => 'microphone',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['whiteboard'],
                        'value' => 'whiteboard',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Special Requests / Notes
            [
                'id' => 19,
                'fieldIndex' => 10,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['special_requests_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'special_requests_notes_placeholder'
                ],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
            ],
        ];
    }
}
