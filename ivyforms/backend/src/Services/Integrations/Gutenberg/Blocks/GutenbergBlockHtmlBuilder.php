<?php

/**
 * GutenbergBlockHtmlBuilder class for building block HTML output
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Integrations\Gutenberg\Blocks;

use IvyForms\Common\Sanitizer\HtmlSanitizer;

/**
 * Class GutenbergBlockHtmlBuilder
 *
 * Handles HTML and style building for the IvyForms Gutenberg block
 *
 * @package IvyForms\Services\Blocks
 */
class GutenbergBlockHtmlBuilder
{
    /**
     * Build wrapper attributes array
     *
     * @param array<string, mixed> $attributes
     * @param string $blockInstanceId
     * @return array<string, string>
     */
    public static function buildWrapperAttributes(array $attributes, string $blockInstanceId): array
    {
        $cssClasses = self::buildCssClasses($attributes);

        return [
            'id' => $blockInstanceId,
            'class' => implode(' ', $cssClasses),
            'data-show-title' => !empty($attributes['showTitle']) ? 'true' : 'false',
            'data-show-description' => !empty($attributes['showDescription']) ? 'true' : 'false',
        ];
    }

    /**
     * Build CSS classes array from attributes
     *
     * @param array<string, mixed> $attributes
     * @return array<int, string>
     */
    private static function buildCssClasses(array $attributes): array
    {
        $cssClasses = ['ivyforms-gutenberg-block'];

        if (!empty($attributes['className'])) {
            $cssClasses[] = HtmlSanitizer::sanitizeCssClasses($attributes['className']);
        }

        if (!empty($attributes['customCssClass'])) {
            $cssClasses[] = HtmlSanitizer::sanitizeCssClasses($attributes['customCssClass']);
        }

        return $cssClasses;
    }

    /**
     * Convert wrapper attributes array to HTML attribute string
     *
     * @param array<string, string> $wrapperAttributes
     * @return string
     */
    public static function attributesToString(array $wrapperAttributes): string
    {
        $parts = [];
        foreach ($wrapperAttributes as $key => $value) {
            $parts[] = sprintf('%s="%s"', esc_attr($key), esc_attr($value));
        }
        return ' ' . implode(' ', $parts);
    }

    /**
     * Build custom styles for hiding title/description
     *
     * @param array<string, mixed> $attributes
     * @param string $blockInstanceId
     * @return string
     */
    public static function buildCustomStyles(array $attributes, string $blockInstanceId): string
    {
        $rules = self::buildHideRules($attributes, $blockInstanceId);
        return $rules !== '' ? '<style>' . $rules . '</style>' : '';
    }

    /**
     * Build CSS rules for hiding elements
     *
     * @param array<string, mixed> $attributes
     * @param string $blockInstanceId
     * @return string
     */
    private static function buildHideRules(array $attributes, string $blockInstanceId): string
    {
        $selector = '#' . esc_attr($blockInstanceId);
        $rules = [];

        if (isset($attributes['showTitle']) && $attributes['showTitle'] === false) {
            $rules[] = $selector . ' .ivyforms-form-title { display: none !important; }';
        }

        if (isset($attributes['showDescription']) && $attributes['showDescription'] === false) {
            $rules[] = $selector . ' .ivyforms-form-description { display: none !important; }';
        }

        return implode('', $rules);
    }

    /**
     * Build the final block HTML
     *
     * @param string $wrapperAttrsString
     * @param string $formOutput
     * @param string $customStyles
     * @param string $embeddedScripts
     * @return string
     */
    public static function buildBlockHtml(
        string $wrapperAttrsString,
        string $formOutput,
        string $customStyles,
        string $embeddedScripts
    ): string {
        return $customStyles . '<div' . $wrapperAttrsString . '>' . $formOutput . '</div>' . $embeddedScripts;
    }
}
