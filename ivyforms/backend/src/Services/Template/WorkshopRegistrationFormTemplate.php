<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class WorkshopRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'workshop_registration_form',
            'name' => BackendStrings::getTemplateStrings()['workshop_registration_form'],
            'description' => BackendStrings::getTemplateStrings()['workshop_registration_form_desc'],
            'category' => 'registration-forms',
            'subcategory' => 'workshop-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'workshop-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['workshop_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getPersonalInformationFields(),
                    self::getWorkshopSelectionFields(),
                    self::getTandCFields()
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
        ];
    }

    /**
     * Get workshop selection fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getWorkshopSelectionFields(): array
    {
        return [
            // Workshop level
            [
                'id' => 8,
                'fieldIndex' => 3,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['workshop_level'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_workshop_level'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['beginner'],
                        'value' => 'beginner',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['intermediate'],
                        'value' => 'intermediate',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['advanced'],
                        'value' => 'advanced',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Preferred Date
            [
                'id' => 12,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['preferred_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['date_1'],
                        'value' => 'date_1',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['date_2'],
                        'value' => 'date_2',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['date_3'],
                        'value' => 'date_3',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ],
            ],
            // Learning goals
            [
                'id' => 13,
                'fieldIndex' => 5,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['learning_goals'],
                'placeholder' => BackendStrings::getTemplateStrings()['learning_goals_description'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'rows' => 4,
            ],
        ];
    }

    /**
     * Get terms and conditions fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTandCFields(): array
    {
        return [
            // Terms and Conditions
            [
                'id' => 15,
                'fieldIndex' => 6,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['terms_and_conditions'],
                'placeholder' => '',
                'required' => true,
                'requiredMessage' => BackendStrings::getTemplateStrings()['terms_and_conditions_required'],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['agree_to_terms'],
                        'value' => 'agree',
                        'isDefault' => false,
                        'position' => 1
                    ],
                ]
            ],
        ];
    }
}
