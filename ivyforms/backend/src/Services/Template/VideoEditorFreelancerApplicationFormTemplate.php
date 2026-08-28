<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class VideoEditorFreelancerApplicationFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'video_editor_freelancer_application_form',
            'name' => BackendStrings::getTemplateStrings()['video_editor_freelancer_application_form'],
            'description' => BackendStrings::getTemplateStrings()['video_editor_freelancer_application_form_desc'],
            'category' => 'application-forms',
            'subcategory' => 'freelancer-application-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'video-editor-freelancer-application-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['video_editor_freelancer_application_form'],
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
