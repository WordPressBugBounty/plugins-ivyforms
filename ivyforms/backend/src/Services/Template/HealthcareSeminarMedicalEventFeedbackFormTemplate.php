<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class HealthcareSeminarMedicalEventFeedbackFormTemplate
{
    /**
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        $strings = BackendStrings::getTemplateStrings();

        return [
            'id' => 'healthcare_seminar_medical_event_feedback_form',
            'name' => $strings['healthcare_seminar_medical_event_feedback_form'],
            'description' => $strings['healthcare_seminar_medical_event_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'event-feedback-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL
                . 'healthcare-seminar-medical-event-feedback-form.svg',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        $strings = BackendStrings::getTemplateStrings();

        return array_merge(
            self::getTemplateMeta(),
            [
                'form_data' => [
                    'name' => $strings['healthcare_seminar_medical_event_feedback_form'],
                    'published' => 1,
                    'showTitle' => 1,
                    'storeEntries' => 1,
                    'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                    'fields' => [],
                    'settings' => [],
                ],
            ]
        );
    }
}
