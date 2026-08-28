<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

/**
 * Pure access-rule merge helpers for MCP permission grant/revoke.
 */
final class McpAccessRulesMerger
{
    /**
     * Add/merge a grant onto a rule that matches the principal and form scope, or append a new rule.
     *
     * @param list<array<string, mixed>> $rules
     * @param 'role'|'user' $type
     * @param string|int $principal
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @return list<array<string, mixed>>
     */
    public static function upsertGrant(
        array $rules,
        string $type,
        $principal,
        array $permissions,
        ?array $formIds
    ): array {
        $scopeKey = self::scopeKey($formIds);
        foreach ($rules as &$rule) {
            if (!self::ruleMatches($rule, $type, $principal, $scopeKey)) {
                continue;
            }
            $existing            = is_array($rule['permissions'] ?? null) ? $rule['permissions'] : [];
            $rule['permissions'] = array_values(array_unique(array_merge($existing, $permissions)));
            $rule['updated_at']  = time();

            unset($rule);

            return $rules;
        }
        unset($rule);

        $rules[] = self::newRule($type, $principal, $permissions, $formIds);

        return $rules;
    }

    /**
     * Remove permission keys from matching rules, dropping rules that become empty.
     *
     * Only rules for the same principal and form scope as {@see upsertGrant()} are modified:
     * omit formIds to revoke from global rules only; pass formIds to revoke from that scope.
     *
     * @param list<array<string, mixed>> $rules
     * @param 'role'|'user' $type
     * @param string|int $principal
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @return list<array<string, mixed>>
     */
    public static function applyRevoke(
        array $rules,
        string $type,
        $principal,
        array $permissions,
        ?array $formIds
    ): array {
        $scopeKey = self::scopeKey($formIds);
        $out      = [];
        foreach ($rules as $rule) {
            if (!self::ruleMatches($rule, $type, $principal, $scopeKey)) {
                $out[] = $rule;
                continue;
            }
            $existing  = is_array($rule['permissions'] ?? null) ? $rule['permissions'] : [];
            $remaining = array_values(array_diff($existing, $permissions));
            if ($remaining === []) {
                continue;
            }
            $rule['permissions'] = $remaining;
            $rule['updated_at']  = time();
            $out[]               = $rule;
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $rule
     * @param 'role'|'user' $type
     * @param string|int $principal
     */
    private static function ruleMatches(array $rule, string $type, $principal, string $scopeKey): bool
    {
        if (($rule['type'] ?? '') !== $type) {
            return false;
        }

        if (self::scopeKey($rule['form_ids'] ?? null) !== $scopeKey) {
            return false;
        }

        if ($type === 'role') {
            return strtolower(trim((string) ($rule['role_slug'] ?? ''))) === (string) $principal;
        }

        return (int) ($rule['user_id'] ?? 0) === (int) $principal;
    }

    /**
     * @param 'role'|'user' $type
     * @param string|int $principal
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @return array<string, mixed>
     */
    private static function newRule(string $type, $principal, array $permissions, ?array $formIds): array
    {
        $now  = time();
        $rule = [
            'id'          => self::ruleId(),
            'type'        => $type,
            'permissions' => $permissions,
            'form_ids'    => $formIds,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];
        if ($type === 'role') {
            $rule['role_slug'] = (string) $principal;

            return $rule;
        }

        $rule['user_id'] = (int) $principal;

        return $rule;
    }

    /**
     * @param list<int>|null $formIds
     */
    private static function scopeKey(?array $formIds): string
    {
        if ($formIds === null) {
            return 'global';
        }
        $ids = array_values(array_unique(array_map('intval', $formIds)));
        sort($ids);

        return 'forms:' . implode(',', $ids);
    }

    private static function ruleId(): string
    {
        if (function_exists('wp_generate_uuid4')) {
            return 'mcp-' . wp_generate_uuid4();
        }

        return uniqid('mcp-', true);
    }
}
