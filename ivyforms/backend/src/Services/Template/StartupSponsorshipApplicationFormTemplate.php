<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class StartupSponsorshipApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'startup_sponsorship_application_form',
            'name' => BackendStrings::getTemplateStrings()['startup_sponsorship_application_form'],
            'description' => BackendStrings::getTemplateStrings()['startup_sponsorship_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'sponsorship-application-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'startup-sponsorship-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['startup_sponsorship_application_form'],
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
