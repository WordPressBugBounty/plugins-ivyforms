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
class IvyFormsNavigationAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerOpenFormBuilder();
        self::registerOpenEntry();
    }

    private static function registerOpenFormBuilder(): void
    {
        wp_register_ability('ivyforms/open-form-builder', [
            'label'       => __('Open Form Builder', 'ivyforms'),
            'description' => __(
                'Returns the admin URL to open a specific form in the IvyForms builder. ' .
                'Use when the user asks to open, edit, or navigate to a form, its settings, or its results. ' .
                'Returns an adminLink the user can click to navigate to the form. ' .
                'Section values: "settings/general" for general settings, ' .
                '"settings/notification" (alias: "settings/notifications") for notifications, ' .
                '"settings/integrations" for integrations, "results/entries" for form entries/submissions.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId'],
                'properties' => [
                    'formId'  => ['type' => 'integer', 'description' => 'Form ID to open in builder'],
                    'section' => [
                        'type'        => 'string',
                        'description' => 'Optional section: "settings/general", ' .
                            '"settings/notification" (alias "settings/notifications"), ' .
                            '"settings/integrations", "results/entries"',
                    ],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'formId'    => ['type' => 'integer'],
                    'adminLink' => [
                        'type' => 'string',
                        'description' => 'Admin URL — open this link to navigate to the form',
                    ],
                    'section'   => ['type' => ['string', 'null']],
                ],
            ],
            'execute_callback' => function (array $input) {
                $formId  = (int) ($input['formId'] ?? 0);
                $section = sanitize_text_field($input['section'] ?? '');

                $normalized = ($section === 'settings/notifications') ? 'settings/notification' : $section;

                $hash = '/manage/' . $formId;
                if ($normalized) {
                    $hash .= '/' . ltrim($normalized, '/');
                }

                return [
                    'formId'    => $formId,
                    'adminLink' => admin_url('admin.php?page=ivyforms-builder#' . $hash),
                    'section'   => $section ?: null,
                ];
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/open-form-builder');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. Use `ivyforms/open-form-builder` 
                    to navigate to a form. For `section`, use: `settings/general`, `settings/notification` 
                    (alias `settings/notifications`), `settings/integrations`, or `results/entries`. 
                    For after-submission confirmation management, navigate to the relevant 
                    notification/confirmation area via `settings/notification` when no dedicated confirmation 
                    tool is available.',
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerOpenEntry(): void
    {
        wp_register_ability('ivyforms/open-entry', [
            'label'       => __('Open Entry', 'ivyforms'),
            'description' => __(
                'Returns the admin URL to open a specific entry/submission details page. ' .
                'Use when the user asks to open, view, or go to a specific entry. ' .
                'Returns an adminLink the user can click to navigate to the entry.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['entryId'],
                'properties' => [
                    'entryId' => ['type' => 'integer', 'description' => 'Entry ID to open'],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'entryId'   => ['type' => 'integer'],
                    'adminLink' => [
                        'type' => 'string',
                        'description' => 'Admin URL — open this link to navigate to the entry',
                    ],
                ],
            ],
            'execute_callback' => function (array $input) {
                $entryId = (int) ($input['entryId'] ?? 0);
                return [
                    'entryId'   => $entryId,
                    'adminLink' => McpAbilitiesHelper::adminEntryUrl($entryId),
                ];
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/open-entry');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. Use `ivyforms/open-entry` to navigate to a specific
                     entry. When listing entries, render each returned entry using its `adminLink` as a clickable 
                     markdown link.',
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }
}
