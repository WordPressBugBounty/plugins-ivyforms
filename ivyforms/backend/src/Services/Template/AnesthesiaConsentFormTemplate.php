<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class AnesthesiaConsentFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'anesthesia_consent_form',
            'name' => BackendStrings::getTemplateStrings()['anesthesia_consent_form'],
            'description' => BackendStrings::getTemplateStrings()['anesthesia_consent_form_desc'],
            'category' => 'consent-forms',
            'subcategory' => 'medical-consent-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'anesthesia-consent-form.svg'
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
                'name' => BackendStrings::getTemplateStrings()['anesthesia_consent_form'],
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
