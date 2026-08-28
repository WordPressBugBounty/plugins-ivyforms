<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CharityEventRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'charity_event_registration_form',
            'name' => BackendStrings::getTemplateStrings()['charity_event_registration_form'],
            'description' => BackendStrings::getTemplateStrings()['charity_event_registration_form_desc'],
            'category' => 'event-registration-forms',
            'subcategory' => 'charity-event-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'charity-event-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['charity_event_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getPersonalInformationFields(),
                    self::getCharityEventFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get personal information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPersonalInformationFields(): array
    {
        return [
            // Name Field - Parent
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
            // First Name - Child of Name Field
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
            // Last Name - Child of Name Field
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
            // Email Address
            [
                'id' => 4,
                'fieldIndex' => 2,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => BackendStrings::getTemplateStrings()['email_address'],
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
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Get charity event details fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCharityEventFields(): array
    {
        return [
            // Volunteer Role
            [
                'id' => 10,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['volunteer_role'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_role'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['setup'],
                        'value' => 'setup',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['registration'],
                        'value' => 'registration',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['fundraising'],
                        'value' => 'fundraising',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['cleanup'],
                        'value' => 'cleanup',
                        'isDefault' => false,
                        'position' => 4
                    ],
                ]
            ],
            // Donation Amount
            [
                'id' => 11,
                'fieldIndex' => 5,
                'type' => 'number',
                'label' => BackendStrings::getTemplateStrings()['donation_amount'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_donation_amount'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
            ],
        ];
    }

    /**
     * Get additional information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInformationFields(): array
    {
        return [
            // Subscribe to Newsletter
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['subscribe_to_newsletter'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
            // Special Notes
            [
                'id' => 15,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['special_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['any_special_notes'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'rows' => 4,
            ],
        ];
    }
}
