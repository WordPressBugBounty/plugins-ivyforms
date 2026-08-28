<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository\Entry;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Helpers\EntryBatchUpdater;
use IvyForms\Common\Helpers\EntryQueryHelper;
use IvyForms\Entity\Entry\Entry;
use IvyForms\Factory\Entry\EntryFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Repository\VisibleFormIdsQueryFilter;
use IvyForms\Services\InstallActions\DB\Entry\EntriesTable;
use IvyForms\Services\InstallActions\DB\EntryField\EntryFieldsTable as EntryFieldsTable;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;
use IvyForms\Services\Translations\BackendStrings;

class EntryRepository extends AbstractRepository implements EntryRepositoryInterface
{
    public const FACTORY = EntryFactory::class;

    // Column constants
    public const COLUMN_STATUS = 'status';
    public const COLUMN_STARRED = 'starred';

    /**
     * Add entry to the database
     *
     * @param Entry $entity
     *
     * @return int
     *
     * @throws QueryExecutionException
     */
    public function add($entity): int
    {
        $data = $entity->toArray(true);

        $dateCreated = !empty($data['dateCreated']) ? (string)$data['dateCreated'] : current_time('mysql');
        $dateEdited = !empty($data['dateEdited']) ? (string)$data['dateEdited'] : $dateCreated;

        $result = $this->wpdb->insert(
            $this->table,
            [
                'formId'        => $data['formId'],
                'userId'        => $data['userId'],
                'dateCreated'   => $dateCreated,
                'dateEdited'    => $dateEdited,
                'status'        => $data['status'],
                'ipAddress'     => $data['ipAddress'],
                'userAgent'     => $data['userAgent'],
                'sourceURL'     => $data['sourceURL'],
                'starred'       => $data['starred'] ? 1 : 0,
            ],
            ['%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Update entry in the database
     *
     * @param int  $id
     * @param Entry $entity
     *
     * @return bool
     *
     * @throws QueryExecutionException
     */
    public function update(int $id, $entity): bool
    {
        $result = $this->wpdb->update(
            $this->table,
            [
                'dateEdited'  => current_time('mysql'),
            ],
            ['id' => $id],
            ['%s'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_update_data'] . __CLASS__
            );
        }

        return (bool)$result;
    }

    /**
     * Update the starred status of an entry.
     *
     * @param int $entryId
     * @param bool $value
     *
     * @return void
     * @throws QueryExecutionException
     */
    public function updateEntryStarred(int $entryId, bool $value): void
    {
        $result = $this->wpdb->update(
            $this->table,
            [
                'starred' => $value ? 1 : 0,
                'dateEdited' => current_time('mysql'),
            ],
            ['id' => $entryId],
            ['%d', '%s'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_update_starred'] . __CLASS__
            );
        }
    }

    /**
     * Update the status of an entry.
     *
     * @param int $entryId
     * @param string $status
     *
     * @return void
     * @throws QueryExecutionException
     */
    public function updateEntryStatus(int $entryId, string $status): void
    {
        $result = $this->wpdb->update(
            $this->table,
            [
                'status' => $status,
                'dateEdited' => current_time('mysql'),
            ],
            ['id' => $entryId],
            ['%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_update_status'] . __CLASS__
            );
        }
    }

    /**
     * Search for entries with extended logic (field value, form name) and return paginated results with meta.
     *
     * @param array<string, mixed>|null $params
     * @return array<mixed>
     * @throws InvalidArgumentException|QueryExecutionException
     */
    public function search(?array $params): array
    {
        $queryParams = [];
        $whereClauses = $this->buildWhereClauses($params, $queryParams);

        // Always join entry fields and forms for search/filter/sort
        $entriesTable = EntriesTable::getTableName();
        $join = EntryQueryHelper::getEntryJoinClause();
        $formsTable = FormsTable::getTableName();

        // Sorting
        $sortableColumns = $this->getSortableColumns();
        $sortBy = $params['orderBy'] ?? 'id';
        $order = strtolower($params['order'] ?? 'asc') === 'asc' ? 'ASC' : 'DESC';
        // Map 'formName' to 'f.name' for sorting
        $sortColumn = 'e.id';
        if ($sortBy === 'formName') {
            $sortColumn = 'f.name';
        } elseif (in_array($sortBy, $sortableColumns, true)) {
            $sortColumn = 'e.' . $sortBy;
        }

        // Pagination
        $page = max(($params['page'] ?? 1), 1);
        $perPage = $params['perPage'] ?? 10;
        $queryBuild = EntryQueryHelper::buildEntrySelectQuery(
            $entriesTable,
            $join,
            $whereClauses,
            $sortColumn,
            $order,
            $queryParams,
            $perPage,
            $page
        );
        $sql = $queryBuild['sql'];
        $mainParams = $queryBuild['params'];
        $results = $this->wpdb->get_results($this->wpdb->prepare($sql, $mainParams), ARRAY_A);
        if ($results === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_search_entries'] . __CLASS__
            );
        }

        // Total count for pagination meta (exclude LIMIT/OFFSET)
        $countSql = "SELECT COUNT(DISTINCT e.id) FROM {$entriesTable} e $join WHERE {$whereClauses}";
        $total = (int)$this->wpdb->get_var($this->wpdb->prepare($countSql, $queryParams));

        // Fetch form names for all unique formIds in the results
        $formIdToName = [];
        $formNamesQuery = EntryQueryHelper::getFormNamesByIdsQuery($results, $formsTable);
        if (!empty($formNamesQuery['sql'])) {
            $formRows = $this->wpdb->get_results(
                $this->wpdb->prepare($formNamesQuery['sql'], $formNamesQuery['params']),
                ARRAY_A
            );
            if ($formRows === null) {
                throw new QueryExecutionException(
                    BackendStrings::getExceptionStrings()['unable_get_form_names'] . __CLASS__
                );
            }
            foreach ($formRows as $row) {
                $formIdToName[$row['id']] = $row['name'];
            }
        }

        return [
            'data' => $results,
            'meta' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
            ],
            'formIdToName' => $formIdToName,
        ];
    }

