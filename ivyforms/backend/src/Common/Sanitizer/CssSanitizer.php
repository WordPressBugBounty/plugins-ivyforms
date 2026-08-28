<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Sanitizer;

class CssSanitizer
{
    /**
     * Patterns matched per declaration (not whole style string — `;` separates declarations).
     *
     * @return array<int, string>
     */
    private static function declarationDangerousPatterns(): array
    {
        return [
            '/expression\s*\(/i',
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/<\s*script/i',
            '/on\w+\s*=/i',
            '/import\s+/i',
            '/<!--/i',
            '/-->/i',
            '/behavior\s*:/i',
            '/-moz-binding\s*:/i',
            '/\{/',
            '/\}/',
            '/\/\*/',
            '/\*\//',
            '/\\\\/i',
        ];
    }

    private static function logBlockedDeclaration(string $declaration, string $pattern): void
    {
        $cssHash = substr(hash('sha256', $declaration), 0, 12);
        error_log(
            '🚨 BLOCKED dangerous CSS declaration' .
            ' | pattern=' . $pattern .
            ' | css_hash=' . $cssHash .
            ' | css_len=' . strlen($declaration)
        );
    }

    private static function isDangerousCssDeclaration(string $declaration): bool
    {
        foreach (self::declarationDangerousPatterns() as $pattern) {
            if (preg_match($pattern, $declaration)) {
                self::logBlockedDeclaration($declaration, $pattern);
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, string> $matches
     */
    private static function rewriteSingleCssUrlMatch(array $matches): string
    {
        $url = trim($matches[1]);

        if (preg_match('/[;{}]|\/\*|\*\//', $url)) {
            $urlHash = substr(hash('sha256', $url), 0, 12);
            error_log(
                '🚨 BLOCKED unsafe CSS url() rule fired' .
                ' | rule=declaration_breaking_tokens' .
                ' | url_hash=' . $urlHash .
                ' | url_len=' . strlen($url)
            );
            return '';
        }

        if (preg_match('/^(https?:|data:image\/)/i', $url)) {
            return 'url("' . esc_url_raw($url) . '")';
        }

        $urlHash = substr(hash('sha256', $url), 0, 12);
        error_log(
            '🚨 BLOCKED unsafe CSS url() rule fired' .
            ' | rule=allowed_url_protocol' .
            ' | url_hash=' . $urlHash .
            ' | url_len=' . strlen($url)
        );

        return '';
    }

    private static function rewriteUrlFunctionsInDeclaration(string $declaration): string
    {
        $out = preg_replace_callback(
            '/url\s*\(\s*["\']?([^"\')]+)["\']?\s*\)/i',
            /**
             * Explicit call so PHPMD counts `rewriteSingleCssUrlMatch` as used (callable array is not traced).
             *
             * @param array<int, string> $matches
             */
            static function (array $matches): string {
                return self::rewriteSingleCssUrlMatch($matches);
            },
            $declaration
        );

        return $out === null ? '' : trim((string) $out);
    }

    /**
     * Sanitize CSS value to prevent XSS attacks while allowing safe color/function values.
     *
     * After structural checks and `url()` handling, the result is passed through `esc_attr()`
     * so it is safe to embed in double-quoted HTML `style` attributes (matches legacy behavior).
     *
     * @param string $css Raw inline style declaration string.
     *
     * @return string Safe for use inside `style="..."`.
     */
    public static function sanitizeCssValue(string $css): string
    {
        $sanitizedDeclarations = self::sanitizeCssDeclarationList($css);
        if ($sanitizedDeclarations === '') {
            return '';
        }

        return esc_attr($sanitizedDeclarations);
    }

    /**
     * Sanitize a semicolon-separated declaration list for use inside a stylesheet rule block.
     *
     * Unlike sanitizeCssValue(), this returns raw CSS (not HTML-escaped) so it can be injected
     * into a <style> tag safely after declaration-level sanitization.
     *
     * @param string $css Raw CSS declaration list.
     * @return string
     */
    public static function sanitizeCssDeclarationList(string $css): string
    {
        $css = trim($css);
        if ($css === '') {
            return '';
        }

        $safeDeclarations = [];
        foreach (explode(';', $css) as $chunk) {
            $declaration = trim($chunk);
            if ($declaration === '') {
                continue;
            }
            if (self::isDangerousCssDeclaration($declaration)) {
                continue;
            }
            $sanitized = self::rewriteUrlFunctionsInDeclaration($declaration);
            if ($sanitized !== '') {
                $safeDeclarations[] = $sanitized;
            }
        }

        if ($safeDeclarations === []) {
            return '';
        }

        return implode('; ', $safeDeclarations);
    }

    /**
     * Sanitize a single CSS class name.
     *
     * @param mixed $value
     * @return string
     */
    public static function sanitizeCssClass($value): string
    {
        if (!is_string($value)) {
            return '';
        }

        return sanitize_html_class($value);
    }

    /**
     * Sanitize a comma-separated list of CSS classes.
     *
     * @param mixed $value
     * @return string
     */
    public static function sanitizeCssClassList($value): string
    {
        if (!is_string($value)) {
            return '';
        }

        $parts = preg_split('/[,\s]+/', $value) ?: [];
        $uniqueClasses = [];

        foreach ($parts as $part) {
            $sanitized = sanitize_html_class($part);
            if ($sanitized !== '') {
                $uniqueClasses[$sanitized] = true;
            }
        }

        return implode(', ', array_keys($uniqueClasses));
    }

    /**
     * Sanitize CSS unit value (px, rem, em, %, etc.)
     *
     * @param string $value
     * @return string
     */
    public static function sanitizeCssUnit(string $value): string
    {
        if (preg_match('/^-?[0-9.]+(?:px|rem|em|%|vh|vw|pt)?$/i', trim($value))) {
            return sanitize_text_field($value);
        }

        return '';
    }

    /**
     * Sanitize a CSS length or the keyword "auto" (e.g. width / height).
     *
     * @param string $value
     * @return string
     */
    public static function sanitizeCssUnitOrAuto(string $value): string
    {
        $trimmed = trim($value);
        if ('' === $trimmed) {
            return '';
        }

        if (preg_match('/^auto$/i', $trimmed)) {
            return 'auto';
        }

        return self::sanitizeCssUnit($trimmed);
    }

    /**
     * @return array<int, string>
     */
    private static function strictColorFunctionPatterns(): array
    {
        return [
            '/^rgba?\([0-9,.\s%]+\)$/i',
            '/^hsla?\([\d\s.,%\/-]+\)$/i',
            '/^hwb\([\d\s.,%\/-]+\)$/i',
            '/^var\(--[-\w]+\)$/i',
        ];
    }

    private static function matchStrictHexColor(string $color): ?string
    {
        if (!preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6}|[a-f0-9]{8})$/i', $color)) {
            return null;
        }

        return strlen($color) === 9 ? strtolower($color) : $color;
    }

    private static function matchStrictFunctionColor(string $color): ?string
    {
        foreach (self::strictColorFunctionPatterns() as $pattern) {
            if (preg_match($pattern, $color)) {
                return sanitize_text_field($color);
            }
        }

        return null;
    }

    private static function matchStrictNamedColor(string $color): ?string
    {
        if (!preg_match('/^[a-z]+$/i', $color) || strlen($color) > 20) {
            return null;
        }

        return sanitize_text_field($color);
    }

    private static function matchKnownStrictColorFormat(string $color): ?string
    {
        if (strcasecmp($color, 'transparent') === 0) {
            return 'transparent';
        }

        $hexColor = self::matchStrictHexColor($color);
        if ($hexColor !== null) {
            return $hexColor;
        }

        $functionColor = self::matchStrictFunctionColor($color);
        if ($functionColor !== null) {
            return $functionColor;
        }

        return self::matchStrictNamedColor($color);
    }

    /**
     * Sanitize color value for advanced style settings — rejects values unsafe for CSS output.
     *
     * @param string $color
     * @return string
     */
    public static function sanitizeStrictColor(string $color): string
    {
        $color = trim($color);
        if ($color === '') {
            return '';
        }

        return self::matchKnownStrictColorFormat($color) ?? '';
    }
}
