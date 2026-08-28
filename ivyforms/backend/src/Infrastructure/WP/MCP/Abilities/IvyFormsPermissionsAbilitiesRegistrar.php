<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP\Abilities;

use IvyForms\Common\Helpers\McpHelpers\McpPermissionsHelper;
use IvyForms\Infrastructure\WP\MCP\McpAbilityPermissions;
use WP_Error;

/**
 * Registers administrator-only MCP abilities for managing IvyForms permissions
 * (the access rules behind Settings → Permissions).
 *
 * All abilities here are gated by {@see McpAbilityPermissions::canExecuteAbility()}, which
 * requires administrator-level access for permission-management abilities.
 */
class IvyFormsPermissionsAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerGetAccessRules();
        self::registerListPermissionKeys();
        self::registerGrantRolePermission();
        self::registerRevokeRolePermission();
        self::registerGrantUserPermission();
        self::registerRevokeUserPermission();
    }

    private static function registerGetAccessRules(): void
    {
        wp_register_ability('ivyforms/get-access-rules', [
            'label'       => __('Get Access Rules', 'ivyforms'),
            'description' => __(
                'List the current IvyForms permission (access) rules that grant roles and users '
                . 'access to forms, entries, and settings. READ-ONLY. Administrator access required. '
                . 'Use this before granting or revoking permissions so you know the current state.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [],
            ],
            'output_schema' => ['type' => 'array'],
            'execute_callback' => static function () {
                $rules = McpPermissionsHelper::currentRules();
                if ($rules === null) {
                    return new WP_Error(
                        'access_rules_read_failed',
                        __('Could not read the current access rules.', 'ivyforms'),
                        ['status' => 500]
                    );
                }

                return $rules;
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-access-rules');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => ['public' => true, 'type' => 'tool'],
                'annotations'  => ['readonly' => true, 'destructive' => false, 'idempotent' => true],
            ],
        ]);
    }

    private static function registerListPermissionKeys(): void
    {
        wp_register_ability('ivyforms/list-permission-keys', [
            'label'       => __('List Permission Keys', 'ivyforms'),
            'description' => __(
                'List the available IvyForms permission keys (with labels) and the WordPress roles '
                . 'they can be assigned to. READ-ONLY. Administrator access required. '
                . 'Use this to discover valid permission keys before granting/revoking.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function () {
                return McpPermissionsHelper::permissionMetadata();
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/list-permission-keys');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => ['public' => true, 'type' => 'tool'],
                'annotations'  => ['readonly' => true, 'destructive' => false, 'idempotent' => true],
            ],
        ]);
    }

    private static function registerGrantRolePermission(): void
    {
        wp_register_ability('ivyforms/grant-role-permission', [
            'label'       => __('Grant Role Permission', 'ivyforms'),
            'description' => __(
                'Grant one or more IvyForms permissions to a WordPress role. Administrator access required. '
                . 'Optionally limit the grant to specific form IDs (omit formIds to grant globally). '
                . 'Valid permission keys: view_forms_list, add_edit_forms, delete_forms, '
                . 'access_settings_page, view_entries_admin, delete_entries_admin. '
                . 'Use list-permission-keys to discover keys/roles and get-access-rules to review the result.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['roleSlug', 'permissions'],
                'properties' => [
                    'roleSlug'    => [
                        'type'        => 'string',
                        'description' => __('Role slug, e.g. subscriber, editor', 'ivyforms'),
                    ],
                    'permissions' => [
                        'type'        => 'array',
                        'items'       => ['type' => 'string'],
                        'description' => __('Permission keys to grant', 'ivyforms'),
                    ],
                    'formIds'     => [
                        'type'        => 'array',
                        'items'       => ['type' => 'integer'],
                        'description' => __('Optional: limit the grant to these form IDs', 'ivyforms'),
                    ],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function (array $input) {
                $roleSlug    = sanitize_text_field((string) ($input['roleSlug'] ?? ''));
                $permissions = self::stringList($input['permissions'] ?? []);
                $formIds     = self::optionalFormIds($input['formIds'] ?? null);

                return McpPermissionsHelper::grantRole($roleSlug, $permissions, $formIds);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/grant-role-permission');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => ['public' => true, 'type' => 'tool'],
                'annotations'  => ['readonly' => false, 'destructive' => false, 'idempotent' => true],
            ],
        ]);
    }

    private static function registerRevokeRolePermission(): void
    {
        wp_register_ability('ivyforms/revoke-role-permission', [
            'label'       => __('Revoke Role Permission', 'ivyforms'),
            'description' => __(
                'Revoke one or more IvyForms permissions from a WordPress role. Administrator access required. '
                . 'Optionally limit the revoke to specific form IDs (omit formIds to revoke from global rules only). '
                . 'If a role rule has no permissions left afterwards it is removed entirely. '
                . 'Use get-access-rules to review the current grants first.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['roleSlug', 'permissions'],
                'properties' => [
                    'roleSlug'    => [
                        'type'        => 'string',
                        'description' => __('Role slug, e.g. subscriber, editor', 'ivyforms'),
                    ],
                    'permissions' => [
                        'type'        => 'array',
                        'items'       => ['type' => 'string'],
                        'description' => __('Permission keys to revoke', 'ivyforms'),
                    ],
                    'formIds'     => [
                        'type'        => 'array',
                        'items'       => ['type' => 'integer'],
                        'description' => __(
                            'Optional: limit the revoke to these form IDs (omit for global rules only)',
                            'ivyforms'
                        ),
                    ],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function (array $input) {
                $roleSlug    = sanitize_text_field((string) ($input['roleSlug'] ?? ''));
                $permissions = self::stringList($input['permissions'] ?? []);
                $formIds     = self::optionalFormIds($input['formIds'] ?? null);

                return McpPermissionsHelper::revokeRole($roleSlug, $permissions, $formIds);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/revoke-role-permission');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => ['public' => true, 'type' => 'tool'],
                'annotations'  => ['readonly' => false, 'destructive' => true, 'idempotent' => true],
            ],
        ]);
    }

    private static function registerGrantUserPermission(): void
    {
        wp_register_ability('ivyforms/grant-user-permission', [
            'label'       => __('Grant User Permission', 'ivyforms'),
            'description' => __(
                'Grant one or more IvyForms permissions to a specific WordPress user. Administrator access '
                . 'required. Identify the user by userId, or by userLogin/userEmail. Optionally limit the grant '
                . 'to specific form IDs (omit formIds to grant globally). User-targeted grants override '
                . 'role-targeted grants for the same permission.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['permissions'],
                'properties' => [
                    'userId'      => [
                        'type'        => 'integer',
                        'description' => __('WordPress user ID', 'ivyforms'),
                    ],
                    'userLogin'   => [
                        'type'        => 'string',
                        'description' => __('WordPress username (if userId unknown)', 'ivyforms'),
                    ],
                    'userEmail'   => [
                        'type'        => 'string',
                        'description' => __('WordPress user email (if userId unknown)', 'ivyforms'),
                    ],
                    'permissions' => [
                        'type'        => 'array',
                        'items'       => ['type' => 'string'],
                        'description' => __('Permission keys to grant', 'ivyforms'),
                    ],
                    'formIds'     => [
                        'type'        => 'array',
                        'items'       => ['type' => 'integer'],
                        'description' => __('Optional: limit the grant to these form IDs', 'ivyforms'),
                    ],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function (array $input) {
                $userId      = self::resolveUserId($input);
                $permissions = self::stringList($input['permissions'] ?? []);
                $formIds     = self::optionalFormIds($input['formIds'] ?? null);

                return McpPermissionsHelper::grantUser($userId, $permissions, $formIds);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/grant-user-permission');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => ['public' => true, 'type' => 'tool'],
                'annotations'  => ['readonly' => false, 'destructive' => false, 'idempotent' => true],
            ],
        ]);
    }

    private static function registerRevokeUserPermission(): void
    {
        wp_register_ability('ivyforms/revoke-user-permission', [
            'label'       => __('Revoke User Permission', 'ivyforms'),
            'description' => __(
                'Revoke one or more IvyForms permissions from a specific WordPress user. Administrator access '
                . 'required. Identify the user by userId, or by userLogin/userEmail. Optionally limit the revoke '
                . 'to specific form IDs (omit formIds to revoke from global rules only). If a user rule has no '
                . 'permissions left afterwards it is removed entirely.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['permissions'],
                'properties' => [
                    'userId'      => [
                        'type'        => 'integer',
                        'description' => __('WordPress user ID', 'ivyforms'),
                    ],
                    'userLogin'   => [
                        'type'        => 'string',
                        'description' => __('WordPress username (if userId unknown)', 'ivyforms'),
                    ],
                    'userEmail'   => [
                        'type'        => 'string',
                        'description' => __('WordPress user email (if userId unknown)', 'ivyforms'),
                    ],
                    'permissions' => [
                        'type'        => 'array',
                        'items'       => ['type' => 'string'],
                        'description' => __('Permission keys to revoke', 'ivyforms'),
                    ],
                    'formIds'     => [
                        'type'        => 'array',
                        'items'       => ['type' => 'integer'],
                        'description' => __(
                            'Optional: limit the revoke to these form IDs (omit for global rules only)',
                            'ivyforms'
                        ),
                    ],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function (array $input) {
                $userId      = self::resolveUserId($input);
                $permissions = self::stringList($input['permissions'] ?? []);
                $formIds     = self::optionalFormIds($input['formIds'] ?? null);

                return McpPermissionsHelper::revokeUser($userId, $permissions, $formIds);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/revoke-user-permission');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => ['public' => true, 'type' => 'tool'],
                'annotations'  => ['readonly' => false, 'destructive' => true, 'idempotent' => true],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $input
     */
    private static function resolveUserId(array $input): int
    {
        $userId    = (int) ($input['userId'] ?? 0);
        $userLogin = sanitize_text_field((string) ($input['userLogin'] ?? ''));
        $userEmail = sanitize_email((string) ($input['userEmail'] ?? ''));

        return McpPermissionsHelper::resolveUserId($userId, $userLogin, $userEmail);
    }

    /**
     * @param mixed $value
     * @return list<string>
     */
    private static function stringList($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $item) {
            if (is_string($item) && $item !== '') {
                $out[] = sanitize_text_field($item);
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @param mixed $value
     * @return list<int>|null
     */
    private static function optionalFormIds($value): ?array
    {
        if ($value === null || !is_array($value) || $value === []) {
            return null;
        }

        $ids = [];
        foreach ($value as $item) {
            $id = filter_var($item, FILTER_VALIDATE_INT);
            if ($id !== false && $id > 0) {
                $ids[] = $id;
            }
        }

        return $ids === [] ? null : array_values(array_unique($ids));
    }
}
