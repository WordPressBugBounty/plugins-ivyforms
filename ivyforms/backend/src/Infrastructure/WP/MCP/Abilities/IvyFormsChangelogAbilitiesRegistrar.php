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
class IvyFormsChangelogAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerGetChangelog();
    }

    private static function registerGetChangelog(): void
    {
        wp_register_ability('ivyforms/get-changelog', [
            'label'       => __('Get Changelog', 'ivyforms'),
            'description' => __(
                'Retrieve the IvyForms changelog, including latest version updates, features, improvements, 
                and bug fixes.

                Use this ability ONLY when the user explicitly asks for:
                - changelog
                - release notes
                - what changed in IvyForms
                - version updates or update history

                Do NOT use this ability for:
                - plugin settings
                - documentation
                - feature explanation unrelated to versions
                - support/debugging issues

                This is a READ-ONLY informational endpoint and does not modify any data.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => function () {
                $data = McpAbilitiesHelper::restRequest('GET', '/changelog');
                return $data['data'] ?? $data;
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-changelog');
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
