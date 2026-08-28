<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class TechnicalInterviewFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'technical_interview_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['technical_interview_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['technical_interview_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'interview-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'technical-interview-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['technical_interview_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getCandidateInformationFields(),
                    self::getInterviewOutcomeFields(),
                    self::getRatingFields(),
                    self::getKeyAreasFields(),
                    self::getRecommendationFields(),
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
            // Candidate Name or Reference (Text Field)
            [
                'id' => 1,
                'fieldIndex' => 1,
                'type' => 'text',
                'label' => BackendStrings::getTemplateStrings()['candidate_name_or_id'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_candidate_name_or_id'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 1,
            ],
        ];
    }

    /**
     * Get interview outcome fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getInterviewOutcomeFields(): array
    {
        return [
            // Overall Interview Outcome
            [
                'id' => 7,
                'fieldIndex' => 2,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['overall_interview_outcome'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 2,
                'fieldOptions' => [
                    [
                        'id' => 2,
                        'label' => BackendStrings::getTemplateStrings()['very_positive'],
                        'value' => 'very_positive',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 3,
                        'label' => BackendStrings::getTemplateStrings()['mostly_positive'],
                        'value' => 'mostly_positive',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 4,
                        'label' => BackendStrings::getTemplateStrings()['neutral'],
                        'value' => 'neutral',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 5,
                        'label' => BackendStrings::getTemplateStrings()['mostly_negative'],
                        'value' => 'mostly_negative',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 6,
                        'label' => BackendStrings::getTemplateStrings()['very_negative'],
                        'value' => 'very_negative',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ],
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
        return [
            // Overall Candidate Fit Rating
            [
                'id' => 13,
                'fieldIndex' => 3,
                'type' => 'rating',
                'label' => BackendStrings::getTemplateStrings()['overall_candidate_fit'],
                'required' => true,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 3,
                'labelPosition' => 'default',
                'ratingIcon' => 'star',
                'description' => '',
                'fieldOptions' => [
                    [
                        'id' => 8,
                        'label' => BackendStrings::getNewFormStrings()['good'],
                        'value' => '1',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 9,
                        'label' => BackendStrings::getNewFormStrings()['nice'],
                        'value' => '2',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 10,
                        'label' => BackendStrings::getNewFormStrings()['very_good'],
                        'value' => '3',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 11,
                        'label' => BackendStrings::getNewFormStrings()['awesome'],
                        'value' => '4',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 12,
                        'label' => BackendStrings::getNewFormStrings()['amazing'],
                        'value' => '5',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ],
                'showValues' => false,
                'showRatingText' => false,
            ],
        ];
    }

    /**
     * Get key areas fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getKeyAreasFields(): array
    {
        return [
            // Key Areas Observed
            [
                'id' => 19,
                'fieldIndex' => 4,
                'type' => 'checkbox',
                'label' => BackendStrings::getTemplateStrings()['key_areas_observed'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 4,
                'fieldOptions' => [
                    [
                        'id' => 14,
                        'label' => BackendStrings::getTemplateStrings()['communication'],
                        'value' => 'Communication',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 15,
                        'label' => BackendStrings::getTemplateStrings()['problem_solving'],
                        'value' => 'Problem-solving',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 16,
                        'label' => BackendStrings::getTemplateStrings()['technical_or_role_specific_skills'],
                        'value' => 'Technical or role-specific skills',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 17,
                        'label' => BackendStrings::getTemplateStrings()['cultural_fit'],
                        'value' => 'Cultural fit',
                        'isDefault' => false,
                        'position' => 4
                    ],
                    [
                        'id' => 18,
                        'label' => BackendStrings::getTemplateStrings()['professionalism'],
                        'value' => 'Professionalism',
                        'isDefault' => false,
                        'position' => 5
                    ],
                ]
            ],
            // Key Observations
            [
                'id' => 20,
                'fieldIndex' => 5,
                'type' => 'textarea',
                'label' => BackendStrings::getTemplateStrings()['key_observations'],
                'placeholder' => BackendStrings::getTemplateStrings()['key_observations_placeholder'],
                'required' => false,
                'parentId' => null,
                'defaultValue' => '',
                'position' => 5,
                'rows' => 3,
            ],
        ];
    }

    /**
     * Get recommendation fields
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getRecommendationFields(): array
    {
        return [
            // Recommendation
            [
                'id' => 25,
                'fieldIndex' => 6,
                'type' => 'radio',
                'label' => BackendStrings::getTemplateStrings()['recommendation'],
                'required' => true,
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'position' => 6,
                'fieldOptions' => [
                    [
                        'id' => 21,
                        'label' => BackendStrings::getTemplateStrings()['strong_hire'],
                        'value' => 'Strong hire',
                        'isDefault' => false,
                        'position' => 1
                    ],
                    [
                        'id' => 22,
                        'label' => BackendStrings::getTemplateStrings()['hire'],
                        'value' => 'Hire',
                        'isDefault' => false,
                        'position' => 2
                    ],
                    [
                        'id' => 23,
                        'label' => BackendStrings::getTemplateStrings()['consider'],
                        'value' => 'Consider',
                        'isDefault' => false,
                        'position' => 3
                    ],
                    [
                        'id' => 24,
                        'label' => BackendStrings::getTemplateStrings()['do_not_proceed'],
                        'value' => 'Do not proceed',
                        'isDefault' => false,
                        'position' => 4
                    ],
                ]
            ],
        ];
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
                'id' => 26,
                'fieldIndex' => 7,
                'type' => 'name',
                'label' => BackendStrings::getTemplateStrings()['interviewer_name'],
                'parentId' => null,
                'defaultValue' => '',
                'placeholder' => '',
                'required' => false,
                'readonly' => false,
                'position' => 7,
            ],
            // First Name - Child of Name Field
            [
                'id' => 27,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['first_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_first_name'],
                'required' => false,
                'parentId' => 7,
                'fieldIndex' => 7,
                'defaultValue' => '',
                'position' => 1,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField1']),
            ],
            // Last Name - Child of Name Field
            [
                'id' => 28,
                'type' => 'text',
                'label' => BackendStrings::getNewFormStrings()['last_name'],
                'placeholder' => BackendStrings::getTemplateStrings()['enter_your_last_name'],
                'required' => false,
                'parentId' => 7,
                'fieldIndex' => 7,
                'defaultValue' => '',
                'position' => 2,
                'optionHide' => false,
                'description' => '',
                'settings' => json_encode(['nameFieldType' => 'nameField2']),
            ],
        ];
    }
}
