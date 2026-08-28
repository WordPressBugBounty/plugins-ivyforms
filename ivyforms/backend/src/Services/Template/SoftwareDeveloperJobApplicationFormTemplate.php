<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class SoftwareDeveloperJobApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'software_developer_job_application_form',
            'name' => BackendStrings::getTemplateStrings()['software_developer_job_application_form'],
            'description' => BackendStrings::getTemplateStrings()['software_developer_job_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'job-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'software-developer-job-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['software_developer_job_application_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => array_merge(
                        self::getApplicantInformationFields(),
                        self::getTechnicalExperienceFields(),
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
     * Get technical experience fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalExperienceFields(): array
    {
        return [
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['years_of_experience'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_years_of_experience'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
            [
                'id' => 7,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['preferred_role'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_role'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['frontend'],
                        'value' => 'Frontend',
                        'isDefault' => false,
                        'position' => 1,
                    ],                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['backend'],
                        'value' => 'Backend',
                        'isDefault' => false,
                        'position' => 2,
                    ],                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['full_stack'],
                        'value' => 'Full-stack',
                        'isDefault' => false,
                        'position' => 3,
                    ],                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['mobile'],
                        'value' => 'Mobile',
                        'isDefault' => false,
                        'position' => 4,
                    ]
                ],
            ],
            [
                'id' => 12,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['primary_tech_stack'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_primary_tech_stack'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'rows' => 3,
            ],
            [
                'id' => 13,
                'fieldIndex' => 7,
                'type' => 'website',
                'label' => BackendStrings::getTemplateStrings()['github_profile'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_github_profile'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
            [
                'id' => 14,
                'fieldIndex' => 8,
                'type' => 'website',
                'label' => BackendStrings::getTemplateStrings()['portfolio_project_links'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_portfolio_project_links'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
            ],
            [
                'id' => 15,
                'fieldIndex' => 9,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['biggest_project_description'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_biggest_project_description'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'rows' => 3,
            ],
            [
                'id' => 16,
                'fieldIndex' => 10,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['problem_solving_experience'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_problem_solving_experience'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
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
                'id' => 17,
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
                'id' => 18,
                'fieldIndex' => 12,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['code_samples_upload'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => false,
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
                'id' => 19,
                'fieldIndex' => 13,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['availability_start_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_availability_start_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 13,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ]
        ];
    }
}
