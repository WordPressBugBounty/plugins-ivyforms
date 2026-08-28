<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Services\Permissions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

use IvyForms\Plugin\Plugin;
use IvyForms\Repository\Permissions\AccessRulesRepository;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;

/**
 * One-time sync of stored access rules to WP role/user capabilities (backfill after upgrades).
 */
final class AccessRulesCapabilityBootstrap
{
    private const OPTION_FLAG = 'ivyforms_access_rules_caps_synced_v2';

    public static function maybeRunOnce(): void
    {
        if (!is_admin() || get_option(self::OPTION_FLAG, false)) {
            return;
        }

        $plugin = Plugin::getInstance();
        if (!$plugin instanceof Plugin || $plugin->container === null) {
            return;
        }

        try {
            /** @var AccessRulesRepository $repo */
            $repo  = $plugin->container->get(AccessRulesRepository::class);
            $rules = $repo->getPayload()['rules'];
            AccessRulesRoleCapabilitySynchronizer::syncFromRules(
                $rules,
                AccessRulesRoleCapabilitySynchronizer::extractUserIdsFromUserRules($rules)
            );
        } catch (DependencyException | NotFoundException $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('IvyForms access rules capability sync: ' . $e->getMessage());
            }

            return;
        }

        update_option(self::OPTION_FLAG, 1, false);
    }
}
