<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CraftVendorRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'craft_vendor_registration_form',
            'name' => BackendStrings::getTemplateStrings()['craft_vendor_registration_form'],
            'description' => BackendStrings::getTemplateStrings()[
                'craft_vendor_registration_form_desc'
            ],
            'category' => 'registration-forms',
            'subcategory' => 'vendor-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'craft-vendor-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['craft_vendor_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getVendorInformationFields(),
                    self::getProductDetailsFields(),
                    self::getBoothDetailsFields(),
                    self::getAdditionalFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Vendor information fields (business name, contact, email, phone)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getVendorInformationFields(): array
    {
        return [
            // Business / Vendor Name
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['business_vendor_name'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'enter_business_name'
                ],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 1,
            ],
            // Owner / Contact Person
            [
                'id' => 2,
                'fieldIndex' => 2,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['owner_contact_person'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'enter_owner_contact_name'
                ],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
            ],
            // Email Address
            [
                'id' => 3,
                'fieldIndex' => 3,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => BackendStrings::getTemplateStrings()['name_placeholder'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
            ],
            // Phone Number
            [
                'id' => 4,
                'fieldIndex' => 4,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'phone_placeholder'
                ],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Product details fields (craft type, products description)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProductDetailsFields(): array
    {
        return [
            // Craft Type
            [
                'id' => 10,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['craft_type'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'select_craft_type'
                ],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['jewelry'],
                        'value' => 'jewelry',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['pottery'],
                        'value' => 'pottery',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['textiles'],
                        'value' => 'textiles',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['woodwork'],
                        'value' => 'woodwork',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ]
            ],
            // Products / Description
            [
                'id' => 11,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['products_description'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'describe_your_items'
                ],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'required' => true,
                'rows' => 4,
            ],
        ];
    }

    /**
     * Booth details fields (space size, electricity needed)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getBoothDetailsFields(): array
    {
        return [
            // Booth / Space Size Required
            [
                'id' => 12,
                'fieldIndex' => 7,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()[
                    'booth_space_size_required'
                ],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'enter_space_size'
                ],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
            // Need Electricity
            [
                'id' => 15,
                'fieldIndex' => 8,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['need_electricity'],
                'parentId' => null,
                'position' => 8,
                'required' => false,
                'defaultValue' => '',
                'placeholder' => '',
                'fieldOptions' => [
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ],
            ],
        ];
    }

    /**
     * Additional fields (notes, available dates, vendor rules agreement)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalFields(): array
    {
        return [
            // Additional Notes / Requests
            [
                'id' => 16,
                'fieldIndex' => 9,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()[
                    'additional_notes_requests'
                ],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'vendor_additional_notes_placeholder'
                ],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'required' => false,
                'rows' => 3,
            ],
            // Available Dates
            [
                'id' => 17,
                'fieldIndex' => 10,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['available_date'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'select_available_date'
                ],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Agree to Vendor Rules & Policies
            [
                'id' => 20,
                'fieldIndex' => 11,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()[
                    'agree_vendor_rules_policies'
                ],
                'parentId' => null,
                'position' => 11,
                'required' => true,
                'defaultValue' => '',
                'placeholder' => '',
                'fieldOptions' => [
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ],
            ],
        ];
    }
}
