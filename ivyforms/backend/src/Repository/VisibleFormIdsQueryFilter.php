<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Repository;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Applies visibleFormIds search params as an SQL IN clause.
 */
final class VisibleFormIdsQueryFilter
{
    /**
     * @param array<string, mixed>|null $params
     * @param array<int, string> $whereClauses
     * @param array<int|string, mixed> $queryParams
     */
    public static function apply(
        ?array $params,
        array &$whereClauses,
        array &$queryParams,
        string $columnExpression
    ): void {
        if (!isset($params['visibleFormIds']) || !is_array($params['visibleFormIds'])) {
            return;
        }

        $ids = array_values(array_unique(array_map('intval', $params['visibleFormIds'])));
        $ids = array_values(array_filter($ids, static function (int $id): bool {
            return $id > 0;
        }));

        if ($ids === []) {
            $whereClauses[] = '0=1';

            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $whereClauses[] = "{$columnExpression} IN ({$placeholders})";
        foreach ($ids as $id) {
            $queryParams[] = $id;
        }
    }
}
