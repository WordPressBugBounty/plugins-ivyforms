<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class FitnessClassRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'fitness_class_registration_form',
            'name' => BackendStrings::getTemplateStrings()['fitness_class_registration_form'],
            'description' => BackendStrings::getTemplateStrings()[
                'fitness_class_registration_form_desc'
            ],
            'category' => 'registration-forms',
            'subcategory' => 'class-registration-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'fitness-class-registration-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['fitness_class_registration_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getPersonalInformationFields(),
                    self::getClassSelectionFields(),
                    self::getSchedulePreferenceFields(),
                    self::getHealthAndGoalsFields(),
                    self::getAdditionalOptionsFields()
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
            // Email Address
            [
                'id' => 4,
                'fieldIndex' => 2,
                'type' => 'email',
                'label' => BackendStrings::getTemplateStrings()['email_address'],
                'placeholder' => BackendStrings::getTemplateStrings()['name_placeholder'],
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
                'phoneAutoDetect' => true,
            ],
        ];
    }

    /**
     * Class selection fields (class type, skill level)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getClassSelectionFields(): array
    {
        return [
            // Class Type
            [
                'id' => 12,
                'fieldIndex' => 4,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['class_type'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_class_type'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['yoga'],
                        'value' => 'yoga',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['pilates'],
                        'value' => 'pilates',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['hiit'],
                        'value' => 'hiit',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['zumba'],
                        'value' => 'zumba',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['crossfit'],
                        'value' => 'crossfit',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'other',
                        'isDefault' => false,
                        'position' => 6
                    ],
                ]
            ],
            // Skill Level
            [
                'id' => 16,
                'fieldIndex' => 5,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['skill_level'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_skill_level'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'fieldOptions' => [
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['beginner'],
                        'value' => 'beginner',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['intermediate'],
                        'value' => 'intermediate',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['advanced'],
                        'value' => 'advanced',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
        ];
    }

    /**
     * Schedule preference fields (start date, time, duration)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSchedulePreferenceFields(): array
    {
        return [
            // Preferred Start Date
            [
                'id' => 17,
                'fieldIndex' => 6,
                'type' => 'date',
                'label' => BackendStrings::getTemplateStrings()['preferred_start_date'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_start_date'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 6,
                'dateFieldType' => 'picker',
                'dateFormat' => 'MM/DD/YYYY',
            ],
            // Preferred Class Time
            [
                'id' => 21,
                'fieldIndex' => 9,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['preferred_class_time'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_preferred_class_time'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 9,
                'fieldOptions' => [
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['morning'],
                        'value' => 'morning',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 19,
                        'label' => BackendStrings::getTemplateStrings()['afternoon'],
                        'value' => 'afternoon',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 20,
                        'label' => BackendStrings::getTemplateStrings()['evening'],
                        'value' => 'evening',
                        'isDefault' => false,
                        'position' => 3
                    ],
                ]
            ],
            // Preferred Class Duration
            [
                'id' => 26,
                'fieldIndex' => 11,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['preferred_class_duration'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'select_preferred_class_duration'
                ],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 11,
                'fieldOptions' => [
                    [
                        'id' => 22,
                        'label' => BackendStrings::getTemplateStrings()['duration_30_min'],
                        'value' => '30_min',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 23,
                        'label' => BackendStrings::getTemplateStrings()['duration_1_hour'],
                        'value' => '1_hour',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 24,
                        'label' => BackendStrings::getTemplateStrings()['duration_90_min'],
                        'value' => '90_min',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 25,
                        'label' => BackendStrings::getTemplateStrings()['duration_2_hours'],
                        'value' => '2_hours',
                        'isDefault' => false,
                        'position' => 4
                    ],
                ]
            ],
        ];
    }

    /**
     * Health and goals fields (health notes, equipment, fitness goals)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getHealthAndGoalsFields(): array
    {
        return [
            // Health Notes / Injuries
            [
                'id' => 27,
                'fieldIndex' => 7,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['health_notes_injuries'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'describe_health_conditions_injuries'
                ],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 7,
                'required' => false,
                'rows' => 3,
            ],
            // Require Equipment? (radio)
            [
                'id' => 30,
                'fieldIndex' => 8,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['require_equipment'],
                'parentId' => null,
                'position' => 8,
                'required' => false,
                'defaultValue' => '',
                'placeholder' => '',
                'fieldOptions' => [
                    [
                        'id' => 28,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 29,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ],
            ],
            // Fitness Goals
            [
                'id' => 31,
                'fieldIndex' => 10,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['fitness_goals'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'fitness_goals_placeholder'
                ],
                'parentId' => null,
                'defaultValue' => '',
                'position' => 10,
                'required' => false,
                'rows' => 3,
            ],
        ];
    }

    /**
     * Additional options fields (personal training interest, trainer preference, updates)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalOptionsFields(): array
    {
        return [
            // Interested in Personal Training? (radio)
            [
                'id' => 34,
                'fieldIndex' => 12,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()[
                    'interested_personal_training'
                ],
                'parentId' => null,
                'position' => 12,
                'required' => false,
                'defaultValue' => '',
                'placeholder' => '',
                'fieldOptions' => [
                    [
                        'id' => 32,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 33,
                        'label' => BackendStrings::getTemplateStrings()['no'],
                        'value' => 'no',
                        'isDefault' => false,
                        'position' => 2
                    ],
                ],
            ],
            // Trainer Preference
            [
                'id' => 35,
                'fieldIndex' => 13,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['trainer_preference'],
                'placeholder' => BackendStrings::getTemplateStrings()[
                    'enter_trainer_name_if_any'
                ],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 13,
            ],
            // Receive updates about future classes? (radio)
            [
                'id' => 38,
                'fieldIndex' => 14,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['receive_updates_future_classes'],
                'parentId' => null,
                'position' => 14,
                'required' => false,
                'defaultValue' => '',
                'placeholder' => '',
                'fieldOptions' => [
                    [
                        'id' => 36,
                        'label' => BackendStrings::getTemplateStrings()['yes'],
                        'value' => 'yes',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 37,
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
