<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP;

use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsChangelogAbilitiesRegistrar;
use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsEntriesAbilitiesRegistrar;
use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsFieldsAbilitiesRegistrar;
use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsFormsAbilitiesRegistrar;
use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsIntegrationsAbilitiesRegistrar;
use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsMultiPageAbilitiesRegistrar;
use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsNavigationAbilitiesRegistrar;
use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsNotificationsAbilitiesRegistrar;
use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsPermissionsAbilitiesRegistrar;
use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsSettingsAbilitiesRegistrar;
use IvyForms\Infrastructure\WP\MCP\Abilities\IvyFormsTemplatesAbilitiesRegistrar;

/**
 * Registers WordPress Abilities for every IvyForms MCP tool.
 *
 * Domain-specific abilities live in small registrar classes under MCP\Abilities.
 */
class IvyFormsAbilitiesRegistrar
{
    public static function registerCategories(): void
    {
        if (!function_exists('wp_register_ability_category')) {
            return;
        }

        wp_register_ability_category('ivyforms', [
            'label'       => __('IvyForms', 'ivyforms'),
            'description' => __(
                'IvyForms – Contact Form Builder for WordPress. ' .
                'Manage forms, entries/submissions, notifications, confirmations, settings, ' .
                'integrations, and templates.',
                'ivyforms'
            ),
        ]);
    }

    public static function registerAbilities(): void
    {
        if (!function_exists('wp_register_ability')) {
            return;
        }

        IvyFormsFormsAbilitiesRegistrar::registerAbilities();
        IvyFormsMultiPageAbilitiesRegistrar::registerAbilities();
        IvyFormsFieldsAbilitiesRegistrar::registerAbilities();
        IvyFormsEntriesAbilitiesRegistrar::registerAbilities();
        IvyFormsNotificationsAbilitiesRegistrar::registerAbilities();
        IvyFormsSettingsAbilitiesRegistrar::registerAbilities();
        IvyFormsTemplatesAbilitiesRegistrar::registerAbilities();
        IvyFormsIntegrationsAbilitiesRegistrar::registerAbilities();
        IvyFormsChangelogAbilitiesRegistrar::registerAbilities();
        IvyFormsNavigationAbilitiesRegistrar::registerAbilities();
        IvyFormsPermissionsAbilitiesRegistrar::registerAbilities();
    }
}
