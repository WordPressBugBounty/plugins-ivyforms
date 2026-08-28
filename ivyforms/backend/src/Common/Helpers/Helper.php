<?php

namespace IvyForms\Common\Helpers;

class Helper
{
    /**
     * Parse JSON string or array data to array
     *
     * @param mixed $data
     *
     * @return array<string, mixed>
     */
    public static function parseToArray($data): array
    {
        $parsedData = [];
        if (!empty($data)) {
            $parsedData = is_string($data)
                ? json_decode($data, true)
                : $data;
        }
        return $parsedData;
    }

    /**
     * Convert page ID to page URL using WordPress get_permalink
     *
     * @param string|int $pageId The page ID to convert
     *
     * @return string The page permalink URL, or empty string if not found
     */
    public static function getPageUrlFromId($pageId): string
    {
        if (empty($pageId)) {
            return '';
        }

        $pagePermalink = get_permalink((int) $pageId);
        return $pagePermalink ? $pagePermalink : '';
    }
}
