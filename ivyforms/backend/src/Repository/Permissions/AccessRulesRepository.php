<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Repository\Permissions;

/**
 * Persists IvyForms access rules (global form permissions) in a dedicated option.
 */
class AccessRulesRepository
{
    private const OPTION_KEY = 'ivyforms_access_rules';

    /**
     * @return array{version:int, rules:list<array<string,mixed>>}
     */
    public function getPayload(): array
    {
        $raw = get_option(self::OPTION_KEY, null);
        if (!is_array($raw) || !isset($raw['rules']) || !is_array($raw['rules'])) {
            return [
                'version' => 1,
                'rules'   => [],
            ];
        }

        $version = isset($raw['version']) ? (int) $raw['version'] : 1;

        return [
            'version' => max(1, $version),
            'rules'   => self::filterRuleEntries($raw['rules']),
        ];
    }

    /**
     * @param list<array<string,mixed>> $rules
     */
    public function saveRules(array $rules): void
    {
        $filtered = self::filterRuleEntries($rules);

        $prev    = get_option(self::OPTION_KEY, null);
        $version = 1;
        if (is_array($prev) && array_key_exists('version', $prev)) {
            $version = max(1, (int) $prev['version']);
        }

        update_option(
            self::OPTION_KEY,
            [
                'version' => $version,
                'rules'   => array_values($filtered),
            ],
            false
        );
    }

    /**
     * @param mixed[] $rules
     * @return list<array<string,mixed>>
     */
    private static function filterRuleEntries(array $rules): array
    {
        return array_values(array_filter($rules, 'is_array'));
    }
}
