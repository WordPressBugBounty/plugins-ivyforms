<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class CandidateEvaluationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'candidate_evaluation_form',
            'name' => BackendStrings::getTemplateStrings()['candidate_evaluation_form'],
            'description' => BackendStrings::getTemplateStrings()['candidate_evaluation_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'interview-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'candidate-evaluation-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['candidate_evaluation_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getCandidateInformationFields(),
                    self::getRatingFields(),
                    self::getEvaluationAssessmentFields(),
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
        return array_merge(
            self::getCandidateFullNameField(),
            self::getCandidateEmailField(),
            self::getRoleAppliedForField(),
            self::getInterviewStagesField()
        );
    }

    /**
     * Get candidate full name field with children
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCandidateFullNameField(): array
    {
        return [
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
        ];
    }

    /**
     * Get candidate email field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCandidateEmailField(): array
    {
        return [[
            'id' => 4,
            'fieldIndex' => 2,
            'type' => 'email',
            'label' => BackendStrings::getNewFormStrings()['email'],
            'placeholder' => BackendStrings::getNewFormStrings()['enter_email_address'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 2,
        ]];
    }

    /**
     * Get role applied for field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRoleAppliedForField(): array
    {
        return [[
            'id' => 11,
            'fieldIndex' => 3,
            'type' => 'select',
            'label' => BackendStrings::getTemplateStrings()['role_applied_for'],
            'placeholder' => BackendStrings::getTemplateStrings()['select_role_applied_for'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 3,
            'fieldOptions' => [
                [
                    'id' => 5,
                    'label' => BackendStrings::getTemplateStrings()['software_engineer'],
                    'value' => 'Software Engineer',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 6,
                    'label' => BackendStrings::getTemplateStrings()['product_manager'],
                    'value' => 'Product Manager',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 7,
                    'label' => BackendStrings::getTemplateStrings()['designer'],
                    'value' => 'Designer',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['marketing_specialist'],
                    'value' => 'Marketing Specialist',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['sales_representative'],
                    'value' => 'Sales Representative',
                    'isDefault' => false,
                    'position' => 5
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['other'],
                    'value' => 'Other',
                    'isDefault' => false,
                    'position' => 6
                ],
            ]
        ]];
    }

    /**
     * Get interview stages field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getInterviewStagesField(): array
    {
        return [[
            'id' => 18,
            'fieldIndex' => 4,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['interview_stages'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'fieldOptions' => [
                [
                    'id' => 12,
                    'label' => BackendStrings::getTemplateStrings()['initial_screening'],
                    'value' => 'Initial screening',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 13,
                    'label' => BackendStrings::getTemplateStrings()['technical_interview'],
                    'value' => 'Technical interview',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 14,
                    'label' => BackendStrings::getTemplateStrings()['hr_behavioral_interview'],
                    'value' => 'HR / Behavioral interview',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 15,
                    'label' => BackendStrings::getTemplateStrings()['case_study_task'],
                    'value' => 'Case study or task',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 16,
                    'label' => BackendStrings::getTemplateStrings()['final_interview'],
                    'value' => 'Final interview',
                    'isDefault' => false,
                    'position' => 5
                ],
                [
                    'id' => 17,
                    'label' => BackendStrings::getTemplateStrings()['panel_interview'],
                    'value' => 'Panel interview',
                    'isDefault' => false,
                    'position' => 6
                ],
            ]
        ]];
    }

    /**
     * Get rating fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRatingFields(): array
    {
        return array_merge(
            self::getRoleSpecificSkillsRating(),
            self::getCommunicationSkillsRating(),
            self::getProblemSolvingRating(),
            self::getCulturalFitRating()
        );
    }

    /**
     * Get role-specific skills rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRoleSpecificSkillsRating(): array
    {
        return [[
            'id' => 19,
            'fieldIndex' => 5,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['role_specific_technical_skills'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['role_specific_technical_skills_desc'],
            'fieldOptions' => self::getStandardRatingOptions(13),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get communication skills rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCommunicationSkillsRating(): array
    {
        return [[
            'id' => 20,
            'fieldIndex' => 6,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['communication_skills'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 6,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['communication_skills_desc'],
            'fieldOptions' => self::getStandardRatingOptions(18),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get problem-solving rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProblemSolvingRating(): array
    {
        return [[
            'id' => 21,
            'fieldIndex' => 7,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['problem_solving_critical_thinking'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 7,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['problem_solving_critical_thinking_desc'],
            'fieldOptions' => self::getStandardRatingOptions(23),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get cultural fit rating field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCulturalFitRating(): array
    {
        return [[
            'id' => 22,
            'fieldIndex' => 8,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['cultural_fit_attitude'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 8,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['cultural_fit_attitude_desc'],
            'fieldOptions' => self::getStandardRatingOptions(28),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get standard 5-star rating options
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
     * Get evaluation assessment fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getEvaluationAssessmentFields(): array
    {
        return array_merge(
            self::getKeyStrengthsObservedField(),
            self::getAreasOfConcernField(),
            self::getAdditionalInterviewerNotesField(),
            self::getOverallHiringRecommendationField(),
            self::getJustificationForRecommendationField()
        );
    }

    /**
     * Get key strengths observed field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getKeyStrengthsObservedField(): array
    {
        return [[
            'id' => 34,
            'fieldIndex' => 9,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['key_strengths_observed'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 9,
            'fieldOptions' => [
                [
                    'id' => 28,
                    'label' => BackendStrings::getTemplateStrings()['strong_technical_foundation'],
                    'value' => 'Strong technical foundation',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 29,
                    'label' => BackendStrings::getTemplateStrings()['excellent_communication'],
                    'value' => 'Excellent communication',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 30,
                    'label' => BackendStrings::getTemplateStrings()['fast_learner'],
                    'value' => 'Fast learner',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 31,
                    'label' => BackendStrings::getTemplateStrings()['relevant_prior_experience'],
                    'value' => 'Relevant prior experience',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 32,
                    'label' => BackendStrings::getTemplateStrings()['leadership_potential'],
                    'value' => 'Leadership potential',
                    'isDefault' => false,
                    'position' => 5
                ],
                [
                    'id' => 33,
                    'label' => BackendStrings::getTemplateStrings()['collaborative_mindset'],
                    'value' => 'Collaborative mindset',
                    'isDefault' => false,
                    'position' => 6
                ],
            ]
        ]];
    }

    /**
     * Get areas of concern field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAreasOfConcernField(): array
    {
        return [[
            'id' => 40,
            'fieldIndex' => 10,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['areas_of_concern'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 10,
            'fieldOptions' => [
                [
                    'id' => 35,
                    'label' => BackendStrings::getTemplateStrings()['skill_gaps'],
                    'value' => 'Skill gaps',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 36,
                    'label' => BackendStrings::getTemplateStrings()['limited_experience'],
                    'value' => 'Limited experience',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 37,
                    'label' => BackendStrings::getTemplateStrings()['communication_challenges'],
                    'value' => 'Communication challenges',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 38,
                    'label' => BackendStrings::getTemplateStrings()['lack_domain_knowledge'],
                    'value' => 'Lack of domain knowledge',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 39,
                    'label' => BackendStrings::getTemplateStrings()['cultural_misalignment'],
                    'value' => 'Cultural misalignment',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Get additional interviewer notes field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalInterviewerNotesField(): array
    {
        return [[
            'id' => 41,
            'fieldIndex' => 11,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['additional_interviewer_notes'],
            'placeholder' => BackendStrings::getTemplateStrings()['additional_interviewer_notes_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 11,
            'rows' => 3,
        ]];
    }

    /**
     * Get overall hiring recommendation field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOverallHiringRecommendationField(): array
    {
        return [[
            'id' => 47,
            'fieldIndex' => 12,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['overall_hiring_recommendation'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 12,
            'fieldOptions' => [
                [
                    'id' => 42,
                    'label' => BackendStrings::getTemplateStrings()['strong_hire'],
                    'value' => 'Strong hire',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 43,
                    'label' => BackendStrings::getTemplateStrings()['hire'],
                    'value' => 'Hire',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 44,
                    'label' => BackendStrings::getTemplateStrings()['consider_for_another_role'],
                    'value' => 'Consider for another role',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 45,
                    'label' => BackendStrings::getTemplateStrings()['hold_for_future'],
                    'value' => 'Hold for future',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 46,
                    'label' => BackendStrings::getTemplateStrings()['do_not_hire'],
                    'value' => 'Do not hire',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Get justification for recommendation field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getJustificationForRecommendationField(): array
    {
        return [[
            'id' => 48,
            'fieldIndex' => 13,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['justification_for_recommendation'],
            'placeholder' => BackendStrings::getTemplateStrings()['justification_for_recommendation_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 13,
            'rows' => 3,
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
            // Interviewer Name
            [
                'id' => 49,
                'fieldIndex' => 14,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['interviewer_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'required' => false,
                'readonly' => false,
                'position' => 14,
            ],
            // First Name - Child of Name Field
            [
                'id' => 50,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['first_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_first_name'],
                'required' => false,
                'parentId' => 14,
                'fieldIndex' => 14,
                'defaultValue' => '',
                'position' => 1,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            // Last Name - Child of Name Field
            [
                'id' => 51,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['last_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_last_name'],
                'required' => false,
                'parentId' => 14,
                'fieldIndex' => 14,
                'defaultValue' => '',
                'position' => 2,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
            ],
        ];
    }
}
