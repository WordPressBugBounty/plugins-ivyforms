<?php

/**
 * GutenbergBlockAssets class - extracted asset and editor registration logic
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Integrations\Gutenberg\Blocks;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Services\API\IvyFormsAPI;
use IvyForms\Services\Shortcode\ShortcodeService;

class GutenbergBlockAssets
{
    /**
     * Register custom block category for IvyForms
     *
     * @return void
     */
    public static function registerBlockCategory(): void
    {
        add_filter('block_categories_all', function ($categories) {
            foreach ($categories as $category) {
                if ($category['slug'] === 'ivyforms') {
                    return $categories;
                }
            }

            // Add IvyForms category at the beginning
            return array_merge(
                [
                    [
                        'slug' => 'ivyforms',
                        'title' => __('IvyForms', 'ivyforms'),
                        'icon' => null,
                    ],
                ],
                $categories
            );
        });
    }

    /**
     * Register block assets (JavaScript and CSS)
     *
     * @return void
     */
    public static function registerBlockAssets(): void
    {
        // Hook into enqueue_block_editor_assets for editor-only scripts (block registration)
        add_action('enqueue_block_editor_assets', [self::class, 'enqueueBlockEditorAssetsCallback']);

        // Hook into enqueue_block_assets for scripts that need to run in the iframe (Vue frontend)
        add_action('enqueue_block_assets', [self::class, 'enqueueBlockAssetsCallback']);
    }

    /**
     * Callback for enqueue_block_assets - runs in both editor and iframe
     * This is where we load the Vue frontend scripts for the preview
     *
     * @return void
     * @throws ValidationException
     */
    public static function enqueueBlockAssetsCallback(): void
    {
        // Only enqueue in admin/editor context
        if (!is_admin()) {
            return;
        }

        // Enqueue frontend Vue scripts for live preview in editor iframe
        ShortcodeService::enqueueScripts();

        // Add a small inline flag on the frontend script so the public bundle knows it's
        // being loaded inside the Gutenberg editor
        $frontendScriptHandle = IVYFORMS_DEV ?
            'ivyforms_scripts_dev_vite' : 'ivyforms_script_index';
        if (wp_script_is($frontendScriptHandle, 'registered') || wp_script_is($frontendScriptHandle, 'enqueued')) {
            wp_add_inline_script(
                $frontendScriptHandle,
                "window.IvyForms = window.IvyForms || {}; window.IvyForms.isGutenbergEditor = true;",
                'before'
            );
        }
    }

    /**
     * Callback used for enqueueing block editor assets
     *
     * @return void
     * @throws ValidationException
     */
    public static function enqueueBlockEditorAssetsCallback(): void
    {
        $assetFile = IVYFORMS_PATH . '/backend/src/Services/Integrations/Gutenberg/assets/build/index.asset.php';

        // Default dependencies
        $dependencies = [
            'wp-blocks',
            'wp-element',
            'wp-components',
            'wp-editor',
            'wp-i18n',
            'wp-block-editor',
            'wp-server-side-render',
            'wp-data',
        ];
        $version = IVYFORMS_VERSION;

        if (file_exists($assetFile)) {
            $asset = include $assetFile;
            $dependencies = $asset['dependencies'] ?? $dependencies;
            $version = $asset['version'] ?? $version;
        }

        // Enqueue block editor script
        wp_enqueue_script(
            'ivyforms-gutenberg-block',
            IVYFORMS_URL . 'backend/src/Services/Integrations/Gutenberg/assets/build/index.js',
            $dependencies,
            $version,
            false
        );

        // Initialize the global data object before our block script executes.
        // We add this inline tied to our enqueued script so WP can manage ordering.
        wp_add_inline_script(
            'ivyforms-gutenberg-block',
            'window.wpIvyFormDataList = window.wpIvyFormDataList || {};',
            'before'
        );

        // Register and enqueue the admin preview helper script (handles SSR merges)
        $adminPreviewSrc = IVYFORMS_URL . 'backend/src/Services/Integrations/Gutenberg/assets/admin-preview.js';
        wp_register_script(
            'ivyforms-admin-preview',
            esc_url($adminPreviewSrc),
            ['ivyforms-gutenberg-block'],
            $version,
            true
        );
        wp_enqueue_script('ivyforms-admin-preview');

        // Get forms data and pass to JavaScript using wp_localize_script
        $forms = self::getFormsForSelect();

        // Localize script with forms data
        wp_localize_script(
            'ivyforms-gutenberg-block',
            'ivyFormsGutenberg',
            [
                'forms' => $forms,
                'adminUrl' => admin_url('admin.php?page=ivyforms-builder'),
            ]
        );

        // Enqueue block editor styles
        wp_enqueue_style(
            'ivyforms-gutenberg-block-editor',
            IVYFORMS_URL . 'backend/src/Services/Integrations/Gutenberg/assets/build/index.css',
            ['wp-edit-blocks'],
            $version
        );
    }

    /**
     * Get forms formatted for select dropdown
     *
     * @return array<int, array<string, mixed>> Array of forms with id, title, and settings
     */
    private static function getFormsForSelect(): array
    {
        $forms = IvyFormsAPI::getForms();

        if (is_wp_error($forms) || empty($forms)) {
            return [];
        }

        $formsList = [];
        foreach ($forms as $form) {
            if (is_object($form) && method_exists($form, 'getId') && method_exists($form, 'getName')) {
                $formData = [
                    'value' => (string)$form->getId(),
                    'label' => $form->getName(),
                ];
                // Include form settings for syncing toggles in block editor
                if (method_exists($form, 'isTitleVisible')) {
                    $formData['showTitle'] = $form->isTitleVisible();
                }
                if (method_exists($form, 'isDescriptionVisible')) {
                    $formData['showDescription'] = $form->isDescriptionVisible();
                }
                $formsList[] = $formData;
            }
        }

        return $formsList;
    }
}
