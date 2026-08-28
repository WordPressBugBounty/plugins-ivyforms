<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class OfficeSpaceRentalApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'office_space_rental_application_form',
            'name' => BackendStrings::getTemplateStrings()['office_space_rental_application_form'],
            'description' => BackendStrings::getTemplateStrings()['office_space_rental_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'tenant-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'office-space-rental-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['office_space_rental_application_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => array_merge(
                        self::getCompanyInformationFields(),
                        self::getSpaceRequirementsFields(),
                        self::getDocumentsAndReferencesFields()
                    ),
                    'settings' => [],
                ],
            ]
        );
    }

    /**
     * Get company contact fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCompanyInformationFields(): array
    {
        return [
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
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => '+1 (_) _-_',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
            ],
        ];
    }

    /**
     * Get office space requirement fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSpaceRequirementsFields(): array
    {
        return [
            [
                'id' => 4,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['business_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_business_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['retail_business'],
                        'value' => 'Retail',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['office_business'],
                        'value' => 'Office',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['restaurant_business'],
                        'value' => 'Restaurant',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['warehouse_business'],
                        'value' => 'Warehouse',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'Other',
                        'isDefault' => false,
                        'position' => 5,
                    ],
                ],
            ],
            [
                'id' => 10,
                'fieldIndex' => 5,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['number_of_employees'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_number_of_employees'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
            ],
            [
                'id' => 11,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['office_space_requirements'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_office_space_requirements'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'rows' => 4,
            ],
            [
                'id' => 12,
                'fieldIndex' => 7,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['desired_lease_duration'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_desired_lease_duration'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'fieldOptions' => [
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['lease_duration_6_months'],
                        'value' => '6 months',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['lease_duration_1_year'],
                        'value' => '1 year',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['lease_duration_2_years'],
                        'value' => '2 years',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['lease_duration_3_plus_years'],
                        'value' => '3+ years',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                ],
            ],
            [
                'id' => 17,
                'fieldIndex' => 8,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['monthly_budget_range'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_monthly_budget_range'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
            ],
        ];
    }

    /**
     * Get document upload and reference fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDocumentsAndReferencesFields(): array
    {
        return [
            [
                'id' => 18,
                'fieldIndex' => 9,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['financial_statements'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => true,
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
                'id' => 19,
                'fieldIndex' => 10,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['business_registration_document'],
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
                'id' => 20,
                'fieldIndex' => 11,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['previous_office_rental_experience'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_previous_office_rental_experience'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 11,
                'rows' => 3,
            ],
            [
                'id' => 21,
                'fieldIndex' => 12,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['references'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_references'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 12,
                'rows' => 3,
            ],
            [
                'id' => 22,
                'fieldIndex' => 13,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['id_legal_representative_document'],
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
        ];
    }
}
