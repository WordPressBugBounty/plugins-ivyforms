<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class YogaClassRegistrationFormTemplate
{
/**
 * Yoga Class Registration Form Template
 */
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'yoga_class_registration_form',
            'name' => BackendStrings::getTemplateStrings()['yoga_class_registration_form'],
            'description' => BackendStrings::getTemplateStrings()[
                'yoga_class_registration_form_description'
            ],
            'category' => 'registration-forms',
            'subcategory' => 'class-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'yoga-class-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['yoga_class_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getPersonalInformationFields(),
                    self::getClassDetailsFields(),
                    self::getAdditionalInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Personal information fields (name, email, phone, birth date)
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
                'position' => 3,
            ],
            // Phone Number
            [
                'id' => 5,
                'fieldIndex' => 3,
                'type' => 'phone',
                'label' => BackendStrings::getTemplateStrings()['phone_number'],
                'placeholder' => BackendStrings::getTemplateStrings()['phone_placeholder'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
            ],
            // Date of Birth
            [
                'id' => 6,
                'fieldIndex' => 4,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['date_of_birth'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_date_of_birth'],
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
     * Class details fields (level, schedule, duration)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getClassDetailsFields(): array
    {
        return [
            // Class Level
            [
                'id' => 10,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['class_level'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_class_level'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['beginner'],
                        'value' => 'beginner',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['intermediate'],
                        'value' => 'intermediate',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['advanced'],
                        'value' => 'advanced',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Preferred Schedule
            [
                'id' => 14,
                'fieldIndex' => 6,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['preferred_schedule'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_schedule'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'fieldOptions' => [
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['morning'],
                        'value' => 'morning',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['afternoon'],
                        'value' => 'afternoon',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['evening'],
                        'value' => 'evening',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Preferred Class Duration
            [
                'id' => 18,
                'fieldIndex' => 7,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['preferred_class_duration'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_class_duration'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 8,
                'fieldOptions' => [
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['duration_30_min'],
                        'value' => '30_min',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['duration_45_min'],
                        'value' => '45_min',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['duration_60_min'],
                        'value' => '60_min',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
        ];
    }

    /**
     * Additional information fields (health, equipment, experience, goals, private sessions)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInformationFields(): array
    {
        return [
            // Health Notes
            [
                'id' => 19,
                'fieldIndex' => 8,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['health_notes'],
                'placeholder' => BackendStrings::getTemplateStrings()['health_notes_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
            ],
            // Equipment Needed
            [
                'id' => 23,
                'fieldIndex' => 9,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['equipment_needed'],
                'placeholder' => '',
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'fieldOptions' => [
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['yoga_mat'],
                        'value' => 'yoga_mat',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 21,
                        'label' => BackendStrings::getTemplateStrings()['block'],
                        'value' => 'block',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 22,
                        'label' => BackendStrings::getTemplateStrings()['strap'],
                        'value' => 'strap',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Previous Yoga Experience
            [
                'id' => 24,
                'fieldIndex' => 10,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['previous_yoga_experience'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'previous_yoga_experience_placeholder'
                ],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 11,
            ],
            // Motivation / Goals
            [
                'id' => 25,
                'fieldIndex' => 11,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['motivation_goals'],
                'placeholder' => BackendStrings::getTemplateStrings()['motivation_goals_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 12,
            ],
            // Interested in Private Sessions? (radio)
            [
                'id' => 28,
                'fieldIndex' => 12,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['interested_private_sessions'],
                'placeholder' => '',
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 13,
                'fieldOptions' => [
                    [
                        'id' => 26,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 27,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ]
            ],
        ];
    }
}
