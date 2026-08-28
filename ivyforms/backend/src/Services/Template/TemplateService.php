<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Template;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class TemplateService
 *
 * @package IvyForms\Services\Template
 */
class TemplateService
{
    /**
     * Get all template metadata (without form_data) for listing
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllTemplateMetas(): array
    {
        return TemplateMapper::getAllTemplateMetas();
    }

    /**
     * Get predefined form template by ID
     *
     * @param  string $templateId
     * @return array<string, mixed>|null
     */
    public function getTemplateById(string $templateId): ?array
    {
        return TemplateMapper::getTemplate($templateId);
    }

    /**
     * Get form data from template by ID
     *
     * @param  string $templateId
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public function getFormDataFromTemplate(string $templateId): array
    {
        $template = $this->getTemplateById($templateId);

        if (!$template) {
            throw new InvalidArgumentException(
                BackendStrings::getTemplateStrings()['template_not_found']
            );
        }

        $formData = $template['form_data'];

        // Assign sequential rowIndex to each field ONLY if not already specified
        // This ensures templates without layout get single-column, but templates with
        // custom multi-column layouts are preserved
        if (isset($formData['fields']) && is_array($formData['fields'])) {
            $formData['fields'] = $this->assignFieldLayout($formData['fields']);
        }

        return $formData;
    }

    /**
     * Assign layout properties to fields
     *
     * @param  array<int, array<string, mixed>> $fields
     * @return array<int, array<string, mixed>>
     */
    private function assignFieldLayout(array $fields): array
    {
        $rowCounter = 0;
        foreach ($fields as &$field) {
            if ($this->shouldAssignLayout($field)) {
                $field['rowIndex'] = $rowCounter;
                $field['columnIndex'] = 0;
                $field['width'] = 100;
                $rowCounter++;
                continue;
            }

            if ($this->hasCustomLayout($field)) {
                $rowCounter = max($rowCounter, $field['rowIndex'] + 1);
            }
        }
        unset($field);
        return $fields;
    }

    /**
     * Check if field should get default layout
     *
     * @param  array<string, mixed> $field
     * @return bool
     */
    private function shouldAssignLayout(array $field): bool
    {
        return !isset($field['parentId'])
            && !isset($field['rowIndex'])
            && !isset($field['columnIndex'])
            && !isset($field['width']);
    }

    /**
     * Check if field has custom layout
     *
     * @param  array<string, mixed> $field
     * @return bool
     */
    private function hasCustomLayout(array $field): bool
    {
        return !isset($field['parentId']) && isset($field['rowIndex']);
    }

    /**
     * Get template categories
     *
     * @return array<string, array<string, string>>
     */
    public function getTemplateCategories(): array
    {
        return [
        //            'basic' => [
        //                'name' => '',
        //                'description' => ''
        //            ]
        ];
    }
}
