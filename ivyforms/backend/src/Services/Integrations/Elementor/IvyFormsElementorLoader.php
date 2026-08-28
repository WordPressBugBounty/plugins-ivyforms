<?php

/**
 * IvyForms Elementor Integration Loader
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Integrations\Elementor;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use IvyForms\Services\Integrations\Elementor\Widgets\IvyFormsWidget;
use IvyForms\Services\Shortcode\ShortcodeService;

/**
 * Class IvyFormsElementorLoader
 *
 * Main loader class for Elementor integration (Singleton pattern)
 * Following wpDataTables implementation pattern
 *
 * @package IvyForms\Services\Integrations\Elementor
 */
final class IvyFormsElementorLoader
{
    /**
     * Singleton instance
     *
     * @var IvyFormsElementorLoader|null
     */
    private static ?IvyFormsElementorLoader $instance = null;

    /**
     * Get singleton instance
     *
     * @return IvyFormsElementorLoader
     */
    public static function instance(): IvyFormsElementorLoader
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        if ($this->isCompatible()) {
            add_action('elementor/init', [$this, 'init']);
        }
    }

    /**
     * Check if Elementor is installed
     *
     * @return bool
     */
    public function isCompatible(): bool
    {
        return defined('ELEMENTOR_VERSION');
    }

    /**
     * Check Elementor version for backwards compatibility
     * Returns true if using old registration method (< 3.5.0)
     *
     * @return bool
     */
    public function useLegacyRegistration(): bool
    {
        if (!defined('ELEMENTOR_VERSION')) {
            return false;
        }

        $elementorVersion = (string) constant('ELEMENTOR_VERSION');
        return version_compare($elementorVersion, '3.5.0', '<');
    }

    /**
     * Initialize the Elementor integration
     *
     * @return void
     */
    public function init(): void
    {
        // Register widgets based on Elementor version
        $action = $this->useLegacyRegistration()
            ? 'elementor/widgets/widgets_registered'
            : 'elementor/widgets/register';
        add_action($action, [$this, 'registerWidgets']);

        // Register widget category
        add_action('elementor/elements/categories_registered', [$this, 'registerWidgetCategories']);

        // Enqueue styles for editor and frontend
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueueWidgetStyles']);
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueueWidgetStyles']);

        // Enqueue frontend scripts for live preview in editor
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'enqueueFrontendScriptsForEditor']);

        // Add live preview support via AJAX
        add_action('elementor/preview/enqueue_scripts', [$this, 'enqueuePreviewScripts']);
    }

    /**
     * Include widget files
     *
     * @return void
     */
    public function includeWidgets(): void
    {
        if (!class_exists(Widget_Base::class)) {
            return;
        }

        require_once IVYFORMS_PATH . '/backend/src/Services/Integrations/Elementor/Widgets/IvyFormsWidget.php';
    }

    /**
    * Register widgets with Elementor
    *
    * @param mixed $widgetsManager
     * @return void
     */
    public function registerWidgets($widgetsManager = null): void
    {
        if (!$this->canRegisterWidgets()) {
            return;
        }

        $this->includeWidgets();

        if ($this->useLegacyRegistration()) {
            $this->registerWidgetLegacy();
            return;
        }

        $this->registerWidgetModern($widgetsManager);
    }

    /**
     * Check if all required classes are available for widget registration
     *
     * @return bool
     */
    private function canRegisterWidgets(): bool
    {
        if (!$this->isCompatible()) {
            return false;
        }

        // @phpstan-ignore-next-line
        $requiredClasses = [Plugin::class, Widget_Base::class, Controls_Manager::class];
        foreach ($requiredClasses as $className) {
            if (!class_exists($className)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Register widget using legacy Elementor method (< 3.5.0)
     *
     * @return void
     */
    private function registerWidgetLegacy(): void
    {
        // @phpstan-ignore-next-line
        $plugin = Plugin::instance();
        if (is_object($plugin) && isset($plugin->widgets_manager)) {
            $plugin->widgets_manager->register_widget_type(new IvyFormsWidget());
        }
    }

    /**
     * Register widget using modern Elementor method (>= 3.5.0)
     *
     * @param mixed $widgetsManager
     * @return void
     */
    private function registerWidgetModern($widgetsManager): void
    {
        if (is_object($widgetsManager) && method_exists($widgetsManager, 'register')) {
            $widgetsManager->register(new IvyFormsWidget());
            return;
        }

        // @phpstan-ignore-next-line
        $plugin = Plugin::instance();
        if (is_object($plugin) && isset($plugin->widgets_manager)) {
            $plugin->widgets_manager->register(new IvyFormsWidget());
        }
    }

    /**
    * Register custom widget category
    *
    * @param mixed $elementsManager
     * @return void
     */
    public function registerWidgetCategories($elementsManager): void
    {
        if (!is_object($elementsManager) || !method_exists($elementsManager, 'add_category')) {
            return;
        }

        $elementsManager->add_category(
            'ivyforms-elementor',
            [
                'title' => __('IvyForms', 'ivyforms'),
                'icon' => 'ivyforms-elementor-icon',
            ],
            1
        );
    }

    /**
     * Enqueue widget styles
     *
     * @return void
     */
    public function enqueueWidgetStyles(): void
    {
        wp_register_style(
            'ivyforms-elementor-widget',
            IVYFORMS_URL . 'backend/src/Services/Integrations/Elementor/assets/css/ivyforms-elementor.css',
            [],
            IVYFORMS_VERSION
        );
        wp_enqueue_style('ivyforms-elementor-widget');

        wp_enqueue_script(
            'ivyforms-elementor-icon',
            IVYFORMS_URL . 'backend/src/Services/Integrations/Elementor/assets/js/ivyforms-elementor-icon.js',
            [],
            IVYFORMS_VERSION,
            false
        );
    }

    /**
     * Enqueue frontend scripts for editor/preview with editor flag set
     *
     * @return void
     */
    private function enqueueFrontendScripts(): void
    {
        // Enqueue IvyForms frontend scripts first
        ShortcodeService::enqueueScripts();

        // Get the correct script handle
        // @phpstan-ignore-next-line
        $frontendScriptHandle = (defined('IVYFORMS_DEV') && IVYFORMS_DEV)
            ? 'ivyforms_scripts_dev_vite'
            : 'ivyforms_script_index';

        // Attach inline scripts to IvyForms handle (guaranteed to be enqueued)
        if (wp_script_is($frontendScriptHandle, 'registered') || wp_script_is($frontendScriptHandle, 'enqueued')) {
            // Initialize global form data list before IvyForms scripts
            wp_add_inline_script(
                $frontendScriptHandle,
                'window.wpIvyFormDataList = window.wpIvyFormDataList || {};',
                'before'
            );

            // Mark that we're in Elementor editor
            wp_add_inline_script(
                $frontendScriptHandle,
                "window.IvyForms = window.IvyForms || {}; window.IvyForms.isElementorEditor = true;",
                'before'
            );
        }
    }

    /**
     * Enqueue admin preview script for data synchronization
     *
     * @param string $handle
     * @return void
     */
    private function enqueueAdminPreviewScript(string $handle): void
    {
        $adminPreviewSrc = IVYFORMS_URL . 'backend/src/Services/Integrations/Gutenberg/assets/admin-preview.js';
        wp_enqueue_script(
            $handle,
            esc_url($adminPreviewSrc),
            ['jquery'],
            IVYFORMS_VERSION,
            true
        );
    }

    /**
     * Enqueue frontend scripts for editor live preview
     *
     * @return void
     */
    public function enqueueFrontendScriptsForEditor(): void
    {
        if (!$this->isCompatible()) {
            return;
        }

        if (!class_exists(Plugin::class)) {
            return;
        }

        // Only in editor mode
        $plugin = Plugin::instance();
        if (!is_object($plugin) || !isset($plugin->editor) || !$plugin->editor->is_edit_mode()) {
            return;
        }

        $this->enqueueFrontendScripts();
        $this->enqueueAdminPreviewScript('ivyforms-elementor-preview');
    }

    /**
     * Enqueue scripts for Elementor preview iframe
     *
     * @return void
     */
    public function enqueuePreviewScripts(): void
    {
        if (!$this->isCompatible()) {
            return;
        }

        if (!class_exists(Plugin::class)) {
            return;
        }

        $this->enqueueFrontendScripts();
        $this->enqueueAdminPreviewScript('ivyforms-elementor-preview-iframe');
    }
}
