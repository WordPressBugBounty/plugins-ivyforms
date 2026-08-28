<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class MaternityLeaveRequestFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'maternity_leave_request_form',
            'name' => BackendStrings::getTemplateStrings()['maternity_leave_request_form'],
            'description' => BackendStrings::getTemplateStrings()['maternity_leave_request_form_desc'],
            'category' => 'leave-request',
            'subcategory' => 'leave-request-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'maternity-leave-request-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['maternity_leave_request_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getEmployeeFields(),
                    self::getLeaveFields(),
                    self::getNotificationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Employee information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEmployeeFields(): array
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
            // First Name - Child
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
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            // Last Name - Child
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
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
            ],
            // Email
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
            // Department select
            [
                'id' => 10,
                'fieldIndex' => 3,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['department'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_department'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['hr'],
                        'value' => 'hr',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['it'],
                        'value' => 'it',
                        'isDefault' => false,
                        'position' => 2],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['sales'],
                        'value' => 'sales',
                        'isDefault' => false,
                        'position' => 3],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['marketing'],
                        'value' => 'marketing',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 5],
                ]
            ],
        ];
    }

    /**
     * Leave-specific fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getLeaveFields(): array
    {
        return [
            // Leave Type (Maternity only)
            [
                'id' => 12,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['type_of_leave'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_type_of_leave'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['maternity_leave'],
                        'value' => 'maternity_leave',
                        'isDefault' => false,
                        'position' => 1
                    ],
                ]
            ],
            // Start Date of Leave
            [
                'id' => 13,
                'fieldIndex' => 5,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['start_date_of_leave'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_start_date_of_leave'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Expected Return Date
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['expected_return_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_expected_return_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Additional Notes
            [
                'id' => 15,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['additional_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['additional_notes_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'rows' => 4,
            ],
        ];
    }

    /**
     * Notification / approval fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getNotificationFields(): array
    {
        return [
            // Notify my supervisor? checkbox
            [
                'id' => 18,
                'fieldIndex' => 8,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['notify_my_supervisor'],
                'parentId' => null,
                'position' => 8,
                'required' => false,
                'defaultValue' => '',
                'fieldOptions' => [
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['notify_my_supervisor_yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['notify_my_supervisor_no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
        ];
    }
}
