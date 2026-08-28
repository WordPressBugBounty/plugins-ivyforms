<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class SportsEventWaiverFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'sports_event_waiver_form',
            'name' => BackendStrings::getTemplateStrings()['sports_event_waiver_form'],
            'description' => BackendStrings::getTemplateStrings()['sports_event_waiver_form_desc'],
            'category' => 'waiver-forms',
            'subcategory' => 'liability-waiver-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'sports-event-liability-waiver-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['sports_event_waiver_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => [],
                'settings' => []
            ]
            ]
        );
    }
}
