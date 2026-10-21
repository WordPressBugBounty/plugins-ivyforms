<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Common\Upload;

/**
 * Reads a MIME type from a local file without trusting the client filename.
 */
final class UploadMimeSniffer
{
    public static function sniff(string $path): string
    {
        if ($path === '' || !is_file($path)) {
            return '';
        }

        $detected = self::sniffWithFileinfo($path);
        if ($detected !== '') {
            return $detected;
        }

        return self::sniffWithMimeContentType($path);
    }

    private static function sniffWithFileinfo(string $path): string
    {
        if (!function_exists('finfo_open')) {
            return '';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return '';
        }

        $detected = finfo_file($finfo, $path);
        if (PHP_VERSION_ID < 80100) {
            finfo_close($finfo);
        }

        if (!is_string($detected) || $detected === '') {
            return '';
        }

        return self::normalizeMime($detected);
    }

    private static function sniffWithMimeContentType(string $path): string
    {
        if (!function_exists('mime_content_type')) {
            return '';
        }

        $detected = mime_content_type($path);
        if (!is_string($detected) || $detected === '') {
            return '';
        }

        return self::normalizeMime($detected);
    }

    private static function normalizeMime(string $mime): string
    {
        $separator = strpos($mime, ';');
        if ($separator !== false) {
            $mime = substr($mime, 0, $separator);
        }

        return strtolower(trim($mime));
    }
}
