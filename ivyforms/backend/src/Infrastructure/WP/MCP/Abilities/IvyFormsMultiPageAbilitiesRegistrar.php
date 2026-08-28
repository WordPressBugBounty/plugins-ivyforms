<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP\Abilities;

use IvyForms\Common\Helpers\McpHelpers\McpMultiPageHelper;
use IvyForms\Infrastructure\WP\MCP\McpAbilityPermissions;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Registers IvyForms MCP abilities for multipage forms (Pro).
 */
class IvyFormsMultiPageAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerSetupMultipageForm();
        self::registerAddFormPage();
    }

    private static function registerSetupMultipageForm(): void
    {
        $strings = BackendStrings::getMcpMultipageStrings();

        wp_register_ability('ivyforms/setup-multipage-form', [
            'label'       => $strings['setup_multipage_form_label'],
            'description' => $strings['setup_multipage_form_description'],
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId', 'pages'],
                'properties' => [
                    'formId' => [
                        'type'        => 'integer',
                        'description' => 'Form ID to configure as multipage',
                    ],
                    'pages'  => [
                        'type'        => 'array',
                        'description' => 'Ordered page definitions (min 1). ' .
                            'IDs normalize to page_1, page_2, … on save.',
                        'items'       => [
                            'type'       => 'object',
                            'required'   => ['label'],
                            'properties' => [
                                'label'    => ['type' => 'string', 'description' => 'Page title shown in the builder'],
                                'closable' => [
                                    'type'        => 'boolean',
                                    'description' => 'Whether the page can be removed in the builder ' .
                                        '(first page is always non-closable)',
                                ],
                                'id'       => [
                                    'type'        => 'string',
                                    'description' => 'Optional temporary page id; backend normalizes to page_N',
                                ],
                            ],
                        ],
                    ],
                    'progressIndicator' => [
                        'type'        => 'object',
                        'description' => 'Optional progress indicator settings',
                        'properties'  => [
                            'type'                => [
                                'type' => 'string',
                                'description' => 'progress-bar, tabs, rootline, or none',
                            ],
                            'showPageTitles'      => ['type' => 'boolean'],
                            'hidePageNumbers'     => ['type' => 'boolean'],
                            'hideConnectingLines' => ['type' => 'boolean'],
                        ],
                    ],
                    'fieldAssignments' => [
                        'type'        => 'array',
                        'description' => 'Optional mapping of existing field IDs to page IDs (e.g. page_2)',
                        'items'       => [
                            'type'       => 'object',
                            'required'   => ['fieldId', 'pageId'],
                            'properties' => [
                                'fieldId' => ['type' => 'integer'],
                                'pageId'  => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'formId'    => ['type' => 'integer'],
                    'success'   => ['type' => 'boolean'],
                    'message'   => ['type' => 'string'],
                    'pages'     => ['type' => 'array'],
                    'adminLink' => ['type' => 'string'],
                ],
            ],
            'execute_callback' => static function (array $input) {
                return McpMultiPageHelper::setupMultipageForm($input);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/setup-multipage-form');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerAddFormPage(): void
    {
        $strings = BackendStrings::getMcpMultipageStrings();

        wp_register_ability('ivyforms/add-form-page', [
            'label'       => $strings['add_form_page_label'],
            'description' => $strings['add_form_page_description'],
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId', 'label'],
                'properties' => [
                    'formId' => ['type' => 'integer', 'description' => 'Form ID'],
                    'label'  => ['type' => 'string', 'description' => 'Label for the new page'],
                    'insertAfterPageId' => [
                        'type'        => 'string',
                        'description' => 'Optional page id to insert after (e.g. page_1). Omit to append.',
                    ],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'formId'    => ['type' => 'integer'],
                    'success'   => ['type' => 'boolean'],
                    'message'   => ['type' => 'string'],
                    'pages'     => ['type' => 'array'],
                    'addedPage' => ['type' => 'object'],
                    'adminLink' => ['type' => 'string'],
                ],
            ],
            'execute_callback' => static function (array $input) {
                return McpMultiPageHelper::addFormPage($input);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/add-form-page');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ],
            ],
        ]);
    }
}
