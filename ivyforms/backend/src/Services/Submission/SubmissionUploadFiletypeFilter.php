<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

use IvyForms\Common\Upload\UploadFileExtensionPolicy;
use IvyForms\Entity\Field\Field as FieldEntity;

/**
 * WordPress wp_check_filetype_and_ext filter for IvyForms multipart uploads.
 *
 * Finfo often reports JSON and similar text types as text/plain. This filter
 * overrides WordPress only for that explicit safe-type map. Every other upload
 * keeps WordPress's own result, and the stored filename uses an extension
 * derived from the sniffed MIME.
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
        SubmissionUploadStoredFilename::reset();
    }

    /**
     * Replace the client extension with one derived from the file contents.
     *
     * @param array<string, mixed> $fileStruct
     *
     * @return array<string, mixed>
     */
    public static function applySniffedFilename(array $fileStruct): array
    {
        return SubmissionUploadStoredFilename::apply($fileStruct);
    }

    /**
     * @param array{ext: string|false, type: string|false, proper_filename?: string|false} $data
     * @param array<string, mixed>|string $file
     * @param array<string, string>|null $mimes
     * @param string|false $realMime
     *
     * @return array{ext: string|false, type: string|false, proper_filename?: string|false}
     */
    public static function filterFiletypeAndExt(
        array $data,
        $file,
        string $filename,
        ?array $mimes = null,
        $realMime = ''
    ): array {
        unset($file, $mimes);

        if (self::$context === null) {
            return $data;
        }

        $ext = self::resolveExtensionForFilter($filename);
        $sniffedMime = self::mimeForOverride(is_string($realMime) ? $realMime : '');
        if (!self::shouldOverrideFiletype($ext, $sniffedMime)) {
            return $data;
        }

        return [
            'ext' => $ext,
            'type' => SubmissionUploadStoredFilename::primaryMime($ext),
            'proper_filename' => false,
        ];
    }

    private static function shouldOverrideFiletype(string $ext, string $realMime): bool
    {
        if (!self::isExtensionPermitted($ext)) {
            return false;
        }

        return SubmissionUploadStoredFilename::isSafeType($ext, $realMime);
    }

    /**
     * WordPress leaves real_mime empty for extensions it does not recognise.
     */
    private static function mimeForOverride(string $wordPressMime): string
    {
        $fromWordPress = strtolower(trim($wordPressMime));
        $fromUpload = SubmissionUploadStoredFilename::sniffedMime();
        if ($fromUpload === '') {
            return $fromWordPress;
        }

        if ($fromWordPress === '' || $fromWordPress === 'text/plain') {
            return $fromUpload;
        }

        return $fromWordPress;
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
        return $uploadName !== '' ? strtolower((string) pathinfo($uploadName, PATHINFO_EXTENSION)) : '';
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
     * @return array<int, string>
     */
    private static function resolveAllowedExtensions(?FieldEntity $fieldEntity): array
    {
        if (!$fieldEntity instanceof FieldEntity) {
            return [];
        }

        $allowed = UploadFileExtensionPolicy::normalizeExtensions(
            $fieldEntity->getFieldAdvancedSettings()->getAllowedFileExtensions()
        );
        /** @var array<int, mixed> $allowed */
        $allowed = apply_filters('ivyforms/file-upload/allowed_extensions', $allowed, $fieldEntity);

        return UploadFileExtensionPolicy::normalizeExtensions($allowed);
    }
}
