<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Sanitizer;

class HtmlSanitizer
{
    /**
     * Normalize mixed API values to a string for HTML sanitization (avoids PHP 8 TypeError).
     *
     * @param mixed $value
     */
    private static function toHtmlString($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * @param array<mixed> $value
     */
    private static function implodeCssClassArray(array $value): string
    {
        $parts = [];
        foreach ($value as $classPart) {
            if (is_string($classPart) || is_int($classPart) || is_float($classPart)) {
                $trimmed = trim((string) $classPart);
                if ($trimmed !== '') {
                    $parts[] = $trimmed;
                }
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Normalize cssClasses from REST (string, list of strings, or other scalar).
     *
     * @param mixed $value
     */
    private static function toCssClassesString($value): string
    {
        if ($value === null || $value === '' || $value === false) {
            return '';
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_array($value)) {
            return self::implodeCssClassArray($value);
        }
        if (is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    /**
     * @return array{string, array<string, string>}
     */
    private static function replaceDataImgSrcWithPlaceholders(string $content): array
    {
        $placeholders = [];
        $index = 0;
        $replaced = preg_replace_callback(
            '/src\s*=\s*["\']?(data:image\/[^"\'>\s]+)["\']?/i',
            static function (array $matches) use (&$placeholders, &$index): string {
                $placeholder = 'IMGDATA' . $index;
                $placeholders[$placeholder] = $matches[1];
                $index++;

                return 'data-img-src="' . $placeholder . '"';
            },
            $content
        );

        return [$replaced ?? '', $placeholders];
    }

    /**
     * @return array{string, array<string, string>}
     */
    private static function replaceInlineStyleAttributesWithPlaceholders(string $content): array
    {
        $stylePlaceholders = [];
        $placeholderIndex = 0;

        $styleToPlaceholder = static function (array $matches) use (&$stylePlaceholders, &$placeholderIndex): string {
            $placeholder = '___STYLE_PLACEHOLDER_' . $placeholderIndex . '___';
            $stylePlaceholders[$placeholder] = CssSanitizer::sanitizeCssValue($matches[2]);
            $placeholderIndex++;

            return $matches[1] . ' data-style-placeholder="' . $placeholder . '"';
        };

        $content = preg_replace_callback(
            '/(<[^>]+)\s+style="([^"]*)"/i',
            $styleToPlaceholder,
            $content
        ) ?? '';
        $content = preg_replace_callback(
            "/(<[^>]+)\s+style='([^']*)'/i",
            $styleToPlaceholder,
            $content
        ) ?? '';

        return [$content, $stylePlaceholders];
    }

    /**
     * @return array<string, array<string, bool>>
     */
    public static function buildEditorWpKsesAllowedTags(): array
    {
        $allowedTags = wp_kses_allowed_html('post');

        $tagsWithStyle = [
            'span',
            'p',
            'div',
            'h1',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6',
            'li',
            'ul',
            'ol',
            'blockquote',
            'strong',
            'em',
            'u',
            'a',
        ];

        foreach ($tagsWithStyle as $tag) {
            if (!isset($allowedTags[$tag])) {
                $allowedTags[$tag] = [];
            }
            if (!is_array($allowedTags[$tag])) {
                $allowedTags[$tag] = [];
            }
            $allowedTags[$tag]['data-style-placeholder'] = true;
            $allowedTags[$tag]['class'] = true;
        }

        if (!isset($allowedTags['a'])) {
            $allowedTags['a'] = [];
        }
        $allowedTags['a']['href'] = true;
        $allowedTags['a']['target'] = true;
        $allowedTags['a']['rel'] = true;
        $allowedTags['a']['class'] = true;
        $allowedTags['a']['data-style-placeholder'] = true;

        if (!isset($allowedTags['img'])) {
            $allowedTags['img'] = [];
        }
        $allowedTags['img']['src'] = true;
        $allowedTags['img']['alt'] = true;
        $allowedTags['img']['class'] = true;
        $allowedTags['img']['style'] = true;
        $allowedTags['img']['width'] = true;
        $allowedTags['img']['height'] = true;
        $allowedTags['img']['data-img-src'] = true;

        return $allowedTags;
    }

    /**
     * @param array<string, string> $dataUrlPlaceholders
     */
    private static function restoreEditorImgPlaceholders(string $html, array $dataUrlPlaceholders): string
    {
        $out = preg_replace_callback(
            '/<img[^>]*data-img-src=["\']?(IMGDATA\d+)["\']?[^>]*>/i',
            static function (array $matches) use ($dataUrlPlaceholders): string {
                $imgTag = $matches[0];
                $placeholderMatch = [];
                if (!preg_match('/data-img-src=["\']?(IMGDATA\d+)["\']?/i', $imgTag, $placeholderMatch)) {
                    return $matches[0];
                }
                $placeholder = $placeholderMatch[1];
                if (!isset($dataUrlPlaceholders[$placeholder])) {
                    return $matches[0];
                }
                $imgTag = preg_replace('/\s*data-img-src=["\']?IMGDATA\d+["\']?/i', '', $imgTag);
                if (!preg_match('/\ssrc=["\']?[^"\'>]+["\']?/i', $imgTag)) {
                    $imgTag = str_replace('>', ' src="' . $dataUrlPlaceholders[$placeholder] . '">', $imgTag);
                }

                return $imgTag;
            },
            $html
        );

        return $out ?? $html;
    }

    /**
     * @param array<string, string> $stylePlaceholders
     */
    private static function restoreEditorStylePlaceholders(string $html, array $stylePlaceholders): string
    {
        $out = preg_replace_callback(
            '/data-style-placeholder="(___STYLE_PLACEHOLDER_\d+___)"/i',
            static function (array $matches) use ($stylePlaceholders): string {
                $placeholder = $matches[1];
                if (!isset($stylePlaceholders[$placeholder])) {
                    return '';
                }
                $sanitizedStyle = $stylePlaceholders[$placeholder];
                if (!empty($sanitizedStyle)) {
                    return 'style="' . $sanitizedStyle . '"';
                }

                return '';
            },
            $html
        );

        return $out ?? $html;
    }

    /**
     * Sanitize HTML editor content with support for inline styles (colors, formatting)
     *
     * @param mixed $content
     *
     * @return string
     */
    public static function sanitizeEditorContent($content): string
    {
        $content = self::toHtmlString($content);
        [$content, $dataUrlPlaceholders] = self::replaceDataImgSrcWithPlaceholders($content);
        [$content, $stylePlaceholders] = self::replaceInlineStyleAttributesWithPlaceholders($content);
        $allowedTags = self::buildEditorWpKsesAllowedTags();
        $result = wp_kses($content, $allowedTags);
        $result = self::restoreEditorImgPlaceholders($result, $dataUrlPlaceholders);
        $result = self::restoreEditorStylePlaceholders($result, $stylePlaceholders);

        return $result;
    }

    /**
     * Sanitize agreement text allowing basic HTML tags (links, formatting)
     *
     * @param mixed $content The agreement text to sanitize
     *
     * @return string The sanitized agreement text
     */
    public static function sanitizeAgreementText($content): string
    {
        return self::sanitizeBasicHtml(self::toHtmlString($content));
    }

    /**
     * Sanitize field description allowing basic HTML tags (links, formatting)
     *
     * @param mixed $content The field description to sanitize
     *
     * @return string The sanitized field description
     */
    public static function sanitizeFieldDescription($content): string
    {
        return self::sanitizeBasicHtml(self::toHtmlString($content));
    }

    /**
     * Sanitize basic HTML content (links, formatting)
     *
     * @param string $content The HTML content to sanitize
     *
     * @return string The sanitized HTML content
     */
    private static function sanitizeBasicHtml(string $content): string
    {
        // Define allowed HTML tags for agreement text and field descriptions.
        // Keep in sync with SafeHTML field-type "field-description" (href, title, class; rel/target enforced on save).
        $formattingAttrs = [
            'class' => true,
            'title' => true,
        ];
        $allowedTags = [
            'a' => [
                'href'   => true,
                'target' => true,
                'rel'    => true,
                'title'  => true,
                'class'  => true,
            ],
            'strong' => $formattingAttrs,
            'b'      => $formattingAttrs,
            'em'     => $formattingAttrs,
            'i'      => $formattingAttrs,
            'u'      => $formattingAttrs,
            'br'     => $formattingAttrs,
            'p'      => $formattingAttrs,
            'span'   => $formattingAttrs,
        ];

        // Keep in sync with BASIC_HTML_ALLOWED_URI_REGEXP (http, https, mailto; relative URLs have no scheme).
        $allowedProtocols = ['http', 'https', 'mailto'];

        // Sanitize with allowed tags and protocols
        $sanitized = wp_kses($content, $allowedTags, $allowedProtocols);

        // Ensure external links open in a new tab with rel="noopener noreferrer"
        $sanitized = preg_replace_callback(
            '/<a\s+([^>]*href=["\']https?:\/\/[^"\']+["\'][^>]*)>/i',
            function ($matches) {
                $attrs = $matches[1];
                if (stripos($attrs, 'target=') === false) {
                    $attrs .= ' target="_blank"';
                }
                if (stripos($attrs, 'rel=') === false) {
                    $attrs .= ' rel="noopener noreferrer"';
                }
                return '<a ' . $attrs . '>';
            },
            $sanitized
        );

        return $sanitized;
    }

    /**
     * Get allowed HTML tags and attributes for wp_kses sanitization.
     * Includes support for signature images with data: URIs.
     *
     * @return array<string, array<string, bool>>
     */
    public static function getAllowedHtmlTags(): array
    {
        return [
            'img' => [
                'src' => true,
                'alt' => true,
                'style' => true,
                'class' => true,
                'width' => true,
                'height' => true,
            ],
            'a' => [
                'href' => true,
                'title' => true,
                'target' => true,
                'rel' => true,
            ],
            'p' => ['class' => true, 'style' => true],
            'br' => [],
            'strong' => [],
            'b' => [],
            'em' => [],
            'i' => [],
            'u' => [],
            'span' => ['class' => true, 'style' => true],
            'div' => ['class' => true, 'style' => true],
            'ul' => ['class' => true],
            'ol' => ['class' => true],
            'li' => ['class' => true],
            'h1' => ['class' => true, 'style' => true],
            'h2' => ['class' => true, 'style' => true],
            'h3' => ['class' => true, 'style' => true],
            'h4' => ['class' => true, 'style' => true],
            'h5' => ['class' => true, 'style' => true],
            'h6' => ['class' => true, 'style' => true],
            'blockquote' => ['class' => true, 'style' => true],
        ];
    }

    /**
     * Get allowed protocols for wp_kses sanitization.
     * Includes 'data' protocol for signature images.
     *
     * @return array<int, string>
     */
    public static function getAllowedProtocols(): array
    {
        return ['http', 'https', 'mailto', 'data'];
    }

    /**
     * Sanitize HTML content using wp_kses with support for signature images.
     *
     * @param mixed $content
     * @return string
     */
    public static function sanitizeHtmlContent($content): string
    {
        $content = self::toHtmlString($content);

        return wp_kses($content, self::getAllowedHtmlTags(), self::getAllowedProtocols());
    }

    /**
     * Sanitize a space-separated string of CSS classes
     *
     * Uses sanitize_html_class for each individual class name,
     * which is more appropriate than sanitize_text_field for CSS classes.
     *
     * @param mixed $classString Space-separated CSS class names, or a list of class names
     * @return string Sanitized space-separated CSS class names
     */
    public static function sanitizeCssClasses($classString): string
    {
        $classString = trim(self::toCssClassesString($classString));
        if ($classString === '') {
            return '';
        }

        $classes = preg_split('/\s+/', $classString, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $sanitized = array_map('sanitize_html_class', $classes);
        $sanitized = array_filter($sanitized);

        return implode(' ', $sanitized);
    }
}
