<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

use IvyForms\Common\Exceptions\FieldValidationException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\Field\Field as FieldEntity;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Validates multipart file parts before they are stored.
 */
final class SubmissionUploadFileValidator
{
    /**
     * @param array<string, mixed> $fileStruct
     */
    public static function assertUploadSucceeded(array $fileStruct, ?string $logicalKey = null): void
    {
        if (($fileStruct['error'] ?? \UPLOAD_ERR_OK) !== \UPLOAD_ERR_OK) {
            self::fail('file_upload_failed', $logicalKey);
        }
    }

    /**
     * @param array<string, mixed> $fileStruct
     */
    public static function assertValidTmpFile(array $fileStruct, ?string $logicalKey = null): void
    {
        $tmp = isset($fileStruct['tmp_name']) ? (string) $fileStruct['tmp_name'] : '';
        if ($tmp === '') {
            self::fail('file_upload_failed', $logicalKey);
        }

        if (is_uploaded_file($tmp)) {
            return;
        }

        // REST/multipart uploads may use a readable temp path without is_uploaded_file().
        if (self::hasPositiveFileSize($tmp)) {
            return;
        }

        self::fail('file_upload_failed', $logicalKey);
    }

    private static function hasPositiveFileSize(string $path): bool
    {
        if (!is_readable($path)) {
            return false;
        }

        $size = filesize($path);

        return $size !== false && $size > 0;
    }

    public static function assertFileSizeWithinLimits(
        int $size,
        ?FieldEntity $fieldEntity,
        ?string $logicalKey = null
    ): void {
        $maxBytes = wp_max_upload_size();
        if ($maxBytes > 0 && $size > $maxBytes) {
            self::fail('file_upload_failed', $logicalKey);
        }

        if (!$fieldEntity instanceof FieldEntity) {
            return;
        }

        $maxMb = $fieldEntity->getFieldAdvancedSettings()->getMaxFileSizeMb();
        if ($maxMb <= 0 || $size <= $maxMb * 1024 * 1024) {
            return;
        }

        self::fail('file_upload_failed', $logicalKey);
    }

    /**
     * @param array<string, mixed> $fileStruct
     */
    public static function assertAllowedExtension(
        array $fileStruct,
        ?FieldEntity $fieldEntity,
        ?string $logicalKey = null
    ): void {
        if (!$fieldEntity instanceof FieldEntity) {
            return;
        }

        $uploadName = isset($fileStruct['name']) ? (string) $fileStruct['name'] : '';
        $ext = $uploadName !== '' ? strtolower(pathinfo($uploadName, PATHINFO_EXTENSION)) : '';

        /**
         * @param string $resolved
         * @param array<string, mixed> $fileStruct
         * @param FieldEntity $fieldEntity
         */
        $ext = (string) apply_filters(
            'ivyforms/file-upload/resolve_extension',
            $ext,
            $fileStruct,
            $fieldEntity
        );

        $allowed = $fieldEntity->getFieldAdvancedSettings()->getAllowedFileExtensions();
        $allowed = self::normalizeAllowedExtensions($allowed);
        $allowed = apply_filters('ivyforms/file-upload/allowed_extensions', $allowed, $fieldEntity);
        $allowed = self::normalizeAllowedExtensions($allowed);

        if ($allowed === []) {
            if (self::isExtensionAllowedByWordPressMimes($uploadName, $ext)) {
                return;
            }

            self::fail('file_upload_invalid_type', $logicalKey);
        }

        if ($ext !== '' && in_array($ext, $allowed, true)) {
            return;
        }

        self::fail('file_upload_invalid_type', $logicalKey);
    }

    private static function isExtensionAllowedByWordPressMimes(string $uploadName, string $ext): bool
    {
        if ($ext === '') {
            return false;
        }

        $checked = wp_check_filetype($uploadName);

        return $checked['ext'] !== false
            && strtolower($checked['ext']) === $ext;
    }

    /**
     * @param array<int, mixed> $allowed
     *
     * @return array<int, string>
     */
    public static function normalizeAllowedExtensions(array $allowed): array
    {
        $out = [];
        $seen = [];
        foreach ($allowed as $item) {
            if (!is_string($item)) {
                continue;
            }
            $ext = strtolower(ltrim(trim($item), '.'));
            if ($ext === '' || isset($seen[$ext])) {
                continue;
            }
            $seen[$ext] = true;
            $out[] = $ext;
        }

        return $out;
    }

    public static function assertUploadCountWithinLimits(
        int $count,
        ?FieldEntity $fieldEntity,
        ?string $logicalKey = null
    ): void {
        if (!$fieldEntity instanceof FieldEntity || $count === 0) {
            return;
        }

        /** @var array{allowMultiple: bool, maxUploadCount: int} $limits */
        $limits = apply_filters(
            'ivyforms/file-upload/upload_limits',
            [
                'allowMultiple' => false,
                'maxUploadCount' => 1,
            ],
            $fieldEntity
        );

        if (!$limits['allowMultiple'] && $count > 1) {
            self::fail('file_upload_too_many_files', $logicalKey);
        }

        $maxCount = $limits['maxUploadCount'];
        if ($limits['allowMultiple'] && $maxCount > 0 && $count > $maxCount) {
            self::fail('file_upload_too_many_files', $logicalKey);
        }
    }

    /**
     * @param array<int, FieldEntity> $formFields
     */
    public static function findFieldForUploadKey(array $formFields, string $logicalKey): ?FieldEntity
    {
        if (!preg_match('/^file-upload_(\d+)$/', $logicalKey, $matches)) {
            return null;
        }
        $idx = (int) $matches[1];
        foreach ($formFields as $field) {
            if ($field->getType() === 'file-upload' && $field->getIndex() === $idx) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @throws ValidationException
     * @throws FieldValidationException
     */
    private static function fail(string $code, ?string $logicalKey = null): void
    {
        $strings = BackendStrings::getExceptionStrings();
        $message = $strings[$code] ?? $strings['file_upload_failed'];

        if ($logicalKey !== null && $logicalKey !== '') {
            throw new FieldValidationException($message, [
                $logicalKey => ['code' => $code],
            ]);
        }

        throw new ValidationException($message);
    }
}
