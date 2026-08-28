<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP\Abilities;

use IvyForms\Common\Helpers\McpHelpers\McpAbilitiesHelper;
use IvyForms\Common\Helpers\McpHelpers\McpResponseRedactor;
use IvyForms\Infrastructure\WP\MCP\McpAbilityPermissions;

/**
 * Registers IvyForms MCP abilities for this domain.
 */
class IvyFormsSettingsAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerGetSettings();
        self::registerGetSetting();
    }

    private static function registerGetSettings(): void
    {
        wp_register_ability('ivyforms/get-settings', [
            'label'       => __('Get Settings', 'ivyforms'),
            'description' => __(
                'Get all IvyForms plugin settings in a single aggregated response. ' .
                'This ability provides a complete snapshot of the plugin configuration grouped by categories. ' .

                'WHEN TO USE: ' .
                '- Use when the user asks for settings overview, configuration summary, system settings,
                 or plugin configuration state. ' .
                '- Use when the user wants to audit, inspect, or understand current IvyForms setup. ' .
                '- Use as a first step before updating or debugging settings. ' .

                'WHAT IT RETURNS: ' .
                '- Complete settings object grouped by categories ' .
                '- All available configuration keys and values ' .

                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY operation (no changes are made). ' .
                '2. Do NOT use this to update or modify settings; settings writes are not available via MCP. ' .
                '3. Do NOT assume structure beyond returned payload. ' .
                '4. Response may contain sensitive configuration values; handle carefully. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Use this as the entry point for debugging or configuration inspection. ' .
                '- Prefer this over multiple get-setting calls when global context is needed. ' .
                '- Do not attempt partial updates from this response. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT treat this as a single-setting lookup (use get-setting for that). ' .
                '- Do NOT attempt to infer missing settings not returned in payload. ' .
                '- Do NOT modify values based on this response.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function () {
                $data    = McpAbilitiesHelper::restRequest('GET', '/settings/all');
                $payload = $data['data'] ?? $data;

                return is_array($payload) ? McpResponseRedactor::redactSettings($payload) : $payload;
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-settings');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. Use `ivyforms/get-settings` to retrieve all
                     plugin settings, then explain relevant values in the response.',
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerGetSetting(): void
    {
        wp_register_ability('ivyforms/get-setting', [
            'label'       => __('Get Setting', 'ivyforms'),
            'description' => __(
                'Retrieve a single IvyForms plugin setting by specifying its category and option key. ' .
                'This ability is designed for precise, targeted access to configuration values. ' .

                'WHEN TO USE: ' .
                '- Use when the user asks for a specific setting value (e.g. email config, API key, feature toggle). ' .
                '- Use when debugging a specific behavior tied to one configuration option. ' .
                '- Use when the user needs the current value before changing settings in the WordPress admin. ' .

                'WHAT IT RETURNS: ' .
                '- Single setting value for the requested category/option pair ' .
                '- Raw configuration value as stored in the system ' .

                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY operation. ' .
                '2. Requires BOTH category and option; partial queries are invalid. ' .
                '3. Do NOT use for listing or browsing settings; use get-settings instead. ' .
                '4. Output is minimal and scoped to a single configuration entry. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Always validate that category and option are explicitly known before calling. ' .
                '- If user intent is unclear or broad, fallback to get-settings instead. ' .
                '- Use before directing the user to change settings in the WordPress admin when needed. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT use this to enumerate all settings. ' .
                '- Do NOT guess category/option names. ' .
                '- Do NOT treat partial matches as valid inputs.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['category', 'option'],
                'properties' => [
                    'category' => ['type' => 'string', 'description' => 'Settings category slug'],
                    'option'   => ['type' => 'string', 'description' => 'Option key within the category'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function (array $input) {
                $category = sanitize_text_field($input['category'] ?? '');
                $option   = sanitize_text_field($input['option'] ?? '');
                if ($category === '' || $option === '') {
                    return ['error' => __('Category and option are required.', 'ivyforms')];
                }

                $data    = McpAbilitiesHelper::restRequest(
                    'GET',
                    '/setting/' . rawurlencode($category) . '/' . rawurlencode($option)
                );
                $payload = $data['data'] ?? $data;

                if (is_array($payload)) {
                    return McpResponseRedactor::redactSettings($payload);
                }

                return McpResponseRedactor::redactSettings([
                    'category' => $category,
                    'option'   => $option,
                    'value'    => $payload,
                ]);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-setting');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. Use `ivyforms/get-setting` with the provided
                     `category` and `option` to fetch one specific plugin setting.',
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }
}
