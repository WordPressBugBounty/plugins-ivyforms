<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class WeddingCakeOrderFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'wedding_cake_order_form',
            'name' => BackendStrings::getTemplateStrings()['wedding_cake_order_form'],
            'description' => BackendStrings::getTemplateStrings()['wedding_cake_order_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'cake-order-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'wedding-cake-order-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['wedding_cake_order_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getCustomerInformationFields(),
                    self::getCakeDetailsFields(),
                    self::getDeliveryDetailsFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get customer information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCustomerInformationFields(): array
    {
        return [
            // Bride & Groom Names - Parent
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['bride_groom_names'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'required' => true,
                'readonly' => false,
                'position' => 1,
            ],
            // First Name - Child of Name Field
            [
                'id' => 2,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['first_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_couple_names'],
                'required' => true,
                'parentId' => 1,
                'fieldIndex' => 1,
                'defaultValue' => '',
                'position' => 1,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            // Last Name - Child of Name Field
            [
                'id' => 3,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['last_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_couple_lastnames'],
                'required' => true,
                'parentId' => 1,
                'fieldIndex' => 1,
                'defaultValue' => '',
                'position' => 2,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
            ],
            // Email Address
            [
                'id' => 4,
                'fieldIndex' => 2,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_email'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
            ],
            // Phone Number
            [
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_phone_number'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Get cake details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCakeDetailsFields(): array
    {
        return array_merge(
            self::getWeddingDateField(),
            self::getGuestCountField(),
            self::getCakeFlavorField(),
            self::getNumberOfTiersField(),
            self::getCakeStyleThemeField(),
            self::getVenueAddressField(),
            self::getAdditionalRequestsField()
        );
    }

    /**
     * Get wedding date field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWeddingDateField(): array
    {
        return [
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['wedding_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_wedding_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
        ];
    }

    /**
     * Get number of guests field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getGuestCountField(): array
    {
        return [
            [
                'id' => 7,
                'fieldIndex' => 5,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['number_of_guests'],
                'placeholder' => BackendStrings::getTemplateStrings()['eg_150'],
                'required' => true,
                'parentId' => null,
                'position' => 5,
                'minValue' => 1,
                'maxValue' => 1000,
            ],
        ];
    }

    /**
     * Get cake flavor field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCakeFlavorField(): array
    {
        return [
            [
                'id' => 13,
                'fieldIndex' => 6,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['cake_flavors'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_cake_flavors'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'multiple' => true,
                'fieldOptions' => [
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['chocolate'],
                        'value' => 'chocolate',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['vanilla'],
                        'value' => 'vanilla',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['red_velvet'],
                        'value' => 'red_velvet',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['lemon'],
                        'value' => 'lemon',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['custom'],
                        'value' => 'custom',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ]
            ],
        ];
    }

    /**
     * Get number of tiers field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getNumberOfTiersField(): array
    {
        return [
            [
                'id' => 14,
                'fieldIndex' => 7,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['number_of_tiers'],
                'placeholder' => BackendStrings::getTemplateStrings()['eg_3'],
                'required' => true,
                'parentId' => null,
                'position' => 7,
                'minValue' => 1,
                'maxValue' => 10,
            ],
        ];
    }

    /**
     * Get cake style/theme field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCakeStyleThemeField(): array
    {
        return [
            [
                'id' => 15,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['preferred_cake_style_theme'],
                'placeholder' => BackendStrings::getTemplateStrings()['describe_theme_eg'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'required' => true,
                'rows' => 3,
            ],
        ];
    }

    /**
     * Get venue address field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getVenueAddressField(): array
    {
        return [
            // Address Field - Parent
            [
                'id' => 16,
                'fieldIndex' => 9,
                'type' => 'address',
                'label' => BackendStrings::getTemplateStrings()['venue_address'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'readonly' => false,
                'position' => 9,
                'required' => true,
            ],
            // Street Address - Child of Address Field
            [
                'id' => 18,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_1'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_1'],
                'required' => true,
                'parentId' => 9,
                'fieldIndex' => 9,
                'defaultValue' => '',
                'position' => 9,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'streetAddress',
                'settings' => json_encode(
                    [
                    'type' => 'streetAddress',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['address_line_1'],
                    'requiredMessage' => '',
                    'visible' => true
                    ]
                ),
            ],
            // Address Line 2 - Child of Address Field
            [
                'id' => 20,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['address_line_2'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_address_line_2'],
                'required' => false,
                'parentId' => 9,
                'fieldIndex' => 9,
                'defaultValue' => '',
                'position' => 9,
                'hideLabel' => false,
                'description' => '',
                'addressType' => 'addressLine2',
                'settings' => json_encode(
                    [
                    'type' => 'addressLine2',
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => BackendStrings::getTemplateStrings()['address_line_2'],
                    'requiredMessage' => '',
                    'visible' => true
                    ]
                ),
            ],
            // City - Child of Address Field
            [
                'id' => 22,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['city'],
                'placeholder' => BackendStrings::getTemplateStrings()['city'],
                'required' => true,
                'parentId' => 9,
                'fieldIndex' => 9,
                'defaultValue' => '',
                'position' => 9,
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
                    'visible' => true
                    ]
                ),
            ],
            // State/Province - Child of Address Field
            [
                'id' => 24,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['state_province'],
                'placeholder' => BackendStrings::getTemplateStrings()['state'],
                'required' => true,
                'parentId' => 9,
                'fieldIndex' => 9,
                'defaultValue' => '',
                'position' => 9,
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
                    'visible' => true
                    ]
                ),
            ],
            // Zip/Postal Code - Child of Address Field
            [
                'id' => 26,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['zip_postal_code'],
                'placeholder' => BackendStrings::getTemplateStrings()['zip'],
                'required' => true,
                'parentId' => 9,
                'fieldIndex' => 9,
                'defaultValue' => '',
                'position' => 9,
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
                    'visible' => true
                    ]
                ),
            ],
            // Country - Child of Address Field
            [
                'id' => 28,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['country'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_country'],
                'required' => true,
                'parentId' => 9,
                'fieldIndex' => 9,
                'defaultValue' => '',
                'position' => 9,
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
                    'visible' => true
                    ]
                ),
            ],
        ];
    }

    /**
     * Get additional requests field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalRequestsField(): array
    {
        return [
            [
                'id' => 29,
                'fieldIndex' => 10,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['additional_requests'],
                'placeholder' => BackendStrings::getTemplateStrings()['any_special_instructions_or_details'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'required' => false,
                'rows' => 3,
            ],
        ];
    }

    /**
     * Get delivery details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDeliveryDetailsFields(): array
    {
        return [];
    }
}
