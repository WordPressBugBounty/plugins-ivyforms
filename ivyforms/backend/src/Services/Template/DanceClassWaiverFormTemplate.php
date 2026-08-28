<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class DanceClassWaiverFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'dance_class_waiver_form',
            'name' => BackendStrings::getTemplateStrings()['dance_class_waiver_form'],
            'description' => BackendStrings::getTemplateStrings()['dance_class_waiver_form_desc'],
            'category' => 'waiver-forms',
            'subcategory' => 'liability-waiver-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'dance-class-liability-waiver-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['dance_class_waiver_form'],
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
