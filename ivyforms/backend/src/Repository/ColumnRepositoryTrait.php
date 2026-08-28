<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Trait ColumnRepositoryTrait
 *
 * Handles all column-related operations and bulk column actions for repositories.
 * Use this trait in repositories that extend AbstractRepository.
 *
 * Column configuration methods (getSearchableColumns, getFilterableColumns, etc.)
 * should be overridden in child classes.
 *
 * @package IvyForms\Repository
 */
trait ColumnRepositoryTrait
{
    /**
     * Bulk update a column for multiple entities by their IDs.
     *
     * @param string $column Column name to update
     * @param mixed $value Value to set
     * @param array<int> $ids Array of entity IDs
     * @return int Number of updated entities
     * @throws InvalidArgumentException If column is not allowed or IDs are empty
     * @throws QueryExecutionException
     */
    public function bulkUpdateColumn(string $column, $value, array $ids): int
    {
        $ids = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = "UPDATE {$this->table} SET {$column} = %s, dateEdited = %s WHERE id IN ($placeholders)";
        $params = array_merge([$value, current_time('mysql')], $ids);
        $result = $this->wpdb->query($this->wpdb->prepare($query, ...$params));

        return $result;
    }


    /**
     * Get allowed columns for bulk update operations.
     * Override this method in child repositories to specify allowed columns.
     *
     * @return string[]
     */
    protected function getAllowedBulkUpdateColumns(): array
    {
        return [];
    }

    /**
     * Get searchable columns for the repository.
     * Override this method in child repositories to specify which columns are searchable.
     *
     * Default: only 'id' column
     *
     * @return string[]
     */
    protected function getSearchableColumns(): array
    {
        return ['id'];
    }

    /**
     * Get filterable columns for the repository.
     * Override this method in child repositories to specify which columns are filterable.
     *
     * Default: no columns
     *
     * @return array<string, string>
     */
    protected function getFilterableColumns(): array
    {
        return [];
    }

    /**
     * Get sortable columns for the repository.
     * Override this method in child repositories to specify which columns are sortable.
     *
     * Default: only 'id' column
     *
     * @return string[]
     */
    protected function getSortableColumns(): array
    {
        return ['id'];
    }

    /**
     * Get the date column name for the repository.
     * Override this method in child repositories to specify the date column.
     *
     * Default: 'dateCreated'
     *
     * @return string
     */
    protected function getDateColumn(): string
    {
        return 'dateCreated';
    }

    /**
     * Get allowed foreign key columns for deletion operations.
     * Override this method in child repositories to specify allowed columns.
     *
     * Default: formId, entryId, fieldId
     *
     * @return string[]
     */
    protected function getAllowedForeignColumns(): array
    {
        return ['formId', 'entryId', 'fieldId'];
    }
}
