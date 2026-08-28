<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class SickLeaveRequestFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'sick_leave_request_form',
            'name' => BackendStrings::getTemplateStrings()['sick_leave_request_form'],
            'description' => BackendStrings::getTemplateStrings()['sick_leave_request_form_desc'],
            'category' => 'leave-request',
            'subcategory' => 'leave-request-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'sick-leave-request-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['sick_leave_request_form'],
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
        return [
            self::getNameField(),
            self::getFirstNameField(),
            self::getLastNameField(),
            self::getEmailField(),
            self::getDepartmentField(),
            self::getSickLeaveStartDateField(),
            self::getSickLeaveEndDateField(),
            self::getDoctorsNoteField(),
            self::getNotifySupervisorField(),
        ];
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
     * Get email field
     *
     * @return array<string, mixed>
     */
    private static function getEmailField(): array
    {
        return [
            'id' => 4,
            'fieldIndex' => 2,
            'type' => 'email',
            'label' => BackendStrings::getTemplateStrings()['email_address'],
            'placeholder' => 'name@example.com',
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 2,
        ];
    }

    /**
     * Get department field
     *
     * @return array<string, mixed>
     */
    private static function getDepartmentField(): array
    {
        return [
            'id' => 5,
            'fieldIndex' => 3,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['department'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_department'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 3,
            'fieldOptions' => self::getDepartmentOptions(1),
        ];
    }

    /**
     * Get department field options
     *
     * @param int $startId Starting ID for the options
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDepartmentOptions(int $startId): array
    {
        return [
            [
                'id' => $startId,
                'label' => BackendStrings::getTemplateStrings()['hr'],
                'value' => 'hr',
                'isDefault' => false,
                'position' => 1
            ],
            [
                'id' => $startId + 1,
                'label' => BackendStrings::getTemplateStrings()['it'],
                'value' => 'it',
                'isDefault' => false,
                'position' => 2
            ],
            [
                'id' => $startId + 2,
                'label' => BackendStrings::getTemplateStrings()['sales'],
                'value' => 'sales',
                'isDefault' => false,
                'position' => 3
            ],
            [
                'id' => $startId + 3,
                'label' => BackendStrings::getTemplateStrings()['marketing'],
                'value' => 'marketing',
                'isDefault' => false,
                'position' => 4
            ],
            [
                'id' => $startId + 4,
                'label' => BackendStrings::getTemplateStrings()['other'],
                'value' => 'other',
                'isDefault' => false,
                'position' => 5
            ],
        ];
    }

    /**
     * Get sick leave start date field
     *
     * @return array<string, mixed>
     */
    private static function getSickLeaveStartDateField(): array
    {
        return [
            'id' => 11,
            'fieldIndex' => 4,
            'type' => 'date',
            'label' => BackendStrings::getTemplateStrings()['sick_leave_start_date'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_start_date'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 5,
            'dateFieldType' => 'picker',
            'dateFormat' => 'MM/DD/YYYY',
        ];
    }

    /**
     * Get sick leave end date field
     *
     * @return array<string, mixed>
     */
    private static function getSickLeaveEndDateField(): array
    {
        return [
            'id' => 12,
            'fieldIndex' => 5,
            'type' => 'date',
            'label' => BackendStrings::getTemplateStrings()['sick_leave_end_date'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_end_date'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
            'dateFieldType' => 'picker',
            'dateFormat' => 'MM/DD/YYYY',
        ];
    }

    /**
     * Get doctor's note field
     *
     * @return array<string, mixed>
     */
    private static function getDoctorsNoteField(): array
    {
        return [
            'id' => 13,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['doctors_note'],
            'placeholder' => BackendStrings::getTemplateStrings()['attach_doctors_note_if_required'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 7,
        ];
    }

    /**
     * Get notify supervisor field
     *
     * @return array<string, mixed>
     */
    private static function getNotifySupervisorField(): array
    {
        return [
            'id' => 14,
            'fieldIndex' => 7,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['notify_supervisor'],
            'placeholder' => '',
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 8,
            'fieldOptions' => self::getNotifySupervisorOptions(1),
        ];
    }

    /**
     * Get notify supervisor field options
     *
     * @param int $startId Starting ID for the options
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getNotifySupervisorOptions(int $startId): array
    {
        return [
            [
                'id' => $startId,
                'label' => BackendStrings::getTemplateStrings()['yes'],
                'value' => 'yes',
                'isDefault' => false,
                'position' => 1
            ],
            [
                'id' => $startId + 1,
                'label' => BackendStrings::getTemplateStrings()['no'],
                'value' => 'no',
                'isDefault' => false,
                'position' => 2
            ],
        ];
    }
}
