<?php

/**
 * @copyright © Melograno Venture Studio. All rights.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Routes;

use IvyForms\Vendor\DI\Container;
use IvyForms\Vendor\DI\DependencyException;
use IvyForms\Vendor\DI\NotFoundException;
use IvyForms\Routes\Ai\AiRoutes;
use IvyForms\Routes\Confirmation\Confirmation;
use IvyForms\Routes\Entry\Entry;
use IvyForms\Routes\Feedback\Feedback;
use IvyForms\Routes\Form\Form;
use IvyForms\Routes\Notification\Notification;
use IvyForms\Routes\Template\Template;
use IvyForms\Routes\Settings\Settings;
use IvyForms\Routes\Changelog\Changelog;
use IvyForms\Routes\Integrations\Integrations;
use IvyForms\Routes\Permissions\Permissions;
use IvyForms\Routes\PublicApi\PublicRoutes;

/**
 * Class Routes
 *
 * @package IvyForms\Routes
 */
class Routes
{
    public static string $routeNamespace = 'ivyforms/v1';

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function registerRoutes(Container $container): void
    {
        Form::registerRoutes($container, self::$routeNamespace);
        Notification::registerRoutes($container, self::$routeNamespace);
        Confirmation::registerRoutes($container, self::$routeNamespace);
        Entry::registerRoutes($container, self::$routeNamespace);
        Template::registerRoutes($container, self::$routeNamespace);
        Settings::registerRoutes($container, self::$routeNamespace);
        Changelog::registerRoutes($container, self::$routeNamespace);
        AiRoutes::registerRoutes($container, self::$routeNamespace);

        /**
         * Register integration-specific routes before the generic /integrations/{slug} route
         * so paths like /integrations/google-sheets are not captured as a slug lookup.
         */
        do_action('ivyforms/rest/register_additional_routes', $container, self::$routeNamespace);

        Integrations::registerRoutes($container, self::$routeNamespace);
        Feedback::registerRoutes($container, self::$routeNamespace);
        Permissions::registerRoutes($container, self::$routeNamespace);

        PublicRoutes::registerExtensionHook($container);
    }
}
