<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class BusinessVendorOnboardingFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'business_vendor_onboarding_form',
            'name' => BackendStrings::getTemplateStrings()['business_vendor_onboarding_form'],
            'description' => BackendStrings::getTemplateStrings()['business_vendor_onboarding_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'vendor-application-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'business-vendor-onboarding-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['business_vendor_onboarding_form'],
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
