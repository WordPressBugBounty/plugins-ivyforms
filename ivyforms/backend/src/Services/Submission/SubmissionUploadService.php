<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

use IvyForms\Entity\Field\Field as FieldEntity;
use IvyForms\Infrastructure\WP\REST\SubmissionMultipartReader;

/**
 * Merges multipart file parts into submission values (file keys like file-upload_0).
 */
class SubmissionUploadService
{
    /**
     * @param array<string, mixed> $params Request params with decoded `values` array
     * @param array<string, mixed> $uploadedFiles From {@see SubmissionMultipartReader::resolveUploadedFiles()}
     * @param array<\IvyForms\Entity\Field\Field> $formFields
     *
     * @return array<string, mixed>
     */
    public function mergeUploadedFilesIntoValues(
        array $params,
        array $uploadedFiles,
        array $formFields
    ): array {
        if ($uploadedFiles === [] || !$this->hasMergeableValues($params)) {
            unset($params['ivyforms_fm']);

            return $params;
        }

        $mapRaw = isset($params['ivyforms_fm']) ? $params['ivyforms_fm'] : '';
        unset($params['ivyforms_fm']);
        if (!is_string($mapRaw) || $mapRaw === '') {
            return $params;
        }

        $decodedMap = SubmissionMultipartReader::decodeFileParamMap($mapRaw);
        $this->ensureUploadHelpersLoaded();

        $this->assertUploadCountsBeforePersist($uploadedFiles, $decodedMap, $formFields);

        $byLogicalKey = SubmissionUploadFilePersister::collectUploadedUrls(
            $uploadedFiles,
            $decodedMap,
            $formFields
        );

        return $this->applyUploadedUrls($params, $byLogicalKey);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, string[]> $byLogicalKey
     *
     * @return array<string, mixed>
     */
    private function applyUploadedUrls(array $params, array $byLogicalKey): array
    {
        foreach ($byLogicalKey as $logicalKey => $urls) {
            if ($urls === []) {
                continue;
            }
            $params['values'][$logicalKey] = count($urls) === 1 ? $urls[0] : wp_json_encode($urls);
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $uploadedFiles
     * @param array<string, string> $decodedMap
     * @param array<int, FieldEntity> $formFields
     */
    private function assertUploadCountsBeforePersist(
        array $uploadedFiles,
        array $decodedMap,
        array $formFields
    ): void {
        $countsByKey = SubmissionMultipartReader::groupUploadCountsByLogicalKey($uploadedFiles, $decodedMap);
        foreach ($countsByKey as $logicalKey => $count) {
            if ($count === 0) {
                continue;
            }
            $fieldEntity = SubmissionUploadFileValidator::findFieldForUploadKey($formFields, $logicalKey);
            SubmissionUploadFileValidator::assertUploadCountWithinLimits($count, $fieldEntity, $logicalKey);
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    private function hasMergeableValues(array $params): bool
    {
        return array_key_exists('values', $params) && is_array($params['values']);
    }

    private function ensureUploadHelpersLoaded(): void
    {
        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
    }
}
