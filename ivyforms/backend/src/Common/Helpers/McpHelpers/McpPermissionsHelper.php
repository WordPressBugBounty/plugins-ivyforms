<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

use IvyForms\Services\Permissions\PermissionCatalog;
use WP_Error;

/**
 * Shared helpers for the IvyForms MCP permission-management abilities.
 *
 * Reads and writes go through the internal IvyForms REST API
 * ({@see McpAbilitiesHelper::restRequest()}), reusing the same controllers as the
 * Permissions settings tab:
 * - GET  /permissions/access-rules  → {@see \IvyForms\Controllers\Permissions\GetAccessRulesController}
 * - GET  /permissions/meta          → {@see \IvyForms\Controllers\Permissions\GetPermissionsMetaController}
 * - POST /permissions/access-rules  → {@see \IvyForms\Controllers\Permissions\UpdateAccessRulesController}
 *
 * Only the grant/revoke MERGE (read current rules → union/diff → resubmit the full set)
 * lives here, mirroring how the admin UI edits the rule set.
 */
class McpPermissionsHelper
{
    private const ACCESS_RULES_ROUTE = '/permissions/access-rules';
    private const META_ROUTE         = '/permissions/meta';

    /**
     * Current stored access rules (hydrated rows, as returned to the admin UI).
     *
     * Returns null when the read fails, so callers can distinguish "no rules"
     * from "could not read rules" and avoid persisting a partial set.
     *
     * @return list<array<string, mixed>>|null
     */
    public static function currentRules(): ?array
    {
        $response = McpAbilitiesHelper::restRequest('GET', self::ACCESS_RULES_ROUTE);

        return self::rulesFromResponse($response);
    }

    /**
     * Available permission keys (with labels), assignable roles, and forms.
     *
     * @return array<string, mixed>
     */
    public static function permissionMetadata(): array
    {
        $response = McpAbilitiesHelper::restRequest('GET', self::META_ROUTE);
        if (!is_array($response)) {
            return [];
        }
        $payload = $response['data'] ?? $response;

        return is_array($payload) ? $payload : [];
    }

    /**
     * Grant permission keys to a role (creating or extending its rule).
     *
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @return array<string, mixed>|\WP_Error
     */
    public static function grantRole(string $roleSlug, array $permissions, ?array $formIds = null)
    {
        $roleSlug = strtolower(trim($roleSlug));

        return self::grant(
            'role',
            $roleSlug,
            $permissions,
            $formIds,
            sprintf(
                /* translators: %s: WordPress role slug, e.g. editor */
                __('Granted permissions to role "%s".', 'ivyforms'),
                $roleSlug
            )
        );
    }

    /**
     * Revoke permission keys from a role.
     *
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @return array<string, mixed>|\WP_Error
     */
    public static function revokeRole(string $roleSlug, array $permissions, ?array $formIds = null)
    {
        $roleSlug = strtolower(trim($roleSlug));

        return self::revoke(
            'role',
            $roleSlug,
            $permissions,
            $formIds,
            sprintf(
                /* translators: %s: WordPress role slug, e.g. editor */
                __('Revoked permissions from role "%s".', 'ivyforms'),
                $roleSlug
            )
        );
    }

    /**
     * Grant permission keys to a user (creating or extending their rule).
     *
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @return array<string, mixed>|\WP_Error
     */
    public static function grantUser(int $userId, array $permissions, ?array $formIds = null)
    {
        if ($userId <= 0) {
            return self::failure(
                'invalid_user_id',
                __('A valid userId is required.', 'ivyforms')
            );
        }

        return self::grant(
            'user',
            $userId,
            $permissions,
            $formIds,
            sprintf(
                /* translators: %d: WordPress user ID */
                __('Granted permissions to user #%d.', 'ivyforms'),
                $userId
            )
        );
    }

    /**
     * Revoke permission keys from a user.
     *
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @return array<string, mixed>|\WP_Error
     */
    public static function revokeUser(int $userId, array $permissions, ?array $formIds = null)
    {
        if ($userId <= 0) {
            return self::failure(
                'invalid_user_id',
                __('A valid userId is required.', 'ivyforms')
            );
        }

        return self::revoke(
            'user',
            $userId,
            $permissions,
            $formIds,
            sprintf(
                /* translators: %d: WordPress user ID */
                __('Revoked permissions from user #%d.', 'ivyforms'),
                $userId
            )
        );
    }

