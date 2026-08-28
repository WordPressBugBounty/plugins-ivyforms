<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

/**
 * Registers uploaded files in the WordPress media library.
 */
final class SubmissionUploadMediaRegistrar
{
    public static function register(string $file): string
    {
        self::ensureMediaFunctionsLoaded();

        $attachId = self::insertAttachment($file);
        if ($attachId === 0) {
            return '';
        }

        self::generateMetadata($attachId, $file);

        return self::publicUrl($attachId);
    }

    private static function ensureMediaFunctionsLoaded(): void
    {
        if (!function_exists('wp_insert_attachment')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
    }

    private static function insertAttachment(string $file): int
    {
        $fileName = basename($file);
        $fileType = wp_check_filetype($fileName, null);
        $mime = is_array($fileType) && !empty($fileType['type']) ? $fileType['type'] : '';
        $attachment = [
            'post_mime_type' => $mime,
            'post_title' => preg_replace('/\.[^.]+$/', '', $fileName),
            'post_content' => '',
            'post_status' => 'inherit',
        ];
        /** @var int|\WP_Error $attachId */
        $attachId = wp_insert_attachment($attachment, $file);
        if (is_wp_error($attachId) || !$attachId) {
            return 0;
        }

        return (int) $attachId;
    }

    private static function generateMetadata(int $attachId, string $file): void
    {
        $meta = wp_generate_attachment_metadata($attachId, $file);
        if (is_array($meta) && $meta !== []) {
            wp_update_attachment_metadata($attachId, $meta);
        }
    }

    private static function publicUrl(int $attachId): string
    {
        $attachmentUrl = wp_get_attachment_url($attachId);

        return $attachmentUrl ? (string) $attachmentUrl : '';
    }
}
