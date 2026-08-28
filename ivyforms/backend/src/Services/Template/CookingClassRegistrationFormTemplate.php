<?php

declare(strict_types=1);

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CookingClassRegistrationFormTemplate
{
/**
 * Cooking Class Registration Form Template
 */
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'cooking_class_registration_form',
            'name' => BackendStrings::getTemplateStrings()['cooking_class_registration_form'],
            'description' => BackendStrings::getTemplateStrings()[
                'cooking_class_registration_form_description'
            ],
            'category' => 'registration-forms',
            'subcategory' => 'class-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'cooking-class-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['cooking_class_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getPersonalInformationFields(),
                    self::getClassDetailsFields(),
                    self::getPreferencesFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Personal information fields (name, email, phone)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPersonalInformationFields(): array
    {
        return [
            // Full Name - Parent
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
                'position' => 2,
            ],
            // Phone Number
            [
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => BackendStrings::getTemplateStrings()['phone_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 3,
            ],
        ];
    }

    /**
     * Class details fields (skill level, preferred date)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getClassDetailsFields(): array
    {
        return [
            // Skill Level
            [
                'id' => 9,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['skill_level'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_skill_level'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['beginner'],
                        'value' => 'beginner',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['intermediate'],
                        'value' => 'intermediate',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['advanced'],
                        'value' => 'advanced',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Preferred Class Date
            [
                'id' => 10,
                'fieldIndex' => 5,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['preferred_class_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_class_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
        ];
    }

    /**
     * Preferences fields (favorite cuisine, dietary restrictions, equipment)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getPreferencesFields(): array
    {
        return [
            // Favorite Cuisine
            [
                'id' => 11,
                'fieldIndex' => 6,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['favorite_cuisine'],
                'placeholder' => BackendStrings::getTemplateStrings()['favorite_cuisine_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
            ],
            // Dietary Restrictions
            [
                'id' => 12,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['dietary_restrictions'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'cooking_dietary_restrictions_placeholder'
                ],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
            ],
            // Equipment Needed
            [
                'id' => 15,
                'fieldIndex' => 8,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['equipment_needed'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'fieldOptions' => [
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['apron'],
                        'value' => 'apron',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['cooking_tools'],
                        'value' => 'cooking_tools',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
        ];
    }

    /**
     * Additional information fields (motivation/goals)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInformationFields(): array
    {
        return [
            // Motivation / Goals
            [
                'id' => 16,
                'fieldIndex' => 9,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['motivation_goals'],
                'placeholder' => BackendStrings::getTemplateStrings()['motivation_goals_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
            ],
        ];
    }
}
