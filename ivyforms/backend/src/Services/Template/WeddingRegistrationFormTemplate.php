<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class WeddingRegistrationFormTemplate
{
/**
 * Wedding Registration Form Template
 */
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'wedding_registration_form',
            'name' => BackendStrings::getTemplateStrings()['wedding_registration_form'],
            'description' => BackendStrings::getTemplateStrings()[
                'wedding_registration_form_description'
            ],
            'category' => 'event-registration-forms',
            'subcategory' => 'wedding-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'wedding-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['wedding_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getPersonalInformationFields(),
                    self::getGuestFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Personal information fields (name, email)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPersonalInformationFields(): array
    {
        return [
            // Full Name
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['full_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_full_name'],
                'required' => true,
                'readonly' => false,
                'position' => 1,
            ],
            // First Name - Child of Name Field
            [
                'id' => 2,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['first_name'],
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
            // Last Name - Child of Name Field
            [
                'id' => 3,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['last_name'],
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
            // Email Address
            [
                'id' => 4,
                'fieldIndex' => 2,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => BackendStrings::getTemplateStrings()['email_placeholder'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
            ],
        ];
    }

    /**
     * Guest fields (number of guests, meal preference)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getGuestFields(): array
    {
        return [
            // Number of Guests
            [
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['number_of_guests'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_number_of_guests'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'minValue' => 1,
            ],
            // Meal Preference
            [
                'id' => 10,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['meal_preference'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_meal_preference'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['vegetarian'],
                        'value' => 'vegetarian',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['non_vegetarian'],
                        'value' => 'non_vegetarian',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['vegan'],
                        'value' => 'vegan',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['kids_menu'],
                        'value' => 'kids_menu',
                        'isDefault' => false,
                        'position' => 4
                    ],
                ]
            ],
        ];
    }

    /**
     * Additional information fields (message to couple)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInformationFields(): array
    {
        return [
            // Special Requests
            [
                'id' => 14,
                'fieldIndex' => 5,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['special_requests'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['wheelchair_access'],
                        'value' => 'wheelchair_access',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['allergy_information'],
                        'value' => 'allergy_information',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Message to the Couple
            [
                'id' => 15,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['message_to_the_couple'],
                'placeholder' => BackendStrings::getTemplateStrings()['write_your_message_or_wishes_here'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
        ];
    }
}
