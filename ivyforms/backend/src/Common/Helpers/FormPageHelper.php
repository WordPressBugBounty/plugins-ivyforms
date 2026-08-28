<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Common\Helpers;

class FormPageHelper
{
    /**
     * Group fields by page ID
     *
     * @param array<mixed> $fields
     * @return array<string, array<mixed>>
     */
    public static function groupFieldsByPageId(array $fields): array
    {
        $fieldsByPage = [];
        foreach ($fields as $field) {
            $pageId = $field['pageId'] ?? ($field['settings']['pageId'] ?? null);
            if ($pageId) {
                if (!isset($fieldsByPage[$pageId])) {
                    $fieldsByPage[$pageId] = [];
                }
                $fieldsByPage[$pageId][] = $field;
            }
        }
        return $fieldsByPage;
    }

    /**
     * Assign grouped fields to pages
     *
     * @param array<mixed> $pages
     * @param array<string, array<mixed>> $fieldsByPage
     * @return array<mixed>
     */
    public static function assignFieldsToPages(array $pages, array $fieldsByPage): array
    {
        foreach ($pages as &$page) {
            $pageId = $page['id'] ?? null;
            $page['fields'] = $fieldsByPage[$pageId] ?? [];
        }
        return $pages;
    }
}
