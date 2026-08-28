<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP\Abilities;

use IvyForms\Common\Helpers\McpHelpers\McpAbilitiesHelper;
use IvyForms\Infrastructure\WP\MCP\McpAbilityPermissions;

/**
 * Registers IvyForms MCP abilities for this domain.
 */
class IvyFormsTemplatesAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerListTemplates();
        self::registerGetTemplate();
    }

    private static function registerListTemplates(): void
    {
        wp_register_ability('ivyforms/list-templates', [
            'label'       => __('List Templates', 'ivyforms'),
            'description' => __(
                'Get all available IvyForms form templates including metadata such as id, name, description,
                 category, and pro availability flag. ' .

                'WHEN TO USE: ' .
                '- Use when the user asks what templates exist, available form starters, 
                template library, or template options. ' .
                '- Use when creating a new form from a predefined structure 
                (e.g. contact form, blank form, survey form). ' .
                '- Use when the user is browsing or comparing available form layouts before creation. ' .

                'WHAT IT RETURNS: ' .
                '- Template ID (unique identifier such as "blank_form", "contact_form") ' .
                '- Template name ' .
                '- Template description explaining purpose and use-case ' .
                '- Category grouping (e.g. business, contact, survey) ' .
                '- Pro flag indicating if template requires premium version ' .

                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY discovery endpoint (no modifications). ' .
                '2. Do NOT use this for creating forms; use create-form with templateId instead. ' .
                '3. Do NOT assume template fields are included in this response; 
                use get-template for full structure. ' .
                '4. Results may be partial or grouped depending on system configuration. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Use this as the entry point for form creation workflows. ' .
                '- After selection, pass templateId directly to create-form. ' .
                '- Prefer this over guessing template IDs. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT confuse templates with actual forms (use get-forms for forms). ' .
                '- Do NOT attempt to render full form structure from this endpoint. ' .
                '- Do NOT use this to modify templates.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [],
            ],
            'output_schema' => ['type' => 'array'],
            'execute_callback' => function () {
                $data      = McpAbilitiesHelper::restRequest('GET', '/templates');
                $templates = $data['data']['data']['templates']
                    ?? $data['data']['templates']
                    ?? $data['templates']
                    ?? [];

                if (is_array($templates) && !isset($templates[0])) {
                    // Convert keyed object to indexed array
                    $templates = array_map(
                        function ($id, $meta) {
                            return array_merge(['id' => $id], is_array($meta) ? $meta : []);
                        },
                        array_keys($templates),
                        array_values($templates)
                    );
                }

                return $templates;
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/list-templates');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerGetTemplate(): void
    {
        wp_register_ability('ivyforms/get-template', [
            'label'       => __('Get Template', 'ivyforms'),
            'description' => __(
                'Retrieve complete IvyForms template definition by template ID, 
                including all structure, fields, and configuration used to generate forms. ' .

                'WHEN TO USE: ' .
                '- Use when the user selects a specific template and wants to inspect its structure
                 before creating a form. ' .
                '- Use when previewing or explaining what a template contains (fields, layout, configuration). ' .
                '- Use when debugging or validating template-based form creation. ' .

                'WHAT IT RETURNS: ' .
                '- Full template object including metadata (id, name, description) ' .
                '- Complete field structure and layout definition ' .
                '- Configuration required to generate a form from this template ' .
                '- Internal template schema used by the form builder ' .

                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY detailed fetch operation. ' .
                '2. Requires a valid templateId (e.g. blank_form, contact_form). ' .
                '3. Do NOT use this for listing templates; use list-templates instead. ' .
                '4. Response may be large because it includes full structure definition. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Use this only after a template is selected or explicitly requested. ' .
                '- Prefer list-templates for discovery and this for deep inspection. ' .
                '- Pass output directly into form creation workflows when needed. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT call this without a known templateId. ' .
                '- Do NOT use this as a substitute for form data (it is not a live form). ' .
                '- Do NOT assume template equals editable form instance.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['templateId'],
                'properties' => [
                    'templateId' => [
                        'type' => 'string',
                        'description' => 'Template ID (e.g. blank_form, contact_form)',
                    ],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => function (array $input) {
                $templateId = sanitize_text_field($input['templateId'] ?? '');
                if ($templateId === '') {
                    return ['error' => __('A valid template ID is required.', 'ivyforms')];
                }

                $data = McpAbilitiesHelper::restRequest('GET', '/template/' . $templateId);
                return $data['data']['data'] ?? $data['data'] ?? $data;
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-template');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }
}
