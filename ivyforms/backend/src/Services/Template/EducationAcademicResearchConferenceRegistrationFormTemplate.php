<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class EducationAcademicResearchConferenceRegistrationFormTemplate
{
    /**
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        $strings = BackendStrings::getTemplateStrings();

        return [
            'id' => 'education_academic_research_conference_registration_form',
            'name' => $strings['education_academic_research_conference_registration_form'],
            'description' => $strings['education_academic_research_conference_registration_form_desc'],
            'category' => 'event-registration-forms',
            'subcategory' => 'conference-registration-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL
                . 'education-academic-research-conference-registration-form.svg',
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
                    'name' => $strings['education_academic_research_conference_registration_form'],
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
