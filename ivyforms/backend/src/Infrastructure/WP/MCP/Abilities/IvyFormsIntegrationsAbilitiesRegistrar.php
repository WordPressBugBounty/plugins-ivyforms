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
class IvyFormsIntegrationsAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerListIntegrations();
        self::registerGetIntegration();
    }

    private static function registerListIntegrations(): void
    {
        wp_register_ability('ivyforms/list-integrations', [
            'label'       => __('List Integrations', 'ivyforms'),
            'description' => __(
                'Retrieve a list of all available IvyForms integrations. ' .
                'This ability is strictly for discovery and overview of integrations and does NOT return 
                configuration details for a specific integration. ' .

                'WHEN TO USE: ' .
                '- Use when the user asks which integrations are available, what integrations exist,
                 list/show integrations, or supported third-party services. ' .
                '- Use for marketplace views, onboarding, or integration selection flows. ' .
                '- Optionally use when filtering by plan tier (e.g. free/pro/enterprise). ' .

                'WHAT IT RETURNS: ' .
                '- Integration slug (identifier) ' .
                '- Integration name ' .
                '- Availability or plan tier (if applicable) ' .
                '- Basic metadata required for listing and selection ' .

                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY discovery endpoint. No configuration data is modified. ' .
                '2. Do NOT use this for fetching details of a specific integration; use get-integration instead. ' .
                '3. Do NOT assume returned integrations are already configured for the user. ' .
                '4. Do NOT use this for enabling, disabling, or modifying integrations. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Treat this as the starting point for integration selection workflows. ' .
                '- If user selects a specific integration, immediately switch to get-integration. ' .
                '- Use slug values as stable identifiers for follow-up calls. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT confuse this with get-integration (single integration details). ' .
                '- Do NOT assume credentials or settings are included. ' .
                '- Do NOT attempt to configure integrations from this ability.',
                'ivyforms'
            ),            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'plan' => ['type' => 'string', 'description' => 'Optional: filter by plan tier'],
                ],
            ],
            'output_schema' => ['type' => 'array'],
            'execute_callback' => function (array $input) {
                $params = [];
                if (!empty($input['plan'])) {
                    $params['plan'] = sanitize_text_field($input['plan']);
                }

                $data = McpAbilitiesHelper::restRequest('GET', '/integrations', $params);
                $integrations = McpAbilitiesHelper::extractIvyFormsRestPayload($data);

                return is_array($integrations) ? array_values($integrations) : [];
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/list-integrations');
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

    private static function registerGetIntegration(): void
    {
        wp_register_ability('ivyforms/get-integration', [
            'label'       => __('Get Integration', 'ivyforms'),
            'description' => __(
                'Retrieve detailed information about a specific IvyForms integration using its slug. ' .
                'This ability returns full integration metadata and configuration schema where applicable. ' .

                'WHEN TO USE: ' .
                '- Use ONLY when a specific integration slug is known (e.g. mailchimp, zapier, slack). ' .
                '- Use when the user asks for details, configuration, setup, 
                or capabilities of a single integration. ' .

                'WHAT IT RETURNS: ' .
                '- Integration slug and name ' .
                '- Configuration requirements (API keys, tokens, etc.) ' .
                '- Supported features and capabilities ' .
                '- Setup instructions or metadata (if available) ' .

                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY detailed lookup operation. ' .
                '2. Do NOT use for listing all integrations; use list-integrations instead. ' .
                '3. This does NOT enable or connect integrations automatically. ' .
                '4. This only provides metadata and configuration structure. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Use this after selection from list-integrations. ' .
                '- Treat this as prerequisite before any integration setup workflow. ' .
                '- If user wants to connect an integration, this is the first step before configuration actions. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT assume integration is active or connected. ' .
                '- Do NOT perform configuration changes from this ability. ' .
                '- Do NOT use for listing multiple integrations.',
                'ivyforms'
            ),            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['slug'],
                'properties' => [
                    'slug' => ['type' => 'string', 'description' => 'Integration slug (e.g. mailchimp, zapier)'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => function (array $input) {
                $slug = sanitize_text_field($input['slug'] ?? '');
                if ($slug === '') {
                    return ['error' => __('A valid slug is required.', 'ivyforms')];
                }

                $data = McpAbilitiesHelper::restRequest('GET', '/integrations/' . $slug);
                return $data['data'] ?? $data;
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-integration');
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
