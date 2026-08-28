<?php

/**
 * FormPreviewHelper - Shared helper for generating form preview data
 *
 * Used by both Gutenberg and Elementor integrations for editor preview rendering.
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Common\Helpers;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Shortcode\ShortcodeService;

/**
 * Class FormPreviewHelper
 *
 * Generates form data JSON and embedded script tags for editor previews
 *
 * @package IvyForms\Common\Helpers
 */
class FormPreviewHelper
{
    /**
     * Generate embedded form data JSON for data attribute
     *
     * @param int $formId
     * @return string
     */
    public static function generateFormDataJson(int $formId): string
    {
        if (empty(ShortcodeService::$formList)) {
            return '';
        }

        $formDataMap = self::filterFormListById($formId);
        if (empty($formDataMap)) {
            return '';
        }

        $json = wp_json_encode($formDataMap);
        return $json !== false ? $json : '';
    }

    /**
     * Generate embedded JSON script blocks
     *
     * @param int $formId
     * @return string
     */
    public static function generateJsonScripts(int $formId): string
    {
        if (empty(ShortcodeService::$formList)) {
            return '';
        }

        $scripts = '';
        foreach (ShortcodeService::$formList as $counterKey => $payload) {
            if (isset($payload['id']) && (int)$payload['id'] === (int)$formId) {
                $scripts .= self::buildScriptTag($counterKey, $payload);
            }
        }

        return $scripts;
    }

    /**
     * Build a single JSON script tag
     *
     * @param string $counterKey
     * @param array<string, mixed> $payload
     * @return string
     */
    private static function buildScriptTag(string $counterKey, array $payload): string
    {
        $json = wp_json_encode($payload);
        $json = str_replace('</script>', '<\/script>', $json);
        return '<script type="application/json" class="ivyforms-ssr-data"'
            . ' data-ivyforms-counter="' . esc_attr($counterKey) . '">'
            . $json . '</script>';
    }

    /**
     * Filter form list by form ID
     *
     * @param int $formId
     * @return array<string, mixed>
     */
    private static function filterFormListById(int $formId): array
    {
        $formDataMap = [];
        foreach (ShortcodeService::$formList as $counterKey => $payload) {
            if (isset($payload['id']) && (int)$payload['id'] === (int)$formId) {
                $formDataMap[$counterKey] = $payload;
            }
        }
        return $formDataMap;
    }
}
