<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * @copyright Â© Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

use IvyForms\Vendor\DI\ContainerBuilder;

$containerBuilder = new ContainerBuilder();

try {
    $container = $containerBuilder->build();
} catch (Exception $e) {
    // Handle the exception
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Container build failed: ' . $e->getMessage());
    }
    exit('Container build failed. Please check the logs.');
}

/**
 * Set app settings
 */
//$container->set('settings', require('settings.php'));


require 'repository.php';

require 'services.php';

return $container;
