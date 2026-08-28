<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class HRInterviewFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'hr_interview_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['hr_interview_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['hr_interview_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'interview-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'hr-interview-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['hr_interview_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getCandidateInformationFields(),
                    self::getRatingFields(),
                    self::getBehavioralAssessmentFields(),
                    self::getInterviewerInformationFields()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Get candidate information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCandidateInformationFields(): array
    {
        return [
            // Candidate Full Name
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['candidate_full_name'],
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
            // Role Applied For
            [
                'id' => 10,
                'fieldIndex' => 2,
                'type' => 'select',
                'label' => BackendStrings::getTemplateStrings()['role_applied_for'],
                'placeholder' => BackendStrings::getTemplateStrings()['select_role_applied_for'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 2,
                'fieldOptions' => [
                    [
                        'id' => 4,
                        'label' => BackendStrings::getTemplateStrings()['software_engineer'],
                        'value' => 'Software Engineer',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['product_manager'],
                        'value' => 'Product Manager',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['designer'],
                        'value' => 'Designer',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['marketing_specialist'],
                        'value' => 'Marketing Specialist',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['sales_representative'],
                        'value' => 'Sales Representative',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'Other',
                        'isDefault' => false,
                        'position' => 6
                    ],
                ]
            ],
            // Interview Stage(s)
            [
                'id' => 16,
                'fieldIndex' => 3,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['interview_stages'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 3,
                'fieldOptions' => [
                    [
                        'id' => 11,
                        'label' => BackendStrings::getTemplateStrings()['initial_hr_screening'],
                        'value' => 'Initial HR screening',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['behavioral_interview'],
                        'value' => 'Behavioral interview',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['culture_fit_interview'],
                        'value' => 'Culture fit interview',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['final_interview'],
                        'value' => 'Final interview',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['panel_interview'],
                        'value' => 'Panel interview',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ]
            ],
        ];
    }

    /**
     * Get rating fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRatingFields(): array
    {
        return array_merge(
            self::getCommunicationSkillsRating(),
            self::getCulturalFitValuesAlignmentRating(),
            self::getMotivationEngagementRating(),
            self::getProfessionalismAttitudeRating()
        );
    }

    /**
     * Get communication skills rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCommunicationSkillsRating(): array
    {
        return [[
            'id' => 17,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['communication_skills'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['communication_skills_desc'],
            'fieldOptions' => self::getStandardRatingOptions(7),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get cultural fit and values alignment rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCulturalFitValuesAlignmentRating(): array
    {
        return [[
            'id' => 18,
            'fieldIndex' => 5,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['cultural_fit_values_alignment'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['cultural_fit_values_alignment_desc'],
            'fieldOptions' => self::getStandardRatingOptions(12),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get motivation and engagement rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getMotivationEngagementRating(): array
    {
        return [[
            'id' => 19,
            'fieldIndex' => 6,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['motivation_engagement'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 6,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['motivation_engagement_desc'],
            'fieldOptions' => self::getStandardRatingOptions(17),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get professionalism and attitude rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProfessionalismAttitudeRating(): array
    {
        return [[
            'id' => 20,
            'fieldIndex' => 7,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['professionalism_attitude'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 7,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['professionalism_attitude_desc'],
            'fieldOptions' => self::getStandardRatingOptions(22),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get standard rating options
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getStandardRatingOptions(int $startId): array
    {
        return [
            [
                'id' => $startId,
                'label' => BackendStrings::getNewFormStrings()['good'],
                'value' => '1',
                'isDefault' => false,
                'position' => 1
            ],
            [
                'id' => $startId + 1,
                'label' => BackendStrings::getNewFormStrings()['nice'],
                'value' => '2',
                'isDefault' => false,
                'position' => 2
            ],
            [
                'id' => $startId + 2,
                'label' => BackendStrings::getNewFormStrings()['very_good'],
                'value' => '3',
                'isDefault' => false,
                'position' => 3
            ],
            [
                'id' => $startId + 3,
                'label' => BackendStrings::getNewFormStrings()['awesome'],
                'value' => '4',
                'isDefault' => false,
                'position' => 4
            ],
            [
                'id' => $startId + 4,
                'label' => BackendStrings::getNewFormStrings()['amazing'],
                'value' => '5',
                'isDefault' => false,
                'position' => 5
            ],
        ];
    }

    /**
     * Get behavioral assessment fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getBehavioralAssessmentFields(): array
    {
        return array_merge(
            self::getStrengthsDemonstratedField(),
            self::getBehavioralConcernsField(),
            self::getBehavioralExamplesNotesField(),
            self::getOverallBehavioralAssessmentField(),
            self::getHRRecommendationField()
        );
    }

    /**
     * Get strengths demonstrated field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getStrengthsDemonstratedField(): array
    {
        return [[
            'id' => 32,
            'fieldIndex' => 8,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['strengths_demonstrated'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 8,
            'fieldOptions' => [
                [
                    'id' => 26,
                    'label' => BackendStrings::getTemplateStrings()['clear_confident_communication'],
                    'value' => 'Clear and confident communication',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 27,
                    'label' => BackendStrings::getTemplateStrings()['team_oriented_mindset'],
                    'value' => 'Team-oriented mindset',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 28,
                    'label' => BackendStrings::getTemplateStrings()['adaptability_flexibility'],
                    'value' => 'Adaptability and flexibility',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 29,
                    'label' => BackendStrings::getTemplateStrings()['ownership_accountability'],
                    'value' => 'Ownership and accountability',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 30,
                    'label' => BackendStrings::getTemplateStrings()['leadership_potential'],
                    'value' => 'Leadership potential',
                    'isDefault' => false,
                    'position' => 5
                ],
                [
                    'id' => 31,
                    'label' => BackendStrings::getTemplateStrings()['positive_attitude'],
                    'value' => 'Positive attitude',
                    'isDefault' => false,
                    'position' => 6
                ],
            ]
        ]];
    }

    /**
     * Get behavioral concerns field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getBehavioralConcernsField(): array
    {
        return [[
            'id' => 38,
            'fieldIndex' => 9,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['behavioral_concerns'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 9,
            'fieldOptions' => [
                [
                    'id' => 33,
                    'label' => BackendStrings::getTemplateStrings()['poor_communication'],
                    'value' => 'Poor communication',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 34,
                    'label' => BackendStrings::getTemplateStrings()['lack_of_motivation'],
                    'value' => 'Lack of motivation',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 35,
                    'label' => BackendStrings::getTemplateStrings()['resistance_to_feedback'],
                    'value' => 'Resistance to feedback',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 36,
                    'label' => BackendStrings::getTemplateStrings()['misalignment_with_values'],
                    'value' => 'Misalignment with values',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 37,
                    'label' => BackendStrings::getTemplateStrings()['unrealistic_expectations'],
                    'value' => 'Unrealistic expectations',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Get behavioral examples and notes field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getBehavioralExamplesNotesField(): array
    {
        return [[
            'id' => 39,
            'fieldIndex' => 10,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['behavioral_examples_notes'],
            'placeholder' => BackendStrings::getTemplateStrings()['behavioral_examples_notes_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 10,
            'rows' => 3,
        ]];
    }

    /**
     * Get overall behavioral assessment field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOverallBehavioralAssessmentField(): array
    {
        return [[
            'id' => 44,
            'fieldIndex' => 11,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['overall_behavioral_assessment'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 11,
            'fieldOptions' => [
                [
                    'id' => 40,
                    'label' => BackendStrings::getTemplateStrings()['excellent_fit'],
                    'value' => 'Excellent fit',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 41,
                    'label' => BackendStrings::getTemplateStrings()['good_fit'],
                    'value' => 'Good fit',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 42,
                    'label' => BackendStrings::getTemplateStrings()['partial_fit'],
                    'value' => 'Partial fit',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 43,
                    'label' => BackendStrings::getTemplateStrings()['poor_fit'],
                    'value' => 'Poor fit',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
        ]];
    }

    /**
     * Get HR recommendation field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getHRRecommendationField(): array
    {
        return [[
            'id' => 49,
            'fieldIndex' => 12,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['hr_recommendation'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 12,
            'fieldOptions' => [
                [
                    'id' => 45,
                    'label' => BackendStrings::getTemplateStrings()['strong_hire'],
                    'value' => 'Strong hire',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 46,
                    'label' => BackendStrings::getTemplateStrings()['hire'],
                    'value' => 'Hire',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 47,
                    'label' => BackendStrings::getTemplateStrings()['proceed_with_caution'],
                    'value' => 'Proceed with caution',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 48,
                    'label' => BackendStrings::getTemplateStrings()['do_not_proceed'],
                    'value' => 'Do not proceed',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
        ]];
    }

    /**
     * Get interviewer information fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getInterviewerInformationFields(): array
    {
        return [
            // HR Interviewer Name
            [
                'id' => 50,
                'fieldIndex' => 13,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['hr_interviewer_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'required' => false,
                'readonly' => false,
                'position' => 13,
            ],
            // First Name - Child of Name Field
            [
                'id' => 51,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['first_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_first_name'],
                'required' => false,
                'parentId' => 13,
                'fieldIndex' => 13,
                'defaultValue' => '',
                'position' => 1,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            // Last Name - Child of Name Field
            [
                'id' => 52,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['last_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_last_name'],
                'required' => false,
                'parentId' => 13,
                'fieldIndex' => 13,
                'defaultValue' => '',
                'position' => 2,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
            ],
        ];
    }
}
