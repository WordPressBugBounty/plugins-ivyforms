<?php
/*
 * Plugin Name: IvyForms - The innovative Contact Form Builder
 * Plugin URI: https://ivyforms.com
 * Description: Transform your WordPress site with IvyForms: a powerful, user-friendly plugin for creating and managing customizable forms effortlessly.
 * Version: 1.4.1
 * Requires PHP: 7.4
 * Author: Melograno Ventures
 * Author URI: https://melograno.io/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ivyforms
 * Domain Path: /languages
 *
 * IvyForms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * IvyForms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with IvyForms. If not, see {URI to Plugin License}.
 */
namespace IvyForms;

use Exception;
use IvyForms\Infrastructure\WP\MCP\McpFeatureGate;
use IvyForms\Plugin\Plugin;
use WP\MCP\Core\McpAdapter;

if ( ! defined( 'ABSPATH' ) )
{
    exit; // Exit if accessed directly.
}

if (!defined('IVYFORMS_PATH'))
{
    define('IVYFORMS_PATH', __DIR__);
}

if (!defined('IVYFORMS_URL'))
{
    define('IVYFORMS_URL', plugin_dir_url(__FILE__));
}

if (!defined('IVYFORMS_TEMPLATES_IMAGES_URL'))
{
    define('IVYFORMS_TEMPLATES_IMAGES_URL', plugin_dir_url(__FILE__) . 'frontend/src/assets/images/templates/');
}

if (!defined('IVYFORMS_DEV'))
{
    define('IVYFORMS_DEV', false);
}

// Google Sheets OAuth middleware (override in wp-config.php for hosted/staging).
if (!defined('IVYFORMS_GOOGLE_SHEETS_MIDDLEWARE_URL')) {
    define(
        'IVYFORMS_GOOGLE_SHEETS_MIDDLEWARE_URL',
        IVYFORMS_DEV ? 'http://localhost:8080' : 'https://middleware.ivyforms.com'
    );
}

// Const for plugin slug
if (!defined('IVYFORMS_PLUGIN_SLUG'))
{
    define('IVYFORMS_PLUGIN_SLUG', plugin_basename(__FILE__));
}

if (!defined('IVYFORMS_FILE'))
{
    define('IVYFORMS_FILE', __FILE__);
}
// Const for site URL
if (!defined('IVYFORMS_SITE_URL')) {
    define('IVYFORMS_SITE_URL', get_site_url());
}

if (!defined('IVYFORMS_VERSION')) {
    define('IVYFORMS_VERSION', '1.4.1');
}

if (!defined('IVYFORMS_MCP_MIN_WP_VERSION')) {
    define('IVYFORMS_MCP_MIN_WP_VERSION', '6.9');
}

if (!defined('MELOGRANO_BI_GATE_URL')) {
    define('MELOGRANO_BI_GATE_URL', 'https://bi.melograno.io');
}

require_once IVYFORMS_PATH . '/backend/scope-vendor/autoload.php';
require_once IVYFORMS_PATH . '/backend/vendor/autoload.php';

if (class_exists(McpAdapter::class) && McpFeatureGate::isEnabled()) {
    McpAdapter::instance();
    add_action('mcp_adapter_init', array('\IvyForms\Infrastructure\WP\MCP\IvyFormsMcpServerRegistrar', 'init'));
    add_action('wp_abilities_api_categories_init', array('\IvyForms\Infrastructure\WP\MCP\IvyFormsAbilitiesRegistrar', 'registerCategories'));
    add_action('wp_abilities_api_init', array('\IvyForms\Infrastructure\WP\MCP\IvyFormsAbilitiesRegistrar', 'registerAbilities'));
}

try {
    Plugin::getInstance();
} catch (Exception $e) {
    echo 'ERROR: ' . esc_html($e->getMessage());
}