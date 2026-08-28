<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ContactFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'contact_form',
            'name' => BackendStrings::getTemplateStrings()['contact_form'],
            'description' => BackendStrings::getTemplateStrings()['contact_form_desc'],
            'category' => 'contact-forms',
            'subcategory' => 'emergency-contact-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'contact-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['contact_form'],
                'published'     => 1,
                'showTitle'     => 1,
                'storeEntries'  => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => [
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
                    [
                        'id' => 4,
                        'fieldIndex' => 2,
                        'type' => 'email',
                        'label' => BackendStrings::getTemplateStrings()['email_address'],
                        'placeholder' => BackendStrings::getTemplateStrings()['enter_your_email_address'],
                        'required' => true,
                        'validation' => [
                            'required' => true,
                            'email' => true
                        ],
                        'settings' => [
                            'css_class' => 'ivyforms-field-email'
                        ],
                        'position' => 2,
                    ],
                    [
                        'id' => 5,
                        'fieldIndex' => 3,
                        'type' => 'text',
                        'label' => BackendStrings::getTemplateStrings()['subject'],
                        'placeholder' => BackendStrings::getTemplateStrings()['what_is_this_regarding'],
                        'required' => false,
                        'validation' => [
                            'required' => false,
                        ],
                        'settings' => [
                            'css_class' => 'ivyforms-field-subject'
                        ],
                        'position' => 3,
                    ],
                    [
                        'id' => 6,
                        'fieldIndex' => 4,
                        'type' => 'textarea',
                        'label' => BackendStrings::getTemplateStrings()['message'],
                        'placeholder' => BackendStrings::getTemplateStrings()['please_enter_your_message_here'],
                        'required' => false,
                        'validation' => [
                            'required' => false,
                        ],
                        'settings' => [
                            'css_class' => 'ivyforms-field-message'
                        ],
                        'position' => 4,
                    ]
                ],
                'settings' => []
            ]
            ]
        );
    }
}
