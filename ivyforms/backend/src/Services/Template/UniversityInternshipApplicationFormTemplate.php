<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class UniversityInternshipApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'university_internship_application_form',
            'name' => BackendStrings::getTemplateStrings()['university_internship_application_form'],
            'description' => BackendStrings::getTemplateStrings()['university_internship_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'internship-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'university-internship-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['university_internship_application_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => array_merge(
                        self::getApplicantInformationFields(),
                        self::getEducationFields(),
                        self::getDocumentsFields()
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
     * Get education fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEducationFields(): array
    {
        return [
            [
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['university'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_university'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
            ],
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['field_of_study'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_field_of_study'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['computer_science'],
                        'value' => 'Computer Science',
                        'isDefault' => false,
                        'position' => 1,
                    ],                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['software_engineering'],
                        'value' => 'Software Engineering',
                        'isDefault' => false,
                        'position' => 2,
                    ],                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['information_technology'],
                        'value' => 'Information Technology',
                        'isDefault' => false,
                        'position' => 3,
                    ],                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['business'],
                        'value' => 'Business',
                        'isDefault' => false,
                        'position' => 4,
                    ],                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['marketing'],
                        'value' => 'Marketing',
                        'isDefault' => false,
                        'position' => 5,
                    ],                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'Other',
                        'isDefault' => false,
                        'position' => 6,
                    ]
                ],
            ],
        ];
    }

    /**
     * Get documents fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDocumentsFields(): array
    {
        return [
            [
                'id' => 13,
                'fieldIndex' => 5,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['cv_upload'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'maxFileSizeMb' => 999,
                'saveUploadsTo' => 'ivyforms',
                'allowedFileExtensions' => [],
                'allowMultiple' => false,
                'maxUploadCount' => 0,
            ],
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['transcript_upload'],
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
                'id' => 15,
                'fieldIndex' => 7,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['motivation_letter'],
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
        ];
    }
}
