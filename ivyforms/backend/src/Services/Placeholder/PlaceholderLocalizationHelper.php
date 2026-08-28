<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Placeholder;

/**
 * Resolves {{wp.*}} placeholders in public form field strings at serve time.
 */
class PlaceholderLocalizationHelper
{
    /**
     * @return array<string, string|int>
     */
    public static function getGeneralData(): array
    {
        return PlaceholderService::buildGeneralData(0, [
            'postId' => self::resolvePostId(),
            'referer' => wp_get_referer() ?: '',
        ]);
    }

    /**
     * @param array<string, string|int> $generalData
     */
    public static function resolveFieldString(string $value, array $generalData): string
    {
        if (strpos($value, '{{') === false) {
            return $value;
        }

        return PlaceholderService::replacePlaceholders($value, [], $generalData);
    }

    private static function resolvePostId(): int
    {
        $postId = get_queried_object_id();
        if ($postId) {
            return (int) $postId;
        }

        $theId = get_the_ID();
        if ($theId) {
            return (int) $theId;
        }

        if (is_admin()) {
            $adminPostId = filter_input(INPUT_GET, 'post', FILTER_VALIDATE_INT);
            if (is_int($adminPostId) && $adminPostId > 0) {
                return $adminPostId;
            }
        }

        return 0;
    }
}
