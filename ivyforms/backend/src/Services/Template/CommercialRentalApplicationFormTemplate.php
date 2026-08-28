<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CommercialRentalApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'commercial_rental_application_form',
            'name' => BackendStrings::getTemplateStrings()['commercial_rental_application_form'],
            'description' => BackendStrings::getTemplateStrings()['commercial_rental_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'tenant-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'commercial-rental-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['commercial_rental_application_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => [
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['company_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_company_name'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 1,
            ],
            [
                'id' => 2,
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
                'id' => 3,
                'fieldIndex' => 3,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['business_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_business_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 4,
                        'label' => BackendStrings::getTemplateStrings()['retail_business'],
                        'value' => 'Retail',
                        'isDefault' => false,
                        'position' => 1,
                    ],                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['office_business'],
                        'value' => 'Office',
                        'isDefault' => false,
                        'position' => 2,
                    ],                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['restaurant_business'],
                        'value' => 'Restaurant',
                        'isDefault' => false,
                        'position' => 3,
                    ],                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['warehouse_business'],
                        'value' => 'Warehouse',
                        'isDefault' => false,
                        'position' => 4,
                    ],                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'Other',
                        'isDefault' => false,
                        'position' => 5,
                    ]
                ],
            ],
            [
                'id' => 9,
                'fieldIndex' => 4,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['financial_statements'],
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
                'id' => 10,
                'fieldIndex' => 5,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['business_registration_document'],
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
                'id' => 11,
                'fieldIndex' => 6,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['proposal_intent_letter'],
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
                    ],
                    'settings' => [],
                ],
            ]
        );
    }
}
