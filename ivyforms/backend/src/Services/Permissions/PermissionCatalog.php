<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Services\Permissions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Canonical permission keys stored on access rules and mirrored to WordPress capabilities
 * as ivyforms_{key} (see wpCapability()).
 */
class PermissionCatalog
{
    /** @var array<string, string>|null */
    private static ?array $labelMap = null;

    /**
     * WordPress capability name for a canonical IvyForms permission key.
     */
    public static function wpCapability(string $logicalKey): string
    {
        return 'ivyforms_' . $logicalKey;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return [
            'view_forms_list',
            'add_edit_forms',
            'delete_forms',
            'access_settings_page',
            'view_entries_admin',
            'delete_entries_admin',
        ];
    }

    /**
     * @return list<array{key:string,label:string}>
     */
    public static function definitions(): array
    {
        return [
            ['key' => 'view_forms_list', 'label' => __('View forms list', 'ivyforms')],
            ['key' => 'add_edit_forms', 'label' => __('Add and edit forms', 'ivyforms')],
            ['key' => 'delete_forms', 'label' => __('Delete forms', 'ivyforms')],
            ['key' => 'access_settings_page', 'label' => __('Access this settings page', 'ivyforms')],
            ['key' => 'view_entries_admin', 'label' => __('View entries from the admin area', 'ivyforms')],
            ['key' => 'delete_entries_admin', 'label' => __('Delete entries from the admin area', 'ivyforms')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labelMap(): array
    {
        if (self::$labelMap !== null) {
            return self::$labelMap;
        }

        self::$labelMap = [];
        foreach (self::definitions() as $definition) {
            self::$labelMap[ $definition['key'] ] = $definition['label'];
        }

        return self::$labelMap;
    }

    /**
     * Permissions implied by other grants (expanded on save so REST and UI stay consistent).
     *
     * @return array<string, list<string>>
     */
    public static function dependencyMap(): array
    {
        return [
            'add_edit_forms'       => ['view_forms_list'],
            'delete_forms'         => ['view_forms_list'],
            'delete_entries_admin' => ['view_entries_admin'],
        ];
    }

    /**
     * @param mixed[] $permissions
     * @return list<string>
     */
    public static function sanitize(array $permissions): array
    {
        $allowed = array_flip(self::keys());
        $out     = [];
        foreach ($permissions as $p) {
            if (!is_string($p)) {
                continue;
            }
            if (isset($allowed[$p])) {
                $out[] = $p;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * Sanitize grants and auto-include implied permissions (canonical key order).
     *
     * @param mixed[] $permissions
     * @return list<string>
     */
    public static function sanitizeWithDependencies(array $permissions): array
    {
        $granted = array_flip(self::sanitize($permissions));

        foreach (self::dependencyMap() as $grant => $implied) {
            if (!isset($granted[$grant])) {
                continue;
            }
            foreach ($implied as $key) {
                $granted[$key] = true;
            }
        }

        $out = [];
        foreach (self::keys() as $key) {
            if (isset($granted[$key])) {
                $out[] = $key;
            }
        }

        return $out;
    }
}
