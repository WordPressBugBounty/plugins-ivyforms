<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class EntryLevelJobApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'entry_level_job_application_form',
            'name' => BackendStrings::getTemplateStrings()['entry_level_job_application_form'],
            'description' => BackendStrings::getTemplateStrings()['entry_level_job_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'job-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'entry-level-job-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['entry_level_job_application_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => array_merge(
                        self::getApplicantInformationFields(),
                        self::getEducationAndExperienceFields(),
                        self::getDocumentsAndAvailabilityFields()
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
                'label' => BackendStrings::getTemplateStrings()['name'],
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
        ];
    }

    /**
     * Get education and experience fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEducationAndExperienceFields(): array
    {
        return [
            [
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['education_level'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_education_level'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['high_school'],
                        'value' => 'High School',
                        'isDefault' => false,
                        'position' => 1,
                    ],                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['associate_degree'],
                        'value' => 'Associate Degree',
                        'isDefault' => false,
                        'position' => 2,
                    ],                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['bachelor_degree'],
                        'value' => 'Bachelor\'s Degree',
                        'isDefault' => false,
                        'position' => 3,
                    ],                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['master_degree'],
                        'value' => 'Master\'s Degree',
                        'isDefault' => false,
                        'position' => 4,
                    ]
                ],
            ],
            [
                'id' => 10,
                'fieldIndex' => 4,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['school_university'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_school_university'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
            [
                'id' => 11,
                'fieldIndex' => 5,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['internship_experience'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_internship_experience'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'rows' => 3,
            ],
        ];
    }

    /**
     * Get documents and availability fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDocumentsAndAvailabilityFields(): array
    {
        return [
            [
                'id' => 12,
                'fieldIndex' => 6,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['cv_upload'],
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
                'id' => 13,
                'fieldIndex' => 7,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['portfolio_work_samples'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => false,
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
                'id' => 14,
                'fieldIndex' => 8,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['availability_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_availability_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
        ];
    }
}
