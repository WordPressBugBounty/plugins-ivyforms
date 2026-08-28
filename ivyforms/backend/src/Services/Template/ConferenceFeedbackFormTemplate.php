<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class ConferenceFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'conference_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['conference_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['conference_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'event-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'conference-feedback-form.svg',
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
                'name' => BackendStrings::getTemplateStrings()['conference_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getSessionsAttendedField(),
                    self::getSpeakerQualityRatingField(),
                    self::getVenueLogisticsRatingField(),
                    self::getOverallOrganizationDropdownField(),
                    self::getImprovementTextareaField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Checkbox: Sessions attended
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSessionsAttendedField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 1,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['sessions_attended'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 1,
            'fieldOptions' => [
                [
                    'id' => 1,
                    'label' => BackendStrings::getTemplateStrings()['keynote_sessions'],
                    'value' => 'keynote_sessions',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 2,
                    'label' => BackendStrings::getTemplateStrings()['technical_sessions'],
                    'value' => 'technical_sessions',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 3,
                    'label' => BackendStrings::getTemplateStrings()['panel_discussions'],
                    'value' => 'panel_discussions',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 4,
                    'label' => BackendStrings::getTemplateStrings()['workshops_sessions'],
                    'value' => 'workshops',
                    'isDefault' => false,
                    'position' => 4,
                ],
                [
                    'id' => 5,
                    'label' => BackendStrings::getTemplateStrings()['networking_sessions'],
                    'value' => 'networking_sessions',
                    'isDefault' => false,
                    'position' => 5,
                ],
            ]
        ]];
    }

    /**
     * Rating: Speaker quality
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getSpeakerQualityRatingField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 2,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['speaker_quality'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(1),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Rating: Venue & logistics
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getVenueLogisticsRatingField(): array
    {
        return [[
            'id' => 8,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['venue_and_logistics'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(6),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Rating: Overall conference organization
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOverallOrganizationDropdownField(): array
    {
        return [[
            'id' => 13,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['overall_conference_organization'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => [
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['organization_poor'],
                    'value' => 'poor',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['organization_average'],
                    'value' => 'average',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['organization_good'],
                    'value' => 'good',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 12,
                    'label' => BackendStrings::getTemplateStrings()['organization_excellent'],
                    'value' => 'excellent',
                    'isDefault' => false,
                    'position' => 4,
                ],
            ],
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Paragraph: What could be improved?
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getImprovementTextareaField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 5,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['what_could_be_improved'],
            'placeholder' => BackendStrings::getTemplateStrings()['share_what_could_be_improved'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 5,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * Standard rating options (1-5)
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
                'position' => 1,
            ],
            [
                'id' => $startId + 1,
                'label' => BackendStrings::getNewFormStrings()['nice'],
                'value' => '2',
                'isDefault' => false,
                'position' => 2,
            ],
            [
                'id' => $startId + 2,
                'label' => BackendStrings::getNewFormStrings()['very_good'],
                'value' => '3',
                'isDefault' => false,
                'position' => 3,
            ],
            [
                'id' => $startId + 3,
                'label' => BackendStrings::getNewFormStrings()['awesome'],
                'value' => '4',
                'isDefault' => false,
                'position' => 4,
            ],
            [
                'id' => $startId + 4,
                'label' => BackendStrings::getNewFormStrings()['amazing'],
                'value' => '5',
                'isDefault' => false,
                'position' => 5,
            ],
        ];
    }
}
