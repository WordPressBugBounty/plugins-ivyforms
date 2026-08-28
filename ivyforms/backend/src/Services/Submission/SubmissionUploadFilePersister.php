<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

use IvyForms\Common\Exceptions\FieldValidationException;
use IvyForms\Entity\Field\Field as FieldEntity;
use IvyForms\Infrastructure\WP\REST\SubmissionMultipartReader;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Validates and persists a single multipart file upload for form submission.
 */
final class SubmissionUploadFilePersister
{
    /**
     * @param array<string, mixed> $filesLookup
     * @param array<string, string> $decodedMap
     * @param array<int, FieldEntity> $formFields
     *
     * @return array<string, string[]>
     */
    public static function collectUploadedUrls(
        array $filesLookup,
        array $decodedMap,
        array $formFields
    ): array {
        /** @var array<string, string[]> $byLogicalKey */
        $byLogicalKey = [];

        foreach ($filesLookup as $paramName => $fileStruct) {
            $logicalKey = SubmissionMultipartReader::resolveLogicalUploadKey(
                (string) $paramName,
                $decodedMap
            );
            if ($logicalKey === null || !is_array($fileStruct)) {
                continue;
            }

            $url = self::processUploadedFile($fileStruct, $logicalKey, $formFields);
            if ($url === null) {
                continue;
            }

            if (!isset($byLogicalKey[$logicalKey])) {
                $byLogicalKey[$logicalKey] = [];
            }
            $byLogicalKey[$logicalKey][] = $url;
        }

        return $byLogicalKey;
    }

    /**
     * @param array<string, mixed> $fileStruct
     * @param array<int, FieldEntity> $formFields
     */
    public static function processUploadedFile(
        array $fileStruct,
        string $logicalKey,
        array $formFields
    ): ?string {
        if (($fileStruct['error'] ?? \UPLOAD_ERR_NO_FILE) === \UPLOAD_ERR_NO_FILE) {
            return null;
        }

        SubmissionUploadFileValidator::assertUploadSucceeded($fileStruct, $logicalKey);
        SubmissionUploadFileValidator::assertValidTmpFile($fileStruct, $logicalKey);

        $size = isset($fileStruct['size']) ? (int) $fileStruct['size'] : 0;
        $fieldEntity = SubmissionUploadFileValidator::findFieldForUploadKey($formFields, $logicalKey);
        SubmissionUploadFileValidator::assertFileSizeWithinLimits($size, $fieldEntity, $logicalKey);
        SubmissionUploadFileValidator::assertAllowedExtension($fileStruct, $fieldEntity, $logicalKey);

        $result = self::storeUploadedFile($fileStruct, $fieldEntity);
        if (isset($result['error'])) {
            self::throwUploadFieldError($logicalKey, 'file_upload_failed');
        }

        return self::resolvePublicFileUrl($result, $fieldEntity, $logicalKey);
    }

    /**
     * @param array<string, mixed> $fileStruct
     *
     * @return array<string, mixed>
     */
    private static function storeUploadedFile(array $fileStruct, ?FieldEntity $fieldEntity): array
    {
        $useIvyformsEntryDir = self::shouldUseIvyformsEntryDirectory($fieldEntity);

        SubmissionUploadFiletypeFilter::beginContext($fileStruct, $fieldEntity);
        add_filter(
            SubmissionUploadFiletypeFilter::FILETYPE_FILTER,
            [SubmissionUploadFiletypeFilter::class, 'filterFiletypeAndExt'],
            10,
            5
        );

        if ($useIvyformsEntryDir) {
            SubmissionUploadDirectoryFilter::beginIvyformsEntryContext();
        }

        try {
            $overrides = apply_filters(
                'ivyforms/file-upload/wp_handle_upload_overrides',
                ['test_form' => false],
                $fileStruct,
                $fieldEntity
            );
            if (!is_array($overrides)) {
                $overrides = ['test_form' => false];
            }

            $tmp = isset($fileStruct['tmp_name']) ? (string) $fileStruct['tmp_name'] : '';
            $useSideload = $tmp !== '' && !is_uploaded_file($tmp);

            return $useSideload
                ? wp_handle_sideload($fileStruct, $overrides)
                : wp_handle_upload($fileStruct, $overrides);
        } finally {
            if ($useIvyformsEntryDir) {
                SubmissionUploadDirectoryFilter::endContext();
            }
            remove_filter(
                SubmissionUploadFiletypeFilter::FILETYPE_FILTER,
                [SubmissionUploadFiletypeFilter::class, 'filterFiletypeAndExt'],
                10
            );
            SubmissionUploadFiletypeFilter::endContext();
        }
    }

    /**
     * @param array<string, mixed> $result
     */
    private static function resolvePublicFileUrl(
        array $result,
        ?FieldEntity $fieldEntity,
        string $logicalKey
    ): string {
        $url = isset($result['url']) ? (string) $result['url'] : '';
        if ($url === '') {
            self::throwUploadFieldError($logicalKey, 'file_upload_failed');
        }

        if (
            !$fieldEntity instanceof FieldEntity
            || $fieldEntity->getFieldAdvancedSettings()->getSaveUploadsTo() !== 'media_library'
            || empty($result['file'])
        ) {
            return $url;
        }

        $mediaUrl = SubmissionUploadMediaRegistrar::register((string) $result['file']);

        return $mediaUrl !== '' ? $mediaUrl : $url;
    }

    private static function shouldUseIvyformsEntryDirectory(?FieldEntity $fieldEntity): bool
    {
        if (!$fieldEntity instanceof FieldEntity) {
            return false;
        }

        return $fieldEntity->getFieldAdvancedSettings()->getSaveUploadsTo() === 'ivyforms';
    }

    /**
     * @throws FieldValidationException
     */
    private static function throwUploadFieldError(string $logicalKey, string $code): void
    {
        $strings = BackendStrings::getExceptionStrings();
        $message = $strings[$code] ?? $strings['file_upload_failed'];

        throw new FieldValidationException($message, [
            $logicalKey => ['code' => $code],
        ]);
    }
}
