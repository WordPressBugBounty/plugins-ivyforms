<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Ai;

use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Builds multipage structure and assigns field pageIds for AI-generated forms.
 */
class AiFormMultipageNormalizer
{
    /**
     * @param array<string, mixed> $raw
     *
     * @return array{pages: list<array<string, mixed>>, idMap: array<string, string>}|null
     */
    public function normalizePages(array $raw, string $formType): ?array
    {
        if ($formType === Sanitizer::FORM_TYPE_CONVERSATIONAL) {
            return null;
        }

        if (!AiAvailability::canUseMultipage()) {
            return null;
        }

        $pagesInput = $raw['pages'] ?? null;
        if (!is_array($pagesInput) || count($pagesInput) < 2) {
            return null;
        }

        $draftPages = $this->buildDraftPages($pagesInput);
        if (count($draftPages) < 2) {
            return null;
        }

        return $this->finalizePages($draftPages);
    }

    /**
     * @param list<array<string, mixed>> $fields
     * @param list<array<string, mixed>> $pages
     * @param array<string, string> $idMap AI page id aliases → preserved page id
     *
     * @return list<array<string, mixed>>
     */
    public function ensureFieldPageIds(array $fields, array $pages, array $idMap = []): array
    {
        $validIds = $this->collectPageIds($pages);
        if ($validIds === []) {
            return $fields;
        }

        $fields = $this->remapFieldPageIds($fields, $idMap);

        $defaultPageId = $validIds[0];
        $parentCount = $this->countParentFields($fields);
        $perPage = max(1, (int) ceil($parentCount / count($validIds)));

        $pageIdByFieldIndex = $this->assignParentPageIds(
            $fields,
            $validIds,
            $defaultPageId,
            $perPage
        );

        return $this->assignChildPageIds($fields, $pageIdByFieldIndex, $validIds, $defaultPageId);
    }

    /**
     * @param array<int, mixed> $pagesInput
     *
     * @return list<array<string, mixed>>
     */
    private function buildDraftPages(array $pagesInput): array
    {
        $pages = [];
        foreach (array_values($pagesInput) as $index => $pageInput) {
            if (!is_array($pageInput)) {
                continue;
            }

            $label = sanitize_text_field((string) ($pageInput['label'] ?? ''));
            if ($label === '') {
                $label = sprintf(
                    BackendStrings::getAiStrings()['ai_default_page_label'],
                    $index + 1
                );
            }

            $requestedId = sanitize_text_field((string) ($pageInput['id'] ?? ''));
            $pageId = $requestedId !== '' ? $requestedId : sprintf('page_%d', $index + 1);

            $pages[] = [
                'id' => $pageId,
                'label' => $label,
            ];
        }

        return $pages;
    }

    /**
     * Keep AI-emitted page ids so field pageIds stay aligned. Only rewrite on
     * collision / empty; Pro sanitize later remaps to page_N for persistence.
     *
     * @param list<array<string, mixed>> $draftPages
     *
     * @return array{pages: list<array<string, mixed>>, idMap: array<string, string>}
     */
    private function finalizePages(array $draftPages): array
    {
        $normalized = [];
        $idMap = [];
        $usedIds = [];

        foreach ($draftPages as $index => $page) {
            $oldId = sanitize_text_field((string) ($page['id'] ?? ''));
            $pageId = $this->allocateUniquePageId($oldId, $index, $usedIds);
            $usedIds[strtolower($pageId)] = true;

            // Keep $idMap empty on collision rewrites so fields on the first
            // valid page are not remapped onto the renamed duplicate.

            $normalized[] = [
                'id' => $pageId,
                'label' => $page['label'],
                'closable' => $index > 0,
                'fields' => [],
            ];
        }

        return [
            'pages' => $normalized,
            'idMap' => $idMap,
        ];
    }

    /**
     * @param array<string, true> $usedIds lowercase id → true
     */
    private function allocateUniquePageId(string $requestedId, int $index, array $usedIds): string
    {
        $candidate = $requestedId !== '' ? $requestedId : sprintf('page_%d', $index + 1);
        if (!isset($usedIds[strtolower($candidate)])) {
            return $candidate;
        }

        $fallback = sprintf('page_%d', $index + 1);
        $unique = isset($usedIds[strtolower($fallback)])
            ? sprintf('%s_%d', $fallback, $index + 1)
            : $fallback;

        return $unique;
    }

