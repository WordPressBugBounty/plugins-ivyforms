<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class TechnicalRoleInterviewFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'technical_role_interview_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['technical_role_interview_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['technical_role_interview_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'interview-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'technical-role-interview-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['technical_role_interview_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getCandidateInformationFields(),
                    self::getRatingFields(),
                    self::getTechnicalAssessmentFields(),
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
                'id' => 11,
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
                        'label' => BackendStrings::getTemplateStrings()['frontend_developer'],
                        'value' => 'Frontend Developer',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['backend_developer'],
                        'value' => 'Backend Developer',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['fullstack_developer'],
                        'value' => 'Full-stack Developer',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 7,
                        'label' => BackendStrings::getTemplateStrings()['qa_engineer'],
                        'value' => 'QA Engineer',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 8,
                        'label' => BackendStrings::getTemplateStrings()['data_engineer_analyst'],
                        'value' => 'Data Engineer / Analyst',
                        'isDefault' => false,
                        'position' => 5
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getTemplateStrings()['devops_engineer'],
                        'value' => 'DevOps Engineer',
                        'isDefault' => false,
                        'position' => 6
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getTemplateStrings()['other'],
                        'value' => 'Other',
                        'isDefault' => false,
                        'position' => 7
                    ],
                ]
            ],
            // Interview Stage(s)
            [
                'id' => 17,
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
                        'id' => 12,
                        'label' => BackendStrings::getTemplateStrings()['technical_screening'],
                        'value' => 'Technical screening',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 13,
                        'label' => BackendStrings::getTemplateStrings()['live_coding_interview'],
                        'value' => 'Live coding interview',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['system_design_interview'],
                        'value' => 'System design interview',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['technical_task_assignment'],
                        'value' => 'Technical task / assignment',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['final_technical_round'],
                        'value' => 'Final technical round',
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
            self::getTechnicalKnowledgeRating(),
            self::getProblemSolvingApproachRating(),
            self::getCodeQualitySolutionQualityRating(),
            self::getSystemDesignArchitectureRating()
        );
    }

    /**
     * Get technical knowledge rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalKnowledgeRating(): array
    {
        return [[
            'id' => 18,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['technical_knowledge'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['technical_knowledge_desc'],
            'fieldOptions' => self::getStandardRatingOptions(8),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get problem-solving approach rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getProblemSolvingApproachRating(): array
    {
        return [[
            'id' => 19,
            'fieldIndex' => 5,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['problem_solving_approach'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['problem_solving_approach_desc'],
            'fieldOptions' => self::getStandardRatingOptions(13),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get code quality/solution quality rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCodeQualitySolutionQualityRating(): array
    {
        return [[
            'id' => 20,
            'fieldIndex' => 6,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['code_quality_solution_quality'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 6,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['code_quality_solution_quality_desc'],
            'fieldOptions' => self::getStandardRatingOptions(18),
            'showValues' => false,
            'showRatingText' => false,
        ]];
    }

    /**
     * Get system design and architecture rating
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSystemDesignArchitectureRating(): array
    {
        return [[
            'id' => 21,
            'fieldIndex' => 7,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['system_design_architecture'],
            'required' => false,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 7,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'description' => BackendStrings::getTemplateStrings()['system_design_architecture_desc'],
            'fieldOptions' => self::getStandardRatingOptions(23),
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
     * Get technical assessment fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalAssessmentFields(): array
    {
        return array_merge(
            self::getTechnicalStrengthsObservedField(),
            self::getTechnicalConcernsField(),
            self::getTechnicalNotesField(),
            self::getTechnicalReadinessForRoleField(),
            self::getTechnicalHiringRecommendationField()
        );
    }

    /**
     * Get technical strengths observed field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalStrengthsObservedField(): array
    {
        return [[
            'id' => 33,
            'fieldIndex' => 8,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['technical_strengths_observed'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 8,
            'fieldOptions' => [
                [
                    'id' => 27,
                    'label' => BackendStrings::getTemplateStrings()['strong_fundamentals'],
                    'value' => 'Strong fundamentals',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 28,
                    'label' => BackendStrings::getTemplateStrings()['clean_readable_code'],
                    'value' => 'Clean and readable code',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 29,
                    'label' => BackendStrings::getTemplateStrings()['efficient_algorithms'],
                    'value' => 'Efficient algorithms',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 30,
                    'label' => BackendStrings::getTemplateStrings()['good_debugging_skills'],
                    'value' => 'Good debugging skills',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 31,
                    'label' => BackendStrings::getTemplateStrings()['strong_system_design_thinking'],
                    'value' => 'Strong system design thinking',
                    'isDefault' => false,
                    'position' => 5
                ],
                [
                    'id' => 32,
                    'label' => BackendStrings::getTemplateStrings()['knowledge_best_practices'],
                    'value' => 'Knowledge of best practices',
                    'isDefault' => false,
                    'position' => 6
                ],
            ]
        ]];
    }

    /**
     * Get technical concerns field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalConcernsField(): array
    {
        return [[
            'id' => 39,
            'fieldIndex' => 9,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['technical_concerns'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 9,
            'fieldOptions' => [
                [
                    'id' => 34,
                    'label' => BackendStrings::getTemplateStrings()['gaps_core_knowledge'],
                    'value' => 'Gaps in core knowledge',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 35,
                    'label' => BackendStrings::getTemplateStrings()['inefficient_solutions'],
                    'value' => 'Inefficient solutions',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 36,
                    'label' => BackendStrings::getTemplateStrings()['poor_code_organization'],
                    'value' => 'Poor code organization',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 37,
                    'label' => BackendStrings::getTemplateStrings()['limited_experience_tools_stack'],
                    'value' => 'Limited experience with tools/stack',
                    'isDefault' => false,
                    'position' => 4
                ],
                [
                    'id' => 38,
                    'label' => BackendStrings::getTemplateStrings()['difficulty_explaining_decisions'],
                    'value' => 'Difficulty explaining decisions',
                    'isDefault' => false,
                    'position' => 5
                ],
            ]
        ]];
    }

    /**
     * Get technical notes field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalNotesField(): array
    {
        return [[
            'id' => 40,
            'fieldIndex' => 10,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['technical_notes'],
            'placeholder' => BackendStrings::getTemplateStrings()['technical_notes_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 10,
            'rows' => 3,
        ]];
    }

    /**
     * Get technical readiness for role field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalReadinessForRoleField(): array
    {
        return [[
            'id' => 45,
            'fieldIndex' => 11,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['technical_readiness_role'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 11,
            'fieldOptions' => [
                [
                    'id' => 41,
                    'label' => BackendStrings::getTemplateStrings()['exceeds_expectations'],
                    'value' => 'Exceeds expectations',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 42,
                    'label' => BackendStrings::getTemplateStrings()['meets_expectations'],
                    'value' => 'Meets expectations',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 43,
                    'label' => BackendStrings::getTemplateStrings()['partially_meets_expectations'],
                    'value' => 'Partially meets expectations',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 44,
                    'label' => BackendStrings::getTemplateStrings()['does_not_meet_expectations'],
                    'value' => 'Does not meet expectations',
                    'isDefault' => false,
                    'position' => 4
                ],
            ]
        ]];
    }

    /**
     * Get technical hiring recommendation field
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getTechnicalHiringRecommendationField(): array
    {
        return [[
            'id' => 50,
            'fieldIndex' => 12,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['technical_hiring_recommendation'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 12,
            'fieldOptions' => [
                [
                    'id' => 46,
                    'label' => BackendStrings::getTemplateStrings()['strong_hire'],
                    'value' => 'Strong hire',
                    'isDefault' => false,
                    'position' => 1
                ],
                [
                    'id' => 47,
                    'label' => BackendStrings::getTemplateStrings()['hire'],
                    'value' => 'Hire',
                    'isDefault' => false,
                    'position' => 2
                ],
                [
                    'id' => 48,
                    'label' => BackendStrings::getTemplateStrings()['consider_after_improvement'],
                    'value' => 'Consider after improvement',
                    'isDefault' => false,
                    'position' => 3
                ],
                [
                    'id' => 49,
                    'label' => BackendStrings::getTemplateStrings()['do_not_hire'],
                    'value' => 'Do not hire',
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
            // Interviewer Name
            [
                'id' => 51,
                'fieldIndex' => 13,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['interviewer_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'required' => false,
                'readonly' => false,
                'position' => 13,
            ],
            // First Name - Child of Name Field
            [
                'id' => 52,
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
                'id' => 53,
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
