<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class MaintenanceWorkOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'maintenance_work_order_form',
            'name' => BackendStrings::getTemplateStrings()['maintenance_work_order_form'],
            'description' => BackendStrings::getTemplateStrings()['maintenance_work_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'work-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'maintenance-work-order-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['maintenance_work_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getRequesterInformationFields(),
                    self::getLocationFacilityFields(),
                    self::getMaintenanceRequestFields(),
                    self::getSubmissionApprovalFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get requester information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRequesterInformationFields(): array
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
     * Get location/facility details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getLocationFacilityFields(): array
    {
        return array_merge(
            self::getLocationFields(),
            self::getFacilityAddressFields()
        );
    }

    /**
     * Building / department / room fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getLocationFields(): array
    {
        return [
            // Building / Department / Room
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['building_department_room'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_building_department_room'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
        ];
    }

    /**
     * Facility address fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getFacilityAddressFields(): array
    {
        return [
            // Address Field - Parent
            [
                'id' => 7,
                'fieldIndex' => 5,
                'type' => 'address',
                'label' => BackendStrings::getTemplateStrings()['address_site_location'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'readonly' => false,
                'position' => 5,
                'required' => false,
            ],
            // Address Line 1 - Child of Address Field
            [
                'id' => 9,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_1'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                'required' => false,
                'parentId' => 5,
                'fieldIndex' => 5,
                'defaultValue' => '',
                'position' => 1,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'streetAddress',
                'settings' => json_encode(
                    [
                        'type' => 'streetAddress',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                        'requiredMessage' => '',
                        'visible' => true
                    ]
                ),
            ],
            // Address Line 2 - Child of Address Field
            [
                'id' => 11,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_2'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_2'],
                'required' => false,
                'parentId' => 5,
                'fieldIndex' => 5,
                'defaultValue' => '',
                'position' => 2,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'addressLine2',
                'settings' => json_encode(
                    [
                        'type' => 'addressLine2',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_2'],
                        'requiredMessage' => '',
                        'visible' => true
                    ]
                ),
            ],
            // City - Child of Address Field
            [
                'id' => 13,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['city'],
                'placeholder' => BackendStrings::getTemplateStrings()['city'],
                'required' => false,
                'parentId' => 5,
                'fieldIndex' => 5,
                'defaultValue' => '',
                'position' => 3,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'city',
                'settings' => json_encode(
                    [
                        'type' => 'city',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['city'],
                        'requiredMessage' => '',
                        'visible' => true
                    ]
                ),
            ],
            // State / Province - Child of Address Field
            [
                'id' => 15,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['state_province'],
                'placeholder' => BackendStrings::getTemplateStrings()['state'],
                'required' => false,
                'parentId' => 5,
                'fieldIndex' => 5,
                'defaultValue' => '',
                'position' => 4,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'state',
                'settings' => json_encode(
                    [
                        'type' => 'state',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['state'],
                        'requiredMessage' => '',
                        'visible' => true
                    ]
                ),
            ],
            // Zip / Postal Code - Child of Address Field
            [
                'id' => 17,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['zip_postal_code'],
                'placeholder' => BackendStrings::getTemplateStrings()['zip'],
                'required' => false,
                'parentId' => 5,
                'fieldIndex' => 5,
                'defaultValue' => '',
                'position' => 5,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'zip',
                'settings' => json_encode(
                    [
                        'type' => 'zip',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['zip'],
                        'requiredMessage' => '',
                        'visible' => true
                    ]
                ),
            ],
            // Country - Child of Address Field
            [
                'id' => 19,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['country'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_country'],
                'required' => false,
                'parentId' => 5,
                'fieldIndex' => 5,
                'defaultValue' => '',
                'position' => 6,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'country',
                'settings' => json_encode(
                    [
                        'type' => 'country',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['select_country'],
                        'requiredMessage' => '',
                        'visible' => true
                    ]
                ),
            ],
        ];
    }

    /**
     * Get maintenance request details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getMaintenanceRequestFields(): array
    {
        return [
            // Type of Maintenance / Issue
            [
                'id' => 25,
                'fieldIndex' => 6,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['type_of_maintenance_issue'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_type_of_maintenance'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['electrical'],
                        'value' => 'electrical',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 21,
                        'label' => BackendStrings::getTemplateStrings()['plumbing'],
                        'value' => 'plumbing',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 22,
                        'label' => BackendStrings::getTemplateStrings()['hvac'],
                        'value' => 'hvac',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 23,
                        'label' => BackendStrings::getTemplateStrings()['general_repair'],
                        'value' => 'general_repair',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 24,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ]
            ],
            // Description of Problem
            [
                'id' => 26,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['description_of_problem'],
                'placeholder' => BackendStrings::getTemplateStrings()['describe_the_maintenance_issue'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
            // Urgency / Priority
            [
                'id' => 30,
                'fieldIndex' => 8,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['urgency_priority'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_urgency_level'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'fieldOptions' => [
                    [
                        'id' => 27,
                        'label' => BackendStrings::getTemplateStrings()['low'],
                        'value' => 'low',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 28,
                        'label' => BackendStrings::getTemplateStrings()['medium'],
                        'value' => 'medium',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 29,
                        'label' => BackendStrings::getTemplateStrings()['high'],
                        'value' => 'high',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            [
                'id' => 31,
                'fieldIndex' => 9,
                'type' => 'time',
                'label' => BackendStrings::getTemplateStrings()['preferred_date_time'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_date_time'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'timeFieldType' => 'time-picker',
                'timeFormat' => 'ampm',
            ],
            // Additional Instructions / Notes
            [
                'id' => 32,
                'fieldIndex' => 10,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['additional_instructions_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['add_any_additional_notes'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
            ],
        ];
    }

    /**
     * Get submission/approval fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSubmissionApprovalFields(): array
    {
        return [
            // Supervisor Approval Required?
            [
                'id' => 35,
                'fieldIndex' => 11,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['supervisor_approval_required'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 11,
                'fieldOptions' => [
                    [
                        'id' => 33,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 34,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
            // Date Submitted
            [
                'id' => 36,
                'fieldIndex' => 12,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['date_submitted'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 12,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
        ];
    }
}
