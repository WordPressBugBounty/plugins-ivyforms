<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Common\Upload;

/**
 * Allow-list rules for form file uploads.
 *
 * Dangerous extensions are rejected even when WordPress would allow them for a
 * user with unfiltered_html (html, js) or when a site adds them through upload_mimes.
 *
 * svg and svgz are in that set because SVG can carry active content. Extension
 * presets must not offer them. A saved allow-list that contains only dangerous
 * extensions stays restricted; it does not fall through to "all types".
 */
final class UploadFileExtensionPolicy
{
    /**
     * Extensions that must not be saved on a field or stored from a submission.
     *
     * @var array<string, true>
     */
    private const DANGEROUS = [
        'php' => true,
        'php2' => true,
        'php3' => true,
        'php4' => true,
        'php5' => true,
        'php6' => true,
        'php7' => true,
        'php8' => true,
        'phtml' => true,
        'phtm' => true,
        'pht' => true,
        'phar' => true,
        'phps' => true,
        'phpt' => true,
        'pgif' => true,
        'inc' => true,
        'shtml' => true,
        'shtm' => true,
        'stm' => true,
        'html' => true,
        'htm' => true,
        'xhtml' => true,
        'hta' => true,
        'htaccess' => true,
        'htpasswd' => true,
        'cgi' => true,
        'pl' => true,
        'asp' => true,
        'aspx' => true,
        'asa' => true,
        'asax' => true,
        'ashx' => true,
        'asmx' => true,
        'cer' => true,
        'jsp' => true,
        'jspx' => true,
        'js' => true,
        'mjs' => true,
        'svg' => true,
        'svgz' => true,
        'exe' => true,
        'dll' => true,
        'bat' => true,
        'cmd' => true,
        'com' => true,
        'scr' => true,
        'sh' => true,
        'vbs' => true,
        'jar' => true,
    ];

    /**
     * fileinfo results that do not identify a single safe extension.
     *
     * @var array<string, true>
     */
    private const NONSPECIFIC_MIMES = [
        'application/octet-stream' => true,
        'application/encrypted' => true,
        'application/cdfv2-encrypted' => true,
        'application/zip' => true,
    ];

    public static function isDangerous(string $extension): bool
    {
        $extension = self::normalize($extension);
        if ($extension === '') {
            return false;
        }

        if (isset(self::DANGEROUS[$extension])) {
            return true;
        }

        return (bool) preg_match('/^php\d+$/', $extension);
    }

    /**
     * True when every listed extension is dangerous.
     *
     * An empty list is not dangerous-only. Callers use an empty allow-list for
     * "no restriction".
     *
     * @param array<int, mixed> $extensions
     */
    public static function containsOnlyDangerous(array $extensions): bool
    {
        $sawExtension = false;
        foreach ($extensions as $item) {
            if (!is_string($item)) {
                continue;
            }

            $extension = self::normalize($item);
            if ($extension === '') {
                continue;
            }

            $sawExtension = true;
            if (!self::isDangerous($extension)) {
                return false;
            }
        }

        return $sawExtension;
    }

    /**
     * True when the extension is in get_allowed_mime_types() and is not dangerous.
     */
    public static function isWordPressAllowed(string $extension): bool
    {
        return self::mimeForExtension($extension) !== null;
    }

    public static function mimeForExtension(string $extension): ?string
    {
        $extension = self::normalize($extension);
        if ($extension === '' || self::isDangerous($extension)) {
            return null;
        }

        foreach (self::allowedMimeTypes() as $extensions => $mime) {
            $parts = explode('|', strtolower((string) $extensions));
            if (!in_array($extension, $parts, true)) {
                continue;
            }

            $normalizedMime = strtolower(trim((string) $mime));

            return $normalizedMime !== '' ? $normalizedMime : null;
        }

        return null;
    }

    /**
     * First non-dangerous extension WordPress maps to this MIME, if any.
     */
    public static function extensionForMime(string $mime): ?string
    {
        $mime = strtolower(trim($mime));
        if ($mime === '' || self::isNonspecificMime($mime)) {
            return null;
        }

        foreach (self::allowedMimeTypes() as $extensions => $allowedMime) {
            if (strtolower(trim((string) $allowedMime)) !== $mime) {
                continue;
            }

            foreach (explode('|', strtolower((string) $extensions)) as $extension) {
                if ($extension !== '' && !self::isDangerous($extension)) {
                    return $extension;
                }
            }
        }

        return null;
    }

    public static function isNonspecificMime(string $mime): bool
    {
        return isset(self::NONSPECIFIC_MIMES[strtolower(trim($mime))]);
    }

    /**
     * Apache rules that deny script types already stored under the entry directory.
     */
    public static function apacheScriptGuard(): string
    {
        $extensions = array_keys(self::DANGEROUS);
        $pattern = '(?i)\.(' . implode('|', $extensions) . '|php\\d+)$';

        return "# IvyForms upload execution guard\n"
            . "<FilesMatch \"{$pattern}\">\n"
            . "    <IfModule mod_authz_core.c>\n"
            . "        Require all denied\n"
            . "    </IfModule>\n"
            . "    <IfModule !mod_authz_core.c>\n"
            . "        Deny from all\n"
            . "    </IfModule>\n"
            . "</FilesMatch>\n";
    }

    private static function normalize(string $extension): string
    {
        return strtolower(ltrim(trim($extension), '.'));
    }

    /**
     * @return array<string, string>
     */
    private static function allowedMimeTypes(): array
    {
        if (!function_exists('get_allowed_mime_types')) {
            return [];
        }

        return get_allowed_mime_types();
    }

    /**
     * Lowercase, de-duplicate, and drop dangerous extensions.
     *
     * @param array<int, mixed> $allowed
     *
     * @return array<int, string>
     */
    public static function normalizeExtensions(array $allowed): array
    {
        $out = [];
        $seen = [];
        foreach ($allowed as $item) {
            if (!is_string($item)) {
                continue;
            }
            $ext = self::normalize($item);
            if ($ext === '' || isset($seen[$ext]) || self::isDangerous($ext)) {
                continue;
            }
            $seen[$ext] = true;
            $out[] = $ext;
        }

        return $out;
    }
}
