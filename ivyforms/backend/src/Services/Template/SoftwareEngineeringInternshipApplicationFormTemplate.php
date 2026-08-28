<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class SoftwareEngineeringInternshipApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'software_engineering_internship_application_form',
            'name' => BackendStrings::getTemplateStrings()['software_engineering_internship_application_form'],
            'description' => BackendStrings::getTemplateStrings()
                ['software_engineering_internship_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'internship-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'software-engineering-internship-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['software_engineering_internship_application_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => array_merge(
                        self::getApplicantInformationFields(),
                        self::getEducationAndTechnicalFields(),
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
     * Get education and technical fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEducationAndTechnicalFields(): array
    {
        return [
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['university'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_university'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
            [
                'id' => 7,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['field_of_study'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_field_of_study'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['computer_science'],
                        'value' => 'Computer Science',
                        'isDefault' => false,
                        'position' => 1,
                    ],                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['software_engineering'],
                        'value' => 'Software Engineering',
                        'isDefault' => false,
                        'position' => 2,
                    ],                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['information_technology'],
                        'value' => 'Information Technology',
                        'isDefault' => false,
                        'position' => 3,
                    ],                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['business'],
                        'value' => 'Business',
                        'isDefault' => false,
                        'position' => 4,
                    ],                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['marketing'],
                        'value' => 'Marketing',
                        'isDefault' => false,
                        'position' => 5,
                    ],                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'Other',
                        'isDefault' => false,
                        'position' => 6,
                    ]
                ],
            ],
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['current_year_of_study'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_current_year_of_study'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
            [
                'id' => 15,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['programming_languages_known'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_programming_languages_known'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'rows' => 3,
            ],
            [
                'id' => 16,
                'fieldIndex' => 8,
                'type' => 'website',
                'label' => BackendStrings::getTemplateStrings()['github_portfolio'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_github_portfolio'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
            ],
            [
                'id' => 17,
                'fieldIndex' => 9,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['coding_projects_description'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_coding_projects_description'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'rows' => 3,
            ],
            [
                'id' => 18,
                'fieldIndex' => 10,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['internship_goals'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_internship_goals'],
                'required' => true,
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
                'id' => 19,
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
                'id' => 20,
                'fieldIndex' => 12,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['transcript_upload'],
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
                'id' => 21,
                'fieldIndex' => 13,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['motivation_letter'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 13,
                'maxFileSizeMb' => 999,
                'saveUploadsTo' => 'ivyforms',
                'allowedFileExtensions' => [],
                'allowMultiple' => false,
                'maxUploadCount' => 0,
            ],
            [
                'id' => 22,
                'fieldIndex' => 14,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['availability_hours_per_week'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_availability_hours_per_week'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 14,
            ],
        ];
    }
}
