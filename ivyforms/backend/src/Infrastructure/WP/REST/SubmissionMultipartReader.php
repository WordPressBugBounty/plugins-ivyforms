<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Infrastructure\WP\REST;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;

/**
 * Reads multipart file params and decodes the ivyforms_fm upload map.
 */
final class SubmissionMultipartReader
{
    /**
     * @param WP_REST_Request<array<string, mixed>> $request
     *
     * @return array<string, mixed>
     */
    public static function resolveUploadedFiles(WP_REST_Request $request): array
    {
        return $request->get_file_params();
    }

    /**
     * @return array<string, string>
     */
    public static function decodeFileParamMap(string $mapRaw): array
    {
        /** @var array<string, mixed> $decodedMap */
        $decodedMap = json_decode(wp_unslash($mapRaw), true);
        if (!is_array($decodedMap)) {
            throw new ValidationException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $normalized = [];
        foreach ($decodedMap as $paramName => $logicalKey) {
            if (is_string($paramName) && is_string($logicalKey)) {
                $normalized[$paramName] = $logicalKey;
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, string> $decodedMap
     */
    public static function resolveLogicalUploadKey(string $paramName, array $decodedMap): ?string
    {
        if (strpos($paramName, 'ivyforms_fu_') !== 0 || !isset($decodedMap[$paramName])) {
            return null;
        }
        $logicalKey = $decodedMap[$paramName];

        return strpos($logicalKey, 'file-upload_') === 0 ? $logicalKey : null;
    }

    /**
     * @param array<string, mixed> $filesLookup
     * @param array<string, string> $decodedMap
     *
     * @return array<string, int>
     */
    public static function groupUploadCountsByLogicalKey(array $filesLookup, array $decodedMap): array
    {
        /** @var array<string, int> $counts */
        $counts = [];

        foreach ($filesLookup as $paramName => $fileStruct) {
            $logicalKey = self::resolveLogicalUploadKey((string) $paramName, $decodedMap);
            if ($logicalKey === null || !is_array($fileStruct)) {
                continue;
            }
            if (($fileStruct['error'] ?? \UPLOAD_ERR_NO_FILE) === \UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if (!isset($counts[$logicalKey])) {
                $counts[$logicalKey] = 0;
            }
            $counts[$logicalKey]++;
        }

        return $counts;
    }
}
