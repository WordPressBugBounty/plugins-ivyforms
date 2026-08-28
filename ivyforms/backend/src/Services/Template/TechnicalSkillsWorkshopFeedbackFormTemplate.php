<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class TechnicalSkillsWorkshopFeedbackFormTemplate
{
    /**
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'technical_skills_workshop_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['technical_skills_workshop_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['technical_skills_workshop_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'training-feedback-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'technical-skills-workshop-feedback-form.svg',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return array_merge(
            self::getTemplateMeta(),
            [
                'form_data' => [
                    'name' => BackendStrings::getTemplateStrings()['technical_skills_workshop_feedback_form'],
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
