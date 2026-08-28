<?php

namespace IvyForms\Services\Page;

/**
 * Normalizes pages and field pageId values to deterministic IDs.
 */
class PageIdNormalizer
{
    /**
     * @param array<int, mixed> $pages
     * @return array{pages: array<int, array<string, mixed>>, pageIdMap: array<string, string>, firstPageId: string}
     */
    public static function normalizePagesForForm(array $pages): array
    {
        $pageIdMap = [];
        /** @var array<int, array<string, mixed>> $validPages */
        $validPages = array_values(array_filter($pages, 'is_array'));

        if ($validPages === []) {
            $firstPageId = self::buildPageId(1);

            return [
                'pages' => [[
                    'id' => $firstPageId,
                    'label' => 'Page 1',
                    'closable' => false,
                    'fields' => [],
                ]],
                'pageIdMap' => $pageIdMap,
                'firstPageId' => $firstPageId,
            ];
        }

        $normalizedPages = [];
        foreach ($validPages as $index => $page) {
            $oldId = isset($page['id']) ? (string) $page['id'] : '';
            $newId = self::buildPageId($index + 1);
            if ($oldId !== '') {
                $pageIdMap[$oldId] = $newId;
            }

            $page['id'] = $newId;
            $normalizedPages[] = $page;
        }


        return [
            'pages' => $normalizedPages,
            'pageIdMap' => $pageIdMap,
            'firstPageId' => (string) $normalizedPages[0]['id'],
        ];
    }

    /**
     * Build a deterministic page ID for the given page index (1-based).
     */
    public static function buildPageId(int $pageNumber): string
    {
        return sprintf('page_%d', max(1, $pageNumber));
    }

    /**
     * Return true when $pageId already has the deterministic `page_N` format.
     */
    public static function isDeterministicPageIdForForm(string $pageId): bool
    {
        return preg_match('/^page_[1-9][0-9]*$/', $pageId) === 1;
    }
}
