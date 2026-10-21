<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

use IvyForms\Common\Upload\UploadFileExtensionPolicy;
use IvyForms\Common\Upload\UploadMimeSniffer;

/**
 * Chooses the on-disk extension from the sniffed MIME before wp_handle_upload().
 */
final class SubmissionUploadStoredFilename
{
    private const REJECTED_EXTENSION = 'rejected';

    /**
     * Extensions finfo often reports as text/plain. These keep their own extension.
     *
     * @var array<string, array<int, string>>
     */
    private const SAFE_TYPE_MIMES = [
        'json' => ['application/json', 'text/json', 'text/plain'],
        'csv' => ['text/csv', 'application/csv', 'text/plain'],
        'txt' => ['text/plain'],
    ];

    private static ?string $sniffedMime = null;

    public static function reset(): void
    {
        self::$sniffedMime = null;
    }

    public static function sniffedMime(): string
    {
        return self::$sniffedMime ?? '';
    }

    public static function isSafeType(string $extension, string $realMime): bool
    {
        if (!isset(self::SAFE_TYPE_MIMES[$extension]) || UploadFileExtensionPolicy::isDangerous($extension)) {
            return false;
        }

        if ($realMime === '' || UploadFileExtensionPolicy::isNonspecificMime($realMime)) {
            return true;
        }

        return in_array($realMime, self::SAFE_TYPE_MIMES[$extension], true);
    }

    public static function primaryMime(string $extension): string
    {
        $mimes = self::SAFE_TYPE_MIMES[$extension];

        return $mimes[0];
    }

    /**
     * @param array<string, mixed> $fileStruct
     *
     * @return array<string, mixed>
     */
    public static function apply(array $fileStruct): array
    {
        $originalName = isset($fileStruct['name']) ? (string) $fileStruct['name'] : '';
        $name = self::stripCharactersBeyondUtf8($originalName);
        $clientExt = self::extensionFromName($name);
        $tmp = isset($fileStruct['tmp_name']) ? (string) $fileStruct['tmp_name'] : '';
        self::$sniffedMime = UploadMimeSniffer::sniff($tmp);
        $extension = self::storedExtension($clientExt, self::$sniffedMime) ?? $clientExt;
        if ($extension === '' || UploadFileExtensionPolicy::isDangerous($extension)) {
            $extension = self::REJECTED_EXTENSION;
        }

        $safeName = self::filenameWithExtension($name, $extension);
        if ($safeName !== $originalName) {
            $fileStruct['name'] = $safeName;
        }

        return $fileStruct;
    }

    /**
     * @return string|null Null keeps the client extension.
     */
    private static function storedExtension(string $clientExt, string $realMime): ?string
    {
        if (self::isSafeType($clientExt, $realMime)) {
            return null;
        }

        if ($realMime === '' || UploadFileExtensionPolicy::isNonspecificMime($realMime)) {
            return self::rejectedWhenDangerous($clientExt);
        }

        if (self::clientMimeMatches($clientExt, $realMime)) {
            return null;
        }

        $sniffedExt = UploadFileExtensionPolicy::extensionForMime($realMime);
        if ($sniffedExt !== null) {
            return $sniffedExt;
        }

        if (isset(self::SAFE_TYPE_MIMES[$clientExt])) {
            return self::REJECTED_EXTENSION;
        }

        return self::rejectedWhenDangerous($clientExt);
    }

    private static function clientMimeMatches(string $clientExt, string $realMime): bool
    {
        $clientMime = UploadFileExtensionPolicy::mimeForExtension($clientExt);

        return $clientMime !== null && $clientMime === $realMime;
    }

    /**
     * @return string|null
     */
    private static function rejectedWhenDangerous(string $clientExt): ?string
    {
        if (UploadFileExtensionPolicy::isDangerous($clientExt)) {
            return self::REJECTED_EXTENSION;
        }

        return null;
    }

    private static function filenameWithExtension(string $filename, string $extension): string
    {
        $base = str_replace(["\0", '/', '\\'], '', self::stripDangerousExtensions($filename));
        $base = trim($base);
        if ($base === '' || $base === '.' || $base === '..') {
            $base = 'upload';
        }

        return $base . '.' . $extension;
    }

    private static function stripDangerousExtensions(string $filename): string
    {
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $kept = [];
        foreach (explode('.', $base) as $part) {
            if ($part === '' || UploadFileExtensionPolicy::isDangerous(strtolower($part))) {
                continue;
            }
            $kept[] = $part;
        }

        return trim(implode('.', $kept), '.');
    }

    /**
     * MySQL utf8 columns reject code points above U+FFFF (emoji and similar).
     */
    private static function stripCharactersBeyondUtf8(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $stripped = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $value);

        return is_string($stripped) ? $stripped : $value;
    }

    private static function extensionFromName(string $uploadName): string
    {
        if ($uploadName === '') {
            return '';
        }

        return strtolower(pathinfo($uploadName, PATHINFO_EXTENSION));
    }
}
