<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class RepairWorkOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'repair_work_order_form',
            'name' => BackendStrings::getTemplateStrings()['repair_work_order_form'],
            'description' => BackendStrings::getTemplateStrings()['repair_work_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'work-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'repair-work-order-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['repair_work_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getContactInformationFields(),
                    self::getRepairDetailsFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get contact information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getContactInformationFields(): array
    {
        return [
            // Employee Name
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['employee_name'],
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
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_name'],
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
                'placeholder' => 'name@company.com',
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
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['contact_number'],
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
     * Get repair details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRepairDetailsFields(): array
    {
        return [
            // Location
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['location'],
                'placeholder' => BackendStrings::getTemplateStrings()['exact_location_of_issue'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
            // Equipment/Item to Repair
            [
                'id' => 7,
                'fieldIndex' => 5,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['equipment_item_to_repair'],
                'placeholder' => BackendStrings::getTemplateStrings()['device_or_item_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
            ],
            // Repair Type Dropdown
            [
                'id' => 12,
                'fieldIndex' => 6,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['repair_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_repair_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['mechanical'],
                        'value' => 'mechanical',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['electrical'],
                        'value' => 'electrical',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['software'],
                        'value' => 'software',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                ],
            ],
            // Severity Dropdown
            [
                'id' => 16,
                'fieldIndex' => 7,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['severity'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_severity'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'fieldOptions' => [
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['minor'],
                        'value' => 'minor',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['major'],
                        'value' => 'major',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['critical'],
                        'value' => 'critical',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                ],
            ],
            // Preferred Repair Date
            [
                'id' => 17,
                'fieldIndex' => 8,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['preferred_repair_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_preferred_repair_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
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
            // Steps Already Taken
            [
                'id' => 18,
                'fieldIndex' => 9,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['steps_already_taken'],
                'placeholder' => BackendStrings::getTemplateStrings()['describe_troubleshooting_done'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'rows' => 4,
            ],
            // Notify Department Head
            [
                'id' => 21,
                'fieldIndex' => 10,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['notify_department_head'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'fieldOptions' => [
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                ],
            ],
        ];
    }
}
