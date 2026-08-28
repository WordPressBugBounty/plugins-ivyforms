<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\Repository\ColumnRepositoryTrait;

/**
 * Class AbstractRepository
 *
 * @package IvyForms\Repository
 */
class AbstractRepository
{
    use ColumnRepositoryTrait;

    public const FACTORY = '';

    /** @var string */
    protected string $table;

    /** @var mixed */
    protected $wpdb;

    public function __construct(string $table)
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $table;
    }

    /**
     * @param int $id
     * @return object|null
     *
     */
    public function getById(int $id): ?object
    {
        $row =  $this->wpdb->get_row(
            $this->wpdb->prepare(
                $this->selectQuery() . " WHERE $this->table.id = %d",
                $id
            ),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        return call_user_func([static::FACTORY, 'create'], $row);
    }

    /**
     * @return mixed[]
     * @throws InvalidArgumentException
     */
    public function getAll(): array
    {
        $rows = $this->wpdb->get_results(
            $this->selectQuery(),
            ARRAY_A
        );

        $result = [];

        foreach ($rows as $row) {
            $result[] = call_user_func([static::FACTORY, 'create'], $row);
        }

        return $result;
    }

    /**
     * @param int $id
     * @return int
     * @throws QueryExecutionException
     */
    public function delete(int $id): int
    {
        $result = $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM $this->table WHERE id = %d",
                $id
            )
        );

        if ($result === false) {
            throw new QueryExecutionException(
                sprintf(BackendStrings::getExceptionStrings()['failed_delete_by_id'], $id, $this->table)
            );
        }

        return $result;
    }

    /**
     * Delete multiple entities by their IDs in bulk.
     *
     * @param array<int> $ids
     * @return int Number of deleted entities
     * @throws QueryExecutionException
     */
    public function deleteMany(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = "DELETE FROM $this->table WHERE id IN ($placeholders)";
        $result = $this->wpdb->query($this->wpdb->prepare($query, ...$ids));

        if ($result === false) {
            throw new QueryExecutionException(
                sprintf(
                    BackendStrings::getExceptionStrings()['failed_delete_by_ids'],
                    implode(', ', $ids),
                    $this->table
                )
            );
        }

        return $result;
    }

    /**
     * Delete multiple entities by foreign key values (e.g., form IDs).
     *
     * @param array<int> $foreignIds Array of foreign key values (e.g., form IDs)
     * @param string $foreignColumn Foreign key column name (default: 'formId')
     * @return int Number of deleted entities
     * @throws InvalidArgumentException If no foreign IDs are provided
     * @throws QueryExecutionException
     */
    public function deleteManyByForeignKeyValues(array $foreignIds, string $foreignColumn = 'formId'): int
    {
        if (empty($foreignIds)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['no_form_ids_provided']
            );
        }

        // Validate foreign column to prevent SQL injection
        if (!in_array($foreignColumn, $this->getAllowedForeignColumns(), true)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_foreign_column']
            );
        }

        $foreignIds = array_map('intval', $foreignIds);
        $placeholders = implode(',', array_fill(0, count($foreignIds), '%d'));
        $query = "DELETE FROM {$this->table} WHERE {$foreignColumn} IN ($placeholders)";
        $result = $this->wpdb->query($this->wpdb->prepare($query, ...$foreignIds));

        if ($result === false) {
            throw new QueryExecutionException(
                sprintf(
                    BackendStrings::getExceptionStrings()['failed_delete_by_foreign_keys'],
                    implode(', ', $foreignIds),
                    $this->table
                )
            );
        }

        return $result;
    }

    /**
     * Delete entities by a single foreign key value (e.g., form ID).
     *
     * @param int $foreignId Foreign key value (e.g., form ID)
     * @param string $foreignColumn Foreign key column name (default: 'formId')
     * @return int Number of deleted entities
     * @throws InvalidArgumentException If invalid foreign column is provided
     * @throws QueryExecutionException
     */
    public function deleteOneByForeignKeyValue(int $foreignId, string $foreignColumn = 'formId'): int
    {
        // Validate foreign column to prevent SQL injection
        if (!in_array($foreignColumn, $this->getAllowedForeignColumns(), true)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_foreign_column']
            );
        }

        $query = "DELETE FROM {$this->table} WHERE {$foreignColumn} = %d";
        $result = $this->wpdb->query($this->wpdb->prepare($query, $foreignId));

        if ($result === false) {
            throw new QueryExecutionException(
                sprintf(BackendStrings::getExceptionStrings()['failed_delete_by_foreign_key'], $foreignId, $this->table)
            );
        }

        return $result;
    }

    /**
     * @param array<string, mixed>|null $params
     * @return array<string, mixed>
     */
    public function search(?array $params): array
    {
        $queryParams = [];
        $whereClauses = $this->buildWhereClauses($params, $queryParams);

        $query = $this->buildSearchQuery($whereClauses, $params, $queryParams);
        $results = $this->wpdb->get_results($this->wpdb->prepare($query, $queryParams));

        $total = $this->getTotalCount($whereClauses, $queryParams);

        return [
            'data' => $results,
            'meta' => [
                'page' => $params['page'],
                'perPage' => $params['perPage'],
                'total' => $total,
            ],
        ];
    }

    /**
     * @param array<string, mixed>|null $params
     * @param array<int|string, mixed> $queryParams
     * @return string
     */
    protected function buildWhereClauses(?array $params, array &$queryParams): string
    {
        $whereClauses = ['1=1'];

        $this->addSearchClauses($params, $whereClauses, $queryParams);
        $this->addFilterClauses($params, $whereClauses, $queryParams);
        $this->addDateRangeClauses($params['dateRange'] ?? [], $whereClauses, $queryParams);

        return implode(' AND ', $whereClauses);
    }

    /**
     * @param array<string, mixed>|null $params
     * @param array<int, string> $whereClauses
     * @param array<int|string, mixed> $queryParams
     */
    protected function addSearchClauses(?array $params, array &$whereClauses, array &$queryParams): void
    {
        if (!empty($params['search'])) {
            $searchEscaped = '%' . $this->wpdb->esc_like($params['search']) . '%';
            $searchableColumns = $this->getSearchableColumns();
            $searchConditions = array_map(fn($col) => "$col LIKE %s", $searchableColumns);
            $whereClauses[] = '(' . implode(' OR ', $searchConditions) . ')';
            foreach ($searchableColumns as $col) {
                $queryParams[] = $searchEscaped;
            }
        }
    }

    /**
     * @param array<string, mixed>|null $params
     * @param array<int, string> $whereClauses
     * @param array<int|string, mixed> $queryParams
     */
    protected function addFilterClauses(?array $params, array &$whereClauses, array &$queryParams): void
    {
        if (!empty($params['filters'])) {
            $filterableColumns = $this->getFilterableColumns();
            foreach ($params['filters'] as $key => $value) {
                if (in_array($key, $filterableColumns, true) && $value !== null && $value !== '') {
                    $whereClauses[] = "{$key} = %s";
                    $queryParams[] = sanitize_text_field($value);
                }
            }
        }
    }

    /**
     * @param array<int, string> $dateRange
     * @param array<int, string> $whereClauses
     * @param array<int|string, mixed> $queryParams
     */
    protected function addDateRangeClauses(array $dateRange, array &$whereClauses, array &$queryParams): void
    {

        if (empty($dateRange) || empty($dateRange[0])) {
            return;
        }

        $dateColumn = $this->getDateColumn();

        if (empty($dateRange[1])) {
            $dateRange[1] = $dateRange[0];
        }

        $normalizeStartOfDay = function ($isoDate) {
            $day = substr($isoDate, 0, 10);
            return $day . ' 00:00:00';
        };
        $normalizeEndOfDay = function ($isoDate) {
            $day = substr($isoDate, 0, 10);
            return $day . ' 23:59:59';
        };

        $start = $normalizeStartOfDay($dateRange[0]);
        $end   = $normalizeEndOfDay($dateRange[1]);

        $whereClauses[] = "$dateColumn >= %s";
        $queryParams[] = $start;

        $whereClauses[] = "$dateColumn <= %s";
        $queryParams[] = $end;
    }

    /**
     * @param string $whereClauses
     * @param array<string, mixed>|null $params
     * @param array<int|string, mixed> $queryParams
     * @return string
     */
    protected function buildSearchQuery(string $whereClauses, ?array $params, array &$queryParams): string
    {
        $sortableColumns = $this->getSortableColumns();
        $sortBy = in_array($params['orderBy'] ?? 'id', $sortableColumns, true) ? $params['orderBy'] : 'id';
        $order = strtolower($params['order'] ?? 'asc') === 'asc' ? 'ASC' : 'DESC';
        $page = max(($params['page'] ?? 1), 1);
        $perPage = max(($params['perPage'] ?? 10), 1);
        $offset = ($page - 1) * $perPage;

        $queryParams[] = $perPage;
        $queryParams[] = $offset;

        return $this->selectQuery() . " WHERE {$whereClauses} ORDER BY {$sortBy} {$order} LIMIT %d OFFSET %d";
    }

    /**
     * @param string $whereClauses
     * @param array<int|string, mixed> $queryParams
     * @return int
     */
    protected function getTotalCount(string $whereClauses, array $queryParams): int
    {
        // Exclude only LIMIT and OFFSET, keeping filter parameters intact
        $countParams = $queryParams;
        if (isset($queryParams[count($queryParams) - 2]) && isset($queryParams[count($queryParams) - 1])) {
            $countParams = array_slice($queryParams, 0, -2);
        }

        $countQuery = "SELECT COUNT(*) FROM $this->table WHERE {$whereClauses}";
        return (int)$this->wpdb->get_var($this->wpdb->prepare($countQuery, $countParams));
    }

    public function selectQuery(): string
    {
        return "SELECT * FROM " . $this->table;
    }

    /**
     * Start a database transaction.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return (bool) $this->wpdb->query('START TRANSACTION');
    }

    /**
     * Commit a database transaction.
     *
     * @return bool
     */
    public function commit(): bool
    {
        return (bool) $this->wpdb->query('COMMIT');
    }

    /**
     * Rollback a database transaction.
     *
     * @return bool
     */
    public function rollback(): bool
    {
        return (bool) $this->wpdb->query('ROLLBACK');
    }
}