    /**
     * Override addSearchClauses to use EntryQueryHelper for search WHERE clause construction.
     */
    protected function addSearchClauses(?array $params, array &$whereClauses, array &$queryParams): void
    {
        $searchResult = EntryQueryHelper::buildSearchWhereClause($params);
        if ($searchResult['where']) {
            $whereClauses[] = $searchResult['where'];
            $queryParams = array_merge($queryParams, $searchResult['params']);
        }
    }

    /**
     * Override addFilterClauses to use EntryQueryHelper for filter WHERE clause construction.
     * @param array<string, mixed>|null $params
     */
    protected function addFilterClauses(?array $params, array &$whereClauses, array &$queryParams): void
    {
        $filterableColumns = $this->getFilterableColumns();
        $filterResult = EntryQueryHelper::buildFilterWhereClause($params, $filterableColumns);
        if (!empty($filterResult['where'])) {
            $whereClauses = array_merge($whereClauses, $filterResult['where']);
            // Ensure all values are strings
            $queryParams = array_merge($queryParams, array_map('strval', $filterResult['params']));
        }

        VisibleFormIdsQueryFilter::apply($params, $whereClauses, $queryParams, 'e.formId');
    }

    /**
     * Get the columns that can be searched in the database (including virtual columns for search).
     *
     * @return array<string>
     */
    protected function getSearchableColumns(): array
    {
        return [
            'id',
            'formId',
            'fieldValue',   // virtual, from EntryFields table
            'name',         // virtual, from Forms table
        ];
    }

    /**
     * Get filterable columns
     *
     * @return array<string>
     */
    protected function getFilterableColumns(): array
    {
        return ['starred', 'status', 'formId', 'userId'];
    }

    /**
     * Get sortable columns
     *
     * @return array<string>
     */
    protected function getSortableColumns(): array
    {
        return ['id', 'dateCreated', 'formName'];
    }

