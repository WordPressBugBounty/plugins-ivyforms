<?php

/**
 * Render callback for IvyForms Gutenberg block
 *
 * This file can be used as an alternative to the render_callback in block registration,
 * but currently the rendering is handled by GutenbergBlock::render()
 *
 * @var array<string, string>   $attributes Block attributes
 * @var string                  $content    Block content
 * @var WP_Block                $block      Block instance
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// The actual rendering is handled by GutenbergBlock::render()
// This file is kept for block.json compatibility
