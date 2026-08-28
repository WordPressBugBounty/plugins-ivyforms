<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

use IvyForms\Entity\Field\Field as FieldEntity;

/**
 * WordPress wp_check_filetype_and_ext filter for IvyForms multipart uploads.
 *
 * Finfo often reports JSON and similar types as text/plain while the filename uses .json;
 * without this filter wp_handle_upload() rejects the file even when extension checks passed.
 */
final class SubmissionUploadFiletypeFilter
{
    public const FILETYPE_FILTER = 'wp_check_filetype_and_ext';

    /** @var array<string, mixed>|null */
    private static ?array $context = null;

    /**
     * @param array<string, mixed> $fileStruct
     */
    public static function beginContext(array $fileStruct, ?FieldEntity $fieldEntity): void
    {
        self::$context = [
            'fileStruct' => $fileStruct,
            'fieldEntity' => $fieldEntity,
        ];
    }

    public static function endContext(): void
    {
        self::$context = null;
    }

    /**
     * @param array{ext: string|false, type: string|false, proper_filename?: string|false} $data
     * @param array<string, mixed>|string $file
     * @param array<string, string>|null $mimes
     *
     * @return array{ext: string|false, type: string|false, proper_filename?: string|false}
     */
    public static function filterFiletypeAndExt(
        array $data,
        $file,
        string $filename,
        ?array $mimes = null,
        string $realMime = ''
    ): array {
        unset($file, $realMime);

        if (self::$context === null) {
            return $data;
        }

        $ext = self::resolveExtensionForFilter($filename);
        if ($ext === '' || !self::isExtensionPermitted($ext)) {
            return $data;
        }

        return self::buildAcceptedFiletype($ext, $mimes);
    }

    private static function resolveExtensionForFilter(string $filename): string
    {
        /** @var array<string, mixed> $fileStruct */
        $fileStruct = self::$context['fileStruct'];
        /** @var ?FieldEntity $fieldEntity */
        $fieldEntity = self::$context['fieldEntity'];

        $uploadName = isset($fileStruct['name']) ? (string) $fileStruct['name'] : $filename;

        return self::resolveExtension($uploadName, $fileStruct, $fieldEntity);
    }

    /**
     * @param array<string, mixed> $fileStruct
     */
    private static function resolveExtension(
        string $uploadName,
        array $fileStruct,
        ?FieldEntity $fieldEntity
    ): string {
        $ext = self::extensionFromUploadName($uploadName);
        if (!$fieldEntity instanceof FieldEntity) {
            return $ext;
        }

        /**
         * @param string $resolved
         * @param array<string, mixed> $fileStruct
         * @param FieldEntity $fieldEntity
         */
        return (string) apply_filters(
            'ivyforms/file-upload/resolve_extension',
            $ext,
            $fileStruct,
            $fieldEntity
        );
    }

    private static function extensionFromUploadName(string $uploadName): string
    {
        return $uploadName !== '' ? strtolower(pathinfo($uploadName, PATHINFO_EXTENSION)) : '';
    }

    private static function isExtensionPermitted(string $ext): bool
    {
        /** @var ?FieldEntity $fieldEntity */
        $fieldEntity = self::$context['fieldEntity'];
        $allowed = self::resolveAllowedExtensions($fieldEntity);

        // Empty allow-list: defer to WordPress MIME validation (do not override).
        if ($allowed === []) {
            return false;
        }

        return in_array($ext, $allowed, true);
    }

    /**
     * @param array<string, string>|null $mimes
     *
     * @return array{ext: string, type: string, proper_filename: false}
     */
    private static function buildAcceptedFiletype(string $ext, ?array $mimes): array
    {
        $mimeMap = self::mimeMapForExtension($ext);
        $type = $mimeMap[$ext] ?? 'application/octet-stream';

        if (is_array($mimes) && $mimes !== [] && isset($mimes[$ext])) {
            $type = $mimes[$ext];
        }

        return [
            'ext' => $ext,
            'type' => $type,
            'proper_filename' => false,
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function resolveAllowedExtensions(?FieldEntity $fieldEntity): array
    {
        if (!$fieldEntity instanceof FieldEntity) {
            return [];
        }

        $allowed = SubmissionUploadFileValidator::normalizeAllowedExtensions(
            $fieldEntity->getFieldAdvancedSettings()->getAllowedFileExtensions()
        );
        $allowed = apply_filters('ivyforms/file-upload/allowed_extensions', $allowed, $fieldEntity);

        return SubmissionUploadFileValidator::normalizeAllowedExtensions($allowed);
    }

    /**
     * @return array<string, string>
     */
    private static function mimeMapForExtension(string $ext): array
    {
        /** @var array<string, string> $known */
        $known = [
            'json' => 'application/json',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'txt' => 'text/plain',
            'zip' => 'application/zip',
            'gz' => 'application/gzip',
            'gzip' => 'application/gzip',
            'rar' => 'application/vnd.rar',
            '7z' => 'application/x-7z-compressed',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
        ];

        return [
            $ext => $known[$ext] ?? 'application/octet-stream',
        ];
    }
}
