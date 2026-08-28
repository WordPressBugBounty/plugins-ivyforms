<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class StudentHousingApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'student_housing_application_form',
            'name' => BackendStrings::getTemplateStrings()['student_housing_application_form'],
            'description' => BackendStrings::getTemplateStrings()['student_housing_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'tenant-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'student-housing-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['student_housing_application_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => [
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
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['student_id_upload'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'maxFileSizeMb' => 999,
                'saveUploadsTo' => 'ivyforms',
                'allowedFileExtensions' => [],
                'allowMultiple' => false,
                'maxUploadCount' => 0,
            ],
            [
                'id' => 7,
                'fieldIndex' => 5,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['proof_of_enrollment'],
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
                'id' => 8,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['emergency_contact'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_emergency_contact'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'rows' => 3,
            ],
                    ],
                    'settings' => [],
                ],
            ]
        );
    }
}
