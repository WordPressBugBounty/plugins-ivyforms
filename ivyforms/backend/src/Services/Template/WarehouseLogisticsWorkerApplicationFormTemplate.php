<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class WarehouseLogisticsWorkerApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'warehouse_logistics_worker_application_form',
            'name' => BackendStrings::getTemplateStrings()['warehouse_logistics_worker_application_form'],
            'description' => BackendStrings::getTemplateStrings()['warehouse_logistics_worker_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'employment-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'warehouse-logistics-worker-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['warehouse_logistics_worker_application_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => array_merge(
                        self::getApplicantInformationFields(),
                        self::getWorkExperienceFields(),
                        self::getEmploymentDetailsFields(),
                        self::getDocumentsAndContactFields()
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
     * Get work experience fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWorkExperienceFields(): array
    {
        return [
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['work_experience_in_logistics'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_work_experience_in_logistics'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'rows' => 3,
            ],
            [
                'id' => 7,
                'fieldIndex' => 5,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['forklift_license'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'Yes',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'No',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                ],
            ],
            [
                'id' => 10,
                'fieldIndex' => 6,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['safety_certifications'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => false,
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
                'id' => 11,
                'fieldIndex' => 7,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['physical_capability_confirmation'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'fieldOptions' => [
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['physical_capability_confirmation_option'],
                        'value' => 'confirmed',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                ],
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
                'id' => 13,
                'fieldIndex' => 8,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['shift_availability'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_shift_availability'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'fieldOptions' => [
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['morning_shift'],
                        'value' => 'Morning',
                        'isDefault' => false,
                        'position' => 1,
                    ],                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['afternoon_shift'],
                        'value' => 'Afternoon',
                        'isDefault' => false,
                        'position' => 2,
                    ],                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['night_shift'],
                        'value' => 'Night',
                        'isDefault' => false,
                        'position' => 3,
                    ],                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['flexible_shift'],
                        'value' => 'Flexible',
                        'isDefault' => false,
                        'position' => 4,
                    ]
                ],
            ],
            [
                'id' => 18,
                'fieldIndex' => 9,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['heavy_lifting_experience'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 9,
                'fieldOptions' => [
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'Yes',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'No',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                ],
            ],
            [
                'id' => 21,
                'fieldIndex' => 10,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['previous_employers'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_previous_employers'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'rows' => 3,
            ],
        ];
    }

    /**
     * Get documents and contact fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDocumentsAndContactFields(): array
    {
        return [
            [
                'id' => 22,
                'fieldIndex' => 11,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['cv_upload'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 11,
                'maxFileSizeMb' => 999,
                'saveUploadsTo' => 'ivyforms',
                'allowedFileExtensions' => [],
                'allowMultiple' => false,
                'maxUploadCount' => 0,
            ],
            [
                'id' => 23,
                'fieldIndex' => 12,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['id_document_upload'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 12,
                'maxFileSizeMb' => 999,
                'saveUploadsTo' => 'ivyforms',
                'allowedFileExtensions' => [],
                'allowMultiple' => false,
                'maxUploadCount' => 0,
            ],
            [
                'id' => 24,
                'fieldIndex' => 13,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['emergency_contact'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_emergency_contact'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 13,
                'rows' => 3,
            ],
        ];
    }
}
