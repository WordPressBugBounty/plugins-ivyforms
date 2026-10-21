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
 * Applies an inclusive calendar-day range as SQL bounds.
 *
 * List queries and filter counts both go through this so the day bounds stay identical.
 */
final class DateRangeQueryFilter
{
    /**
     * @param array<int|string, mixed> $dateRange
     * @param array<int, string> $whereClauses
     * @param array<int|string, mixed> $queryParams
     */
    public static function apply(
        array $dateRange,
        string $columnExpression,
        array &$whereClauses,
        array &$queryParams
    ): void {
        if (empty($dateRange[0]) || !is_scalar($dateRange[0])) {
            return;
        }

        $endSource = (!empty($dateRange[1]) && is_scalar($dateRange[1]))
            ? $dateRange[1]
            : $dateRange[0];

        $whereClauses[] = "{$columnExpression} >= %s";
        $queryParams[] = substr((string) $dateRange[0], 0, 10) . ' 00:00:00';

        $whereClauses[] = "{$columnExpression} <= %s";
        $queryParams[] = substr((string) $endSource, 0, 10) . ' 23:59:59';
    }
}
