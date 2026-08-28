<?php

namespace IvyForms\Routes\PublicApi;

use IvyForms\Vendor\DI\Container;

/**
 * Lite extension point for ivyforms-public/v1 routes.
 *
 * Lite fires the hook only; Pro Agency registers controllers when licensed.
 * Public API authentication uses Pro Agency API keys (`X-IvyForms-Api-Key`).
 */
class PublicRoutes
{
    public const NAMESPACE = 'ivyforms-public/v1';

    public static function registerExtensionHook(Container $container): void
    {
        /**
         * Allow Pro and third-party plugins to register public REST routes.
         *
         * @param Container $container
         * @param string    $namespace e.g. ivyforms-public/v1
         */
        do_action('ivyforms/rest/register_public_routes', $container, self::NAMESPACE);
    }
}