    /**
     * @param list<array<string, mixed>> $fields
     * @param array<string, string> $idMap
     *
     * @return list<array<string, mixed>>
     */
    private function remapFieldPageIds(array $fields, array $idMap): array
    {
        if ($idMap === []) {
            return $fields;
        }

        $lookup = [];
        foreach ($idMap as $from => $to) {
            $lookup[strtolower((string) $from)] = $to;
        }

        foreach ($fields as &$field) {
            $pageId = sanitize_text_field((string) ($field['pageId'] ?? ''));
            if ($pageId === '') {
                continue;
            }

            $mapped = $lookup[strtolower($pageId)] ?? null;
            if ($mapped === null) {
                continue;
            }

            $this->applyPageId($field, $mapped);
        }
        unset($field);

        return $fields;
    }

    /**
     * @param list<array<string, mixed>> $pages
     *
     * @return list<string>
     */
    private function collectPageIds(array $pages): array
    {
        $validIds = [];
        foreach ($pages as $page) {
            $id = sanitize_text_field((string) ($page['id'] ?? ''));
            if ($id !== '') {
                $validIds[] = $id;
            }
        }

        return $validIds;
    }

    /**
     * @param list<array<string, mixed>> $fields
     */
    private function countParentFields(array $fields): int
    {
        $parentCount = 0;
        foreach ($fields as $field) {
            if (($field['parentId'] ?? null) === null) {
                $parentCount++;
            }
        }

        return $parentCount;
    }

    /**
     * @param list<array<string, mixed>> $fields
     * @param list<string> $validIds
     *
     * @return array<int, string>
     */
    private function assignParentPageIds(
        array &$fields,
        array $validIds,
        string $defaultPageId,
        int $perPage
    ): array {
        $parentOrdinal = 0;
        $pageIdByFieldIndex = [];

        foreach ($fields as &$field) {
            if (($field['parentId'] ?? null) !== null) {
                continue;
            }

            $rawPageId = (string) ($field['pageId'] ?? '');
            $pageId = $rawPageId === ''
                ? $validIds[(int) min(
                    count($validIds) - 1,
                    floor($parentOrdinal / $perPage)
                )]
                : $this->resolveValidPageId($rawPageId, $validIds, $defaultPageId);

            $parentOrdinal++;
            $fieldIndex = (int) ($field['fieldIndex'] ?? 0);
            $pageIdByFieldIndex[$fieldIndex] = $pageId;
            $this->applyPageId($field, $pageId);
        }
        unset($field);

        return $pageIdByFieldIndex;
    }

    /**
     * @param list<array<string, mixed>> $fields
     * @param array<int, string> $pageIdByFieldIndex
     * @param list<string> $validIds
     *
     * @return list<array<string, mixed>>
     */
    private function assignChildPageIds(
        array $fields,
        array $pageIdByFieldIndex,
        array $validIds,
        string $defaultPageId
    ): array {
        foreach ($fields as &$field) {
            if (($field['parentId'] ?? null) === null) {
                continue;
            }

            $fieldIndex = (int) ($field['fieldIndex'] ?? 0);
            $pageId = $pageIdByFieldIndex[$fieldIndex]
                ?? $this->resolveValidPageId(
                    (string) ($field['pageId'] ?? ''),
                    $validIds,
                    $defaultPageId
                );
            $this->applyPageId($field, $pageId);
        }
        unset($field);

        return $fields;
    }

    /**
     * @param array<string, mixed> $field
     */
    private function applyPageId(array &$field, string $pageId): void
    {
        $field['pageId'] = $pageId;
        $settings = is_array($field['settings'] ?? null) ? $field['settings'] : [];
        $settings['pageId'] = $pageId;
        $field['settings'] = $settings;
    }

    /**
     * @param list<string> $validIds
     */
    private function resolveValidPageId(string $pageId, array $validIds, string $defaultPageId): string
    {
        $pageId = sanitize_text_field($pageId);
        if ($pageId === '') {
            return $defaultPageId;
        }

        $matched = $this->matchPageId($pageId, $validIds);
        if ($matched !== null) {
            return $matched;
        }

        // Last-resort ordinal guess when the model uses page_N casing variants.
        if (preg_match('/^page_(\d+)$/i', $pageId, $matches) === 1) {
            $idx = max(0, ((int) $matches[1]) - 1);

            return $validIds[min($idx, count($validIds) - 1)];
        }

        return $defaultPageId;
    }

    /**
     * @param list<string> $validIds
     */
    private function matchPageId(string $pageId, array $validIds): ?string
    {
        foreach ($validIds as $validId) {
            if (strcasecmp($pageId, $validId) === 0) {
                return $validId;
            }
        }

        return null;
    }
}
