<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class FoodVendorRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'food_vendor_registration_form',
            'name' => BackendStrings::getTemplateStrings()['food_vendor_registration_form'],
            'description' => BackendStrings::getTemplateStrings()[
                'food_vendor_registration_form_desc'
            ],
            'category' => 'registration-forms',
            'subcategory' => 'vendor-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'food-vendor-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['food_vendor_registration_form'],
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
     * Vendor information fields
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
                'placeholder' => BackendStrings::getTemplateStrings()['enter_business_name'],
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
                'placeholder' => BackendStrings::getTemplateStrings()['enter_owner_contact_name'],
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
            // Phone Number (optional)
            [
                'id' => 4,
                'fieldIndex' => 4,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => BackendStrings::getTemplateStrings()['phone_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Product details fields (food type, menu offerings)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProductDetailsFields(): array
    {
        return [
            // Food Type (select)
            [
                'id' => 10,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['food_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_food_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['baked_goods'],
                        'value' => 'baked_goods',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['beverages'],
                        'value' => 'beverages',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['snacks'],
                        'value' => 'snacks',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['full_meals'],
                        'value' => 'full_meals',
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
            // Menu Items / Offerings (required textarea)
            [
                'id' => 11,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['menu_items_offerings'],
                'placeholder' => BackendStrings::getTemplateStrings()['list_main_products_or_menu'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'required' => true,
                'rows' => 4,
            ],
        ];
    }

    /**
     * Booth details fields (size, electricity)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getBoothDetailsFields(): array
    {
        return [
            // Booth / Stall Size Needed (number, optional)
            [
                'id' => 12,
                'fieldIndex' => 7,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['booth_stall_size_needed'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_size_sq_meters'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
            // Require Electricity? (radio, optional)
            [
                'id' => 15,
                'fieldIndex' => 8,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['require_electricity'],
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
     * Additional fields (special requirements, available dates, terms agreement)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalFields(): array
    {
        return [
            // Special Requirements (optional textarea)
            [
                'id' => 16,
                'fieldIndex' => 9,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['special_requirements'],
                'placeholder' => BackendStrings::getTemplateStrings()['special_requirements_placeholder'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'required' => false,
                'rows' => 3,
            ],
            // Available Dates (date picker, required)
            [
                'id' => 17,
                'fieldIndex' => 10,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['available_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_available_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Agree to Vendor Terms & Conditions (radio, required)
            [
                'id' => 20,
                'fieldIndex' => 11,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['agree_vendor_terms_conditions'],
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