    /**
     * Get the date column
     *
     * @return string
     */
    protected function getDateColumn(): string
    {
        return 'e.dateCreated';
    }

    /**
     * @param int $entryId
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function exists(int $entryId): bool
    {
        $query = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE id = %d",
            $entryId
        );

        $count = $this->wpdb->get_var($query);
        if ($count === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        return (int)$count > 0;
    }

    /**
     * Get the count of entries for a specific form.
     *
     * @param int $formId
     * @return int
     * @throws QueryExecutionException
     */
    public function getCountByFormId(int $formId): int
    {
        $queryData = EntryQueryHelper::getCountByFormIdQuery($this->table, $formId);
        $query = $this->wpdb->prepare($queryData['sql'], $queryData['params']);
        $count = $this->wpdb->get_var($query);
        if ($count === null) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        return (int)$count;
    }

    /**
     * Get the count of entries for the filter dropdown.
     *
     * @param array<string, mixed>|null $params
     * @return array<string, int>
     * @throws QueryExecutionException
     */
    public function getFilterCount(?array $params = null): array
    {
        $queryData = EntryQueryHelper::getFilterCountQuery($this->table, $params);
        $result = null;
        if (!empty($queryData['params'])) {
            $result = $this->wpdb->get_row(
                $this->wpdb->prepare($queryData['sql'], ...$queryData['params']),
                ARRAY_A
            );
        }
        if ($result === null) {
            $result = $this->wpdb->get_row($queryData['sql'], ARRAY_A);
        }
        if ($result === null) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_search_entries'] . __CLASS__
            );
        }
        return array_map('intval', $result ?: [
            'readTrueCount' => 0,
            'readFalseCount' => 0,
            'starredTrueCount' => 0,
            'starredFalseCount' => 0,
        ]);
    }

    /**
     * Get entry fields for the given entries.
     *
     * @param array<int, array<string, mixed>> $entries
     * @return array<mixed>
     *
     * @throws QueryExecutionException|InvalidArgumentException
     */
    public function getEntryFields(array $entries): array
    {
        $queryData = EntryQueryHelper::getEntryFieldsQuery($entries);
        if (empty($queryData['sql'])) {
            return [];
        }
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare($queryData['sql'], $queryData['params']),
            ARRAY_A
        );
        if ($results === null) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_get_entry_fields'] . __CLASS__
            );
        }
        return $results;
    }

    /**
     * Get the count of entries for multiple form IDs.
     *
     * @param int[] $formIds
     * @return array<int, int> formId => count
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getEntryCountByFormIds(array $formIds): array
    {
        $queryData = EntryQueryHelper::getEntryCountByFormIdsQuery($formIds);
        if (empty($queryData['sql'])) {
            return [];
        }
        $results = $this->wpdb->get_results(
            $this->wpdb->prepare($queryData['sql'], $queryData['params']),
            ARRAY_A
        );
        if ($results === null) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_search_entries'] . __CLASS__
            );
        }
        $counts = [];
        foreach ($results as $row) {
            $counts[(int)$row['formId']] = (int)$row['count'];
        }
        foreach ($queryData['formIds'] as $id) {
            if (!isset($counts[$id])) {
                $counts[$id] = 0;
            }
        }
        return $counts;
    }

    /**
     * Resolve owning form IDs for the given entry IDs in a single query.
     *
     * @param int[] $entryIds
     * @return array<int, int> entryId => formId
     * @throws QueryExecutionException
     */
    public function getFormIdsByEntryIds(array $entryIds): array
    {
        return EntryQueryHelper::fetchFormIdsByEntryIds($this->wpdb, $entryIds);
    }

    /**
     * Get allowed columns for bulk update operations
     *
     * @return string[]
     */
    protected function getAllowedBulkUpdateColumns(): array
    {
        return [
            self::COLUMN_STARRED,
            self::COLUMN_STATUS,
        ];
    }
}
