<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class UiUxDesignerFreelancerApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'ui_ux_designer_freelancer_application_form',
            'name' => BackendStrings::getTemplateStrings()['ui_ux_designer_freelancer_application_form'],
            'description' => BackendStrings::getTemplateStrings()['ui_ux_designer_freelancer_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'freelancer-application-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'ui-ux-designer-freelancer-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['ui_ux_designer_freelancer_application_form'],
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
