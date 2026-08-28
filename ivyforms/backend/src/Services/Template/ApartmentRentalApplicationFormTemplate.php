<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ApartmentRentalApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'apartment_rental_application_form',
            'name' => BackendStrings::getTemplateStrings()['apartment_rental_application_form'],
            'description' => BackendStrings::getTemplateStrings()['apartment_rental_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'tenant-application-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'apartment-rental-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['apartment_rental_application_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => array_merge(
                        self::getApplicantInformationFields(),
                        self::getCurrentAddressFields(),
                        self::getEmploymentAndDocumentsFields(),
                        self::getRentalDetailsFields()
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
            ],
        ];
    }

    /**
     * Get current address fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCurrentAddressFields(): array
    {
        return [
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'address',
                'label' => BackendStrings::getTemplateStrings()['current_address'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'readonly' => false,
                'position' => 4,
                'required' => true,
            ],
            [
                'id' => 7,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_1'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                'required' => true,
                'parentId' => 4,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 4,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'streetAddress',
                'settings' => json_encode(
                    [
                        'type' => 'streetAddress',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                        'requiredMessage' => '',
                        'visible' => true,
                    ]
                ),
            ],
            [
                'id' => 8,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_2'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_2'],
                'required' => false,
                'parentId' => 4,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 4,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'addressLine2',
                'settings' => json_encode(
                    [
                        'type' => 'addressLine2',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_2'],
                        'requiredMessage' => '',
                        'visible' => true,
                    ]
                ),
            ],
            [
                'id' => 9,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['city'],
                'placeholder' => BackendStrings::getTemplateStrings()['city'],
                'required' => true,
                'parentId' => 4,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 4,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'city',
                'settings' => json_encode(
                    [
                        'type' => 'city',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['city'],
                        'requiredMessage' => '',
                        'visible' => true,
                    ]
                ),
            ],
            [
                'id' => 10,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['state_province'],
                'placeholder' => BackendStrings::getTemplateStrings()['state'],
                'required' => true,
                'parentId' => 4,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 4,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'state',
                'settings' => json_encode(
                    [
                        'type' => 'state',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['state'],
                        'requiredMessage' => '',
                        'visible' => true,
                    ]
                ),
            ],
            [
                'id' => 11,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['zip_postal_code'],
                'placeholder' => BackendStrings::getTemplateStrings()['zip'],
                'required' => true,
                'parentId' => 4,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 4,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'zip',
                'settings' => json_encode(
                    [
                        'type' => 'zip',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['zip'],
                        'requiredMessage' => '',
                        'visible' => true,
                    ]
                ),
            ],
            [
                'id' => 12,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['country'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_country'],
                'required' => true,
                'parentId' => 4,
                'fieldIndex' => 4,
                'defaultValue' => '',
                'position' => 4,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'country',
                'settings' => json_encode(
                    [
                        'type' => 'country',
                        'hideLabel' => false,
                        'description' => '',
                        'placeholder' => BackendStrings::getTemplateStrings()['select_country'],
                        'requiredMessage' => '',
                        'visible' => true,
                    ]
                ),
            ],
        ];
    }

    /**
     * Get employment status and document upload fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEmploymentAndDocumentsFields(): array
    {
        return [
            [
                'id' => 13,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['employment_status'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_employment_status'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['employed'],
                        'value' => 'Employed',
                        'isDefault' => false,
                        'position' => 1,
                    ],
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['self_employed'],
                        'value' => 'Self-employed',
                        'isDefault' => false,
                        'position' => 2,
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['unemployed'],
                        'value' => 'Unemployed',
                        'isDefault' => false,
                        'position' => 3,
                    ],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['student'],
                        'value' => 'Student',
                        'isDefault' => false,
                        'position' => 4,
                    ],
                ],
            ],
            [
                'id' => 18,
                'fieldIndex' => 6,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['monthly_income'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_monthly_income'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
            [
                'id' => 19,
                'fieldIndex' => 7,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['proof_of_income'],
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
            [
                'id' => 20,
                'fieldIndex' => 8,
                'type' => 'file-upload',
                'label' => BackendStrings::getTemplateStrings()['id_document_upload'],
                'placeholder' => BackendStrings::getCommonStrings()['file_upload_placeholder_drag_here'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'maxFileSizeMb' => 999,
                'saveUploadsTo' => 'ivyforms',
                'allowedFileExtensions' => [],
                'allowMultiple' => false,
                'maxUploadCount' => 0,
            ],
        ];
    }

    /**
     * Get rental history and move-in details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRentalDetailsFields(): array
    {
        return [
            [
                'id' => 21,
                'fieldIndex' => 9,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['rental_history'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_rental_history'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'rows' => 4,
            ],
            [
                'id' => 22,
                'fieldIndex' => 10,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['reason_for_moving'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_reason_for_moving'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'rows' => 3,
            ],
            [
                'id' => 23,
                'fieldIndex' => 11,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['number_of_occupants'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_number_of_occupants'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 11,
            ],
            [
                'id' => 24,
                'fieldIndex' => 12,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['pets'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_pets'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 12,
                'rows' => 2,
            ],
            [
                'id' => 25,
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
            [
                'id' => 26,
                'fieldIndex' => 14,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['desired_move_in_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_desired_move_in_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 14,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
        ];
    }
}
