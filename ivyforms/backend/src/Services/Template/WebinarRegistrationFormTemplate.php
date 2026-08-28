<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class WebinarRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'webinar_registration_form',
            'name' => BackendStrings::getTemplateStrings()['webinar_registration_form'],
            'description' => BackendStrings::getTemplateStrings()['webinar_registration_form_desc'],
            'category' => 'event-registration-forms',
            'subcategory' => 'webinar-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'webinar-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['webinar_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getParticipantInformationFields(),
                    self::getWebinarPreferencesFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get participant information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getParticipantInformationFields(): array
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
                'placeholder' => 'name@example.com',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
            ],
        ];
    }

    /**
     * Get webinar preferences fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWebinarPreferencesFields(): array
    {
        return [
            // Session Time
            [
                'id' => 8,
                'fieldIndex' => 3,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['session_time'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_session_time'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['morning'],
                        'value' => 'morning',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['afternoon'],
                        'value' => 'afternoon',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['evening'],
                        'value' => 'evening',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Participation Type
            [
                'id' => 11,
                'fieldIndex' => 4,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['participation_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['live'],
                        'value' => 'live',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['recorded'],
                        'value' => 'recorded',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
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
                'fieldIndex' => 5,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['subscribe_to_newsletter'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 5,
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
            // Questions for Speaker
            [
                'id' => 15,
                'fieldIndex' => 6,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['questions_for_speaker'],
                'placeholder' => BackendStrings::getTemplateStrings()['write_your_question_here'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'rows' => 4,
            ],
        ];
    }
}
