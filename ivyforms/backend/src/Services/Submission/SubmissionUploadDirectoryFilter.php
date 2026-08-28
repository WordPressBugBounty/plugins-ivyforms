<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

/**
 * Redirects form submission file uploads to the IvyForms entry folder.
 */
final class SubmissionUploadDirectoryFilter
{
    private const ENTRY_SUBDIR = '/ivyforms/entry';

    public static function beginIvyformsEntryContext(): void
    {
        add_filter('upload_dir', [self::class, 'filterUploadDir'], 10, 1);
    }

    public static function endContext(): void
    {
        remove_filter('upload_dir', [self::class, 'filterUploadDir'], 10);
    }

    /**
     * @param array<string, string> $dirs
     *
     * @return array<string, string>
     */
    public static function filterUploadDir(array $dirs): array
    {
        $subdir = self::ENTRY_SUBDIR;
        $dirs['subdir'] = $subdir;
        $dirs['path'] = $dirs['basedir'] . $subdir;
        $dirs['url'] = $dirs['baseurl'] . $subdir;

        wp_mkdir_p($dirs['path']);

        return $dirs;
    }
}
