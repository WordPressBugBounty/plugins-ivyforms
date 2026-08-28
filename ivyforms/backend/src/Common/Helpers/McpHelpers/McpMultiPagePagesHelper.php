<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

/**
 * Page structure helpers for IvyForms MCP multipage operations.
 */
class McpMultiPagePagesHelper
{
    /**
     * @param array<int, mixed> $pageInputs
     * @return array<int, array<string, mixed>>
     */
    public static function buildPagesFromInput(array $pageInputs): array
    {
        $pages = [];

        foreach (array_values($pageInputs) as $index => $pageInput) {
            if (!is_array($pageInput)) {
                continue;
            }

            $label = sanitize_text_field((string) ($pageInput['label'] ?? sprintf('Page %d', $index + 1)));
            if ($label === '') {
                $label = sprintf('Page %d', $index + 1);
            }

            $pageId = sanitize_text_field((string) ($pageInput['id'] ?? sprintf('page_%d', $index + 1)));
            if ($pageId === '') {
                $pageId = sprintf('page_%d', $index + 1);
            }

            $pages[] = [
                'id'       => $pageId,
                'label'    => $label,
                'closable' => $index > 0 ? (bool) ($pageInput['closable'] ?? true) : false,
                'fields'   => [],
            ];
        }

        return $pages;
    }

    /**
     * @param array<string, mixed> $form
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function mergeProgressIndicator(array $form, array $input): array
    {
        if (isset($input['progressIndicator']) && is_array($input['progressIndicator'])) {
            $form['progressIndicator'] = $input['progressIndicator'];

            return $form;
        }

        if (!isset($form['progressIndicator']) || !is_array($form['progressIndicator'])) {
            $form['progressIndicator'] = McpMultiPageHelper::defaultProgressIndicator();
        }

        return $form;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function defaultFirstPage(): array
    {
        return [[
            'id'       => 'page_1',
            'label'    => 'Page 1',
            'closable' => false,
        ]];
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    public static function enrichResultWithAddedPage(array $result, string $label): array
    {
        if (empty($result['success']) || !is_array($result['pages'] ?? null)) {
            return $result;
        }

        $addedPage = self::findPageByLabel($result['pages'], $label);
        if ($addedPage !== null) {
            $result['addedPage'] = $addedPage;
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $pages
     * @return array<int, string>
     */
    public static function collectPageIds(array $pages): array
    {
        $pageIds = [];
        foreach ($pages as $page) {
            if (!is_array($page)) {
                continue;
            }
            $pageId = sanitize_text_field((string) ($page['id'] ?? ''));
            if ($pageId !== '') {
                $pageIds[] = $pageId;
            }
        }

        return $pageIds;
    }

    /**
     * @param array<int, array<string, mixed>> $fields
     * @param array<int, mixed> $assignments
     * @param array<int, string> $validPageIds
     * @return array<int, array<string, mixed>>
     */
    public static function applyFieldPageAssignments(
        array $fields,
        array $assignments,
        array $validPageIds,
        string $fallbackPageId
    ): array {
        $assignmentsByFieldId = self::normalizeFieldAssignments($assignments, $validPageIds);

        foreach ($fields as &$field) {
            if (!is_array($field)) {
                continue;
            }

            $fieldId = (int) ($field['id'] ?? 0);
            $targetPageId = $assignmentsByFieldId[$fieldId]
                ?? McpMultiPageHelper::extractFieldPageId($field)
                ?? $fallbackPageId;

            if (!in_array($targetPageId, $validPageIds, true)) {
                $targetPageId = $fallbackPageId;
            }

            McpMultiPageHelper::applyPageIdToField($field, $targetPageId);
        }
        unset($field);

        return $fields;
    }

    /**
     * @param array<int, array<string, mixed>> $pages
     * @param array<string, mixed> $newPage
     * @return array<int, array<string, mixed>>
     */
    public static function insertPage(array $pages, array $newPage, string $insertAfterPageId): array
    {
        if ($insertAfterPageId === '') {
            $pages[] = $newPage;

            return $pages;
        }

        $result = [];
        $inserted = false;

        foreach ($pages as $page) {
            $result[] = $page;
            $currentPageId = sanitize_text_field((string) ($page['id'] ?? ''));
            if ($currentPageId === $insertAfterPageId) {
                $result[] = $newPage;
                $inserted = true;
            }
        }

        if (!$inserted) {
            $result[] = $newPage;
        }

        return $result;
    }

    /**
     * @param array<int, mixed> $pages
     * @return array<int, array<string, mixed>>
     */
    public static function stripFieldsFromPages(array $pages): array
    {
        $normalized = [];

        foreach ($pages as $page) {
            if (!is_array($page)) {
                continue;
            }

            $normalized[] = [
                'id'       => sanitize_text_field((string) ($page['id'] ?? '')),
                'label'    => sanitize_text_field((string) ($page['label'] ?? '')),
                'closable' => (bool) ($page['closable'] ?? false),
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, mixed> $assignments
     * @param array<int, string> $validPageIds
     * @return array<int, string>
     */
    private static function normalizeFieldAssignments(array $assignments, array $validPageIds): array
    {
        $normalized = [];

        foreach ($assignments as $assignment) {
            if (!is_array($assignment)) {
                continue;
            }

            $fieldId = (int) ($assignment['fieldId'] ?? 0);
            $pageId = sanitize_text_field((string) ($assignment['pageId'] ?? ''));
            if ($fieldId <= 0 || $pageId === '' || !in_array($pageId, $validPageIds, true)) {
                continue;
            }

            $normalized[$fieldId] = $pageId;
        }

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $pages
     * @return array<string, mixed>|null
     */
    private static function findPageByLabel(array $pages, string $label): ?array
    {
        foreach ($pages as $page) {
            if (!is_array($page)) {
                continue;
            }
            if (($page['label'] ?? '') === $label) {
                return $page;
            }
        }

        return null;
    }
}
