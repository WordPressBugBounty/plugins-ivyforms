<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class FreelancerApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'freelancer_application_form',
            'name' => BackendStrings::getTemplateStrings()['freelancer_application_form'],
            'description' => BackendStrings::getTemplateStrings()['freelancer_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'freelancer-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'freelancer-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['freelancer_application_form'],
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
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['skill_category'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_skill_category'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['design'],
                        'value' => 'Design',
                        'isDefault' => false,
                        'position' => 1,
                    ],                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['development'],
                        'value' => 'Development',
                        'isDefault' => false,
                        'position' => 2,
                    ],                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['marketing'],
                        'value' => 'Marketing',
                        'isDefault' => false,
                        'position' => 3,
                    ],                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['writing'],
                        'value' => 'Writing',
                        'isDefault' => false,
                        'position' => 4,
                    ],                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['consulting'],
                        'value' => 'Consulting',
                        'isDefault' => false,
                        'position' => 5,
                    ]
                ],
            ],
            [
                'id' => 11,
                'fieldIndex' => 4,
                'type' => 'website',
                'label' => BackendStrings::getTemplateStrings()['portfolio_links'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_portfolio_links'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
            [
                'id' => 12,
                'fieldIndex' => 5,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['cv_upload'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => false,
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
                'id' => 13,
                'fieldIndex' => 6,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['sample_work_upload'],
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
                'id' => 14,
                'fieldIndex' => 7,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['hourly_rate'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_hourly_rate'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
                    ],
                    'settings' => [],
                ],
            ]
        );
    }
}