    /**
     * @param 'role'|'user' $type
     * @param string|int $principal
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @return array<string, mixed>|\WP_Error
     */
    private static function grant(string $type, $principal, array $permissions, ?array $formIds, string $message)
    {
        $clean = PermissionCatalog::sanitize($permissions);
        if ($clean === []) {
            return self::failure(
                'no_valid_permission_keys',
                __('No valid permission keys provided.', 'ivyforms')
            );
        }

        $current = self::currentRules();
        if ($current === null) {
            return self::failure(
                'access_rules_read_failed',
                __('Could not read the current access rules; no changes were made.', 'ivyforms'),
                500
            );
        }

        $rules = McpAccessRulesMerger::upsertGrant($current, $type, $principal, $clean, $formIds);

        return self::persist($rules, $message);
    }

    /**
     * @param 'role'|'user' $type
     * @param string|int $principal
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @return array<string, mixed>|\WP_Error
     */
    private static function revoke(string $type, $principal, array $permissions, ?array $formIds, string $message)
    {
        $clean = PermissionCatalog::sanitize($permissions);
        if ($clean === []) {
            return self::failure(
                'no_valid_permission_keys',
                __('No valid permission keys provided.', 'ivyforms')
            );
        }

        $current = self::currentRules();
        if ($current === null) {
            return self::failure(
                'access_rules_read_failed',
                __('Could not read the current access rules; no changes were made.', 'ivyforms'),
                500
            );
        }

        $rules = McpAccessRulesMerger::applyRevoke($current, $type, $principal, $clean, $formIds);

        return self::persist($rules, $message);
    }

    /**
     * Resolve a user id from an id, login, or email.
     */
    public static function resolveUserId(int $userId, string $userLogin = '', string $userEmail = ''): int
    {
        if ($userId > 0) {
            return $userId;
        }
        if ($userLogin !== '') {
            $user = get_user_by('login', $userLogin);
            if ($user) {
                return (int) $user->ID;
            }
        }
        if ($userEmail !== '') {
            $user = get_user_by('email', $userEmail);
            if ($user) {
                return (int) $user->ID;
            }
        }

        return 0;
    }

    /**
     * Persist the full rule set through the access-rules REST controller (validate + save + sync).
     *
     * @param list<array<string, mixed>> $rules
     * @return array<string, mixed>|\WP_Error
     */
    private static function persist(array $rules, string $successMessage)
    {
        $response = McpAbilitiesHelper::restRequest(
            'POST',
            self::ACCESS_RULES_ROUTE,
            ['rules' => array_values($rules)]
        );

        $saved = self::rulesFromResponse($response);
        if ($saved === null) {
            return self::failure(
                'access_rules_update_failed',
                self::errorMessage($response),
                self::errorStatus($response)
            );
        }

        return [
            'message' => $successMessage,
            'rules'   => $saved,
        ];
    }

    /**
     * Extract the rules array from a (success-wrapped) REST response, or null when absent.
     *
     * @param mixed $response
     * @return list<array<string, mixed>>|null
     */
    private static function rulesFromResponse($response): ?array
    {
        if (!is_array($response)) {
            return null;
        }
        $rules = $response['data']['rules'] ?? $response['rules'] ?? null;

        return is_array($rules) ? array_values($rules) : null;
    }

    /**
     * @param mixed $response
     */
    private static function errorMessage($response): string
    {
        $message = is_array($response)
            ? ($response['message'] ?? $response['data']['message'] ?? '')
            : '';

        return is_string($message) && $message !== ''
            ? $message
            : __('Failed to update access rules.', 'ivyforms');
    }

    /**
     * @param mixed $response
     */
    private static function errorStatus($response): int
    {
        if (!is_array($response)) {
            return 500;
        }

        $status = (int) ($response['data']['status'] ?? 0);

        return $status >= 400 && $status < 600 ? $status : 500;
    }

    private static function failure(string $code, string $message, int $status = 400): WP_Error
    {
        return new WP_Error($code, $message, ['status' => $status]);
    }
}
