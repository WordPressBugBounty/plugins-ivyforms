<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class DigitalMarketingInternshipApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'digital_marketing_internship_application_form',
            'name' => BackendStrings::getTemplateStrings()['digital_marketing_internship_application_form'],
            'description' => BackendStrings::getTemplateStrings()['digital_marketing_internship_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'internship-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'digital-marketing-internship-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['digital_marketing_internship_application_form'],
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
     * Get education and experience fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEducationAndExperienceFields(): array
    {
        return [
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['university_education'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_university_education'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
            [
                'id' => 7,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['marketing_knowledge_level'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_marketing_knowledge_level'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['marketing_knowledge_beginner'],
                        'value' => 'Beginner',
                        'isDefault' => false,
                        'position' => 1,
                    ],                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['marketing_knowledge_intermediate'],
                        'value' => 'Intermediate',
                        'isDefault' => false,
                        'position' => 2,
                    ],                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['marketing_knowledge_advanced'],
                        'value' => 'Advanced',
                        'isDefault' => false,
                        'position' => 3,
                    ]
                ],
            ],
            [
                'id' => 11,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['social_media_platforms_managed'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_social_media_platforms_managed'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'rows' => 3,
            ],
            [
                'id' => 12,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['content_creation_experience'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_content_creation_experience'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'rows' => 3,
            ],
            [
                'id' => 13,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['tools_knowledge_meta_ads_google_ads_canva_etc'],
                'placeholder' => BackendStrings::getTemplateStrings()
                    ['enter_tools_knowledge_meta_ads_google_ads_canva_etc'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
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
                'id' => 14,
                'fieldIndex' => 9,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['portfolio_content_samples'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'maxFileSizeMb' => 999,
                'saveUploadsTo' => 'ivyforms',
                'allowedFileExtensions' => [],
                'allowMultiple' => false,
                'maxUploadCount' => 0,
            ],
            [
                'id' => 15,
                'fieldIndex' => 10,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['cv_upload'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'maxFileSizeMb' => 999,
                'saveUploadsTo' => 'ivyforms',
                'allowedFileExtensions' => [],
                'allowMultiple' => false,
                'maxUploadCount' => 0,
            ],
            [
                'id' => 16,
                'fieldIndex' => 11,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['motivation_letter'],
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
                'id' => 17,
                'fieldIndex' => 12,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['availability'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_availability'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 12,
            ],
        ];
    }
}
