<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class AnnualLeaveRequestFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'annual_leave_request_form',
            'name' => BackendStrings::getTemplateStrings()['annual_leave_request_form'],
            'description' => BackendStrings::getTemplateStrings()['annual_leave_request_form_desc'],
            'category' => 'leave-request',
            'subcategory' => 'leave-request-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'annual-leave-request-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['annual_leave_request_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => self::getFormFields(),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get all form fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getFormFields(): array
    {
        return array_merge(
            [
                self::getNameField(),
                self::getFirstNameField(),
                self::getLastNameField(),
            ],
            self::getEmployeeDetailsFields(),
            self::getLeaveDetailsFields(),
            self::getApprovalFields()
        );
    }

    /**
     * Get name field (parent)
     *
     * @return array<string, mixed>
     */
    private static function getNameField(): array
    {
        return [
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
        ];
    }

    /**
     * Get first name field (child of name field)
     *
     * @return array<string, mixed>
     */
    private static function getFirstNameField(): array
    {
        return [
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
        ];
    }

    /**
     * Get last name field (child of name field)
     *
     * @return array<string, mixed>
     */
    private static function getLastNameField(): array
    {
        return [
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
        ];
    }

    /**
     * Get employee details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEmployeeDetailsFields(): array
    {
        return [
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
            // Employee ID
            [
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['employee_id'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_employee_id'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
            ],
            // Department
            [
                'id' => 13,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['department'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_department'],
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
                        'label' => BackendStrings::getTemplateStrings()['it'],
                        'value' => 'it',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['marketing'],
                        'value' => 'marketing',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['finance'],
                        'value' => 'finance',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['sales'],
                        'value' => 'sales',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['operations'],
                        'value' => 'operations',
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
            // Job Title/Position
            [
                'id' => 14,
                'fieldIndex' => 5,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['job_title_position'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_job_title'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
            ],
        ];
    }

    /**
     * Get leave details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getLeaveDetailsFields(): array
    {
        return [
            // Leave Start Date
            [
                'id' => 15,
                'fieldIndex' => 6,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['leave_start_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_start_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Leave End Date
            [
                'id' => 16,
                'fieldIndex' => 7,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['leave_end_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_end_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Total Number of Days
            [
                'id' => 17,
                'fieldIndex' => 8,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['total_number_of_days'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_total_number_of_days'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'minValue' => 1,
                'maxValue' => 365,
            ],
            // Annual Leave Balance
            [
                'id' => 18,
                'fieldIndex' => 9,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['annual_leave_balance'],
                'placeholder' => BackendStrings::getTemplateStrings()['remaining_leave_days'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'minValue' => 0,
                'maxValue' => 365,
            ],
            // Reason for Leave
            [
                'id' => 19,
                'fieldIndex' => 10,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['reason_for_leave'],
                'placeholder' => BackendStrings::getTemplateStrings()['briefly_describe_reason'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
            ],
        ];
    }

    /**
     * Get approval fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getApprovalFields(): array
    {
        return [
            // Contact During Leave
            [
                'id' => 22,
                'fieldIndex' => 11,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['contact_during_leave'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 11,
                'fieldOptions' => [
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 21,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
            // Emergency Contact
            [
                'id' => 23,
                'fieldIndex' => 12,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['emergency_contact'],
                'placeholder' => BackendStrings::getTemplateStrings()['emergency_contact_phone'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 12,
            ],
            // Supervisor Name
            [
                'id' => 24,
                'fieldIndex' => 13,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['supervisor_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_supervisor_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 13,
            ],
            // Backup / Coverage Plan
            [
                'id' => 25,
                'fieldIndex' => 14,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['backup_coverage_plan'],
                'placeholder' => BackendStrings::getTemplateStrings()['describe_coverage_arrangements'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 14,
            ],
        ];
    }
}
