<?php

/**
 * GutenbergBlock class for handling IvyForms Gutenberg block registration
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Integrations\Gutenberg\Blocks;

use IvyForms\Common\Exceptions\ValidationException;

/**
 * Class GutenbergBlock
 *
 * @package IvyForms\Services\Blocks
 */
class GutenbergBlock
{
    /**
     * Register the Gutenberg block
     *
     * @return void
     */
    public static function register(): void
    {
        if (!function_exists('register_block_type')) {
            return;
        }

        // Register custom block category
        // delegate category & assets registration to extracted helper
        GutenbergBlockAssets::registerBlockCategory();
        GutenbergBlockAssets::registerBlockAssets();

        // Check if block.json exists for WordPress 5.8+ compatibility
        $blockJsonPath = IVYFORMS_PATH . '/backend/src/Services/Integrations/Gutenberg/assets/block.json';

        if (file_exists($blockJsonPath)) {
            register_block_type($blockJsonPath, [
                'render_callback' => [self::class, 'render'],
                'editor_script' => 'ivyforms-gutenberg-block',
                'editor_style' => 'ivyforms-gutenberg-block-editor',
            ]);
            return;
        }

        register_block_type('ivyforms/gutenberg-block', [
            'render_callback' => [self::class, 'render'],
            'attributes' => BlockAttributes::getAttributes(),
            'api_version' => '2',
        ]);
    }

    /**
     * Render the Gutenberg block
     *
     * @param array<string, mixed> $attributes Block attributes
     * @return string Rendered block HTML
     * @throws ValidationException
     */
    public static function render(array $attributes): string
    {
        $formId = GutenbergBlockRenderer::getFormIdFromAttributes($attributes);
        if ($formId === 0) {
            return GutenbergBlockRenderer::renderNoFormSelected();
        }

        $form = GutenbergBlockRenderer::fetchPublishedForm($formId);
        if ($form === null) {
            return GutenbergBlockRenderer::renderFormNotFound();
        }

        return GutenbergBlockRenderer::renderFormBlock($formId, $attributes);
    }
}
