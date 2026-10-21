<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

use IvyForms\Common\Upload\UploadFileExtensionPolicy;

/**
 * Redirects form submission file uploads to the IvyForms entry folder.
 */
final class SubmissionUploadDirectoryFilter
{
    private const ENTRY_SUBDIR = '/ivyforms/entry';

    private const GUARD_MARKER = '# IvyForms upload execution guard';

    /**
     * Block script execution for files already stored before CVE-2026-109384 was fixed.
     * Skips sites that have never created the entry directory.
     */
    public static function ensureEntryScriptGuard(): void
    {
        if (!function_exists('wp_upload_dir')) {
            return;
        }

        $uploads = wp_upload_dir(null, false);
        if (!is_array($uploads) || !empty($uploads['error']) || empty($uploads['basedir'])) {
            return;
        }

        $directory = (string) $uploads['basedir'] . self::ENTRY_SUBDIR;
        if (!is_dir($directory)) {
            return;
        }

        self::writeScriptGuard($directory);
    }

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
        self::writeScriptGuard($dirs['path']);

        return $dirs;
    }

    private static function writeScriptGuard(string $directory): void
    {
        if ($directory === '' || !is_dir($directory)) {
            return;
        }

        $path = rtrim($directory, '/\\') . '/.htaccess';
        $contents = UploadFileExtensionPolicy::apacheScriptGuard();
        if (is_file($path)) {
            $existing = file_get_contents($path);
            if ($existing === $contents) {
                return;
            }
            if (!is_string($existing) || strpos($existing, self::GUARD_MARKER) === false) {
                return;
            }
        }

        $written = file_put_contents($path, $contents);
        if ($written === false) {
            // Keep accepting the upload. Operators still need a log line to fix the open directory.
            error_log(sprintf(
                'IvyForms: could not write the upload execution guard to %s. '
                . 'Fix directory permissions so existing files cannot be executed.',
                $path
            ));
        }
    }
}
