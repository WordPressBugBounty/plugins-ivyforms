<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CorporateEmploymentApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'corporate_employment_application_form',
            'name' => BackendStrings::getTemplateStrings()['corporate_employment_application_form'],
            'description' => BackendStrings::getTemplateStrings()['corporate_employment_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'employment-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'corporate-employment-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['corporate_employment_application_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => array_merge(
                        self::getApplicantInformationFields(),
                        self::getEmploymentDetailsFields()
                    ),
                    'settings' => [],
                ],
            ]
        );
    }

    /**
     * Get applicant information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getApplicantInformationFields(): array
    {
        return [
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
            [
                'id' => 4,
                'fieldIndex' => 2,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_email_address'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
            ],
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
     * Get employment details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEmploymentDetailsFields(): array
    {
        return [
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['department_applied_for'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_department_applied_for'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['engineering'],
                        'value' => 'Engineering',
                        'isDefault' => false,
                        'position' => 1,
                    ],                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['marketing'],
                        'value' => 'Marketing',
                        'isDefault' => false,
                        'position' => 2,
                    ],                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['sales'],
                        'value' => 'Sales',
                        'isDefault' => false,
                        'position' => 3,
                    ],                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['operations'],
                        'value' => 'Operations',
                        'isDefault' => false,
                        'position' => 4,
                    ],                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['finance'],
                        'value' => 'Finance',
                        'isDefault' => false,
                        'position' => 5,
                    ]
                ],
            ],
            [
                'id' => 12,
                'fieldIndex' => 5,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['previous_employer'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_previous_employer'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
            ],
            [
                'id' => 13,
                'fieldIndex' => 6,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['employment_history_document'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'maxFileSizeMb' => 999,
                'saveUploadsTo' => 'ivyforms',
                'allowedFileExtensions' => [],
                'allowMultiple' => false,
                'maxUploadCount' => 0,
            ],
            [
                'id' => 14,
                'fieldIndex' => 7,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['id_document_upload'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'maxFileSizeMb' => 999,
                'saveUploadsTo' => 'ivyforms',
                'allowedFileExtensions' => [],
                'allowMultiple' => false,
                'maxUploadCount' => 0,
            ],
            [
                'id' => 15,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['additional_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_additional_notes'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'rows' => 3,
            ],
        ];
    }
}
