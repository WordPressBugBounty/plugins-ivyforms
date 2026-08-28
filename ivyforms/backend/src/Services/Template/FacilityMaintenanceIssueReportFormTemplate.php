<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class FacilityMaintenanceIssueReportFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'facility_maintenance_issue_report_form',
            'name' => BackendStrings::getTemplateStrings()['facility_maintenance_issue_report_form'],
            'description' => BackendStrings::getTemplateStrings()['facility_maintenance_issue_report_form_desc'],
            'category' => 'order-forms',
            'subcategory' => 'work-order-forms',
            'is_pro' => true,
            'required_plan' => 'essentials',
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'facility-maintenance-issue-report-form.svg',
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
                    'name' => BackendStrings::getTemplateStrings()['facility_maintenance_issue_report_form'],
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
