<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class MarketingGrowthLeadershipConferenceRegistrationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        $strings = BackendStrings::getTemplateStrings();

        return [
            'id' => 'marketing_growth_leadership_conference_registration_form',
            'name' => $strings['marketing_growth_leadership_conference_registration_form'],
            'description' => $strings['marketing_growth_leadership_conference_registration_form_desc'],
            'category' => 'event-registration-forms',
            'subcategory' => 'conference-registration-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL
                . 'marketing-growth-leadership-conference-registration-form.svg',
        ];
    }

    /**
     * Get the full template including form data
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        $strings = BackendStrings::getTemplateStrings();

        return array_merge(
            self::getTemplateMeta(),
            [
                'form_data' => [
                    'name' => $strings['marketing_growth_leadership_conference_registration_form'],
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
