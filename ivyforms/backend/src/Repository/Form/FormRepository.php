<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository\Form;

use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Helpers\FormPageHelper;
use IvyForms\Common\Helpers\FormPersistenceHelper;
use IvyForms\Entity\Form\Form;
use IvyForms\Factory\Form\FormFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Repository\VisibleFormIdsQueryFilter;
use IvyForms\Services\Translations\BackendStrings;

class FormRepository extends AbstractRepository implements FormRepositoryInterface
{
    public const FACTORY = FormFactory::class;

    // Column constants
    public const COLUMN_STARRED = 'starred';

    public const COLUMN_DATE_CREATED = 'dateCreated';


    /**
     * Add form to the database
     *
     * @param Form $entity
     *
     * @return int
     *
     * @throws QueryExecutionException
     */
    public function add($entity): int
    {
        $data = $entity->toArray();

        if (!empty($data['fields']) && !empty($data['pages'])) {
            $fieldsByPage = FormPageHelper::groupFieldsByPageId($data['fields']);
            $data['pages'] = FormPageHelper::assignFieldsToPages($data['pages'], $fieldsByPage);
        }

        $result = $this->wpdb->insert(
            $this->table,
            FormPersistenceHelper::buildInsertRow($data),
            ['%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Update form in the database
     *
     * @param int  $id
     * @param Form $entity
     *
     * @return bool
     *
     * @throws QueryExecutionException
     */
    public function update(int $id, $entity): bool
    {
        $data = $entity->toArray();

        if (!empty($data['fields']) && !empty($data['pages'])) {
            $fieldsByPage = FormPageHelper::groupFieldsByPageId($data['fields']);
            $data['pages'] = FormPageHelper::assignFieldsToPages($data['pages'], $fieldsByPage);
        }

        $result = $this->wpdb->update(
            $this->table,
            FormPersistenceHelper::buildUpdateRow($data),
            ['id' => $id],
            ['%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return $result;
    }

    /**
     * Update the form starred value by ID.
     *
     * @param int $id
     * @param string  $value
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function updateFormStarred(int $id, string $value): bool
    {
        // Update the starred column in the database
        $result = $this->wpdb->update(
            $this->table,
            ['starred' => $value],
            ['id' => $id],
            ['%d'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return $result;
    }

    /**
     * Update the published status of a form by ID.
     *
     * @param int $formId
     * @param string $value
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function updateFormStatus(int $formId, string $value): bool
    {
        // Update the published column in the database
        $result = $this->wpdb->update(
            $this->table,
            ['published' => $value],
            ['id' => $formId],
            ['%d'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return $result;
    }

    /**
     * Get the count of filters for the filter dropdown.
     *
     * @return array<string, int>
     * @throws QueryExecutionException
     */
    public function getFilterCount(): array
    {
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT 
            SUM(published = 1) as publishedTrueCount,
            SUM(published = 0) as publishedFalseCount,
            SUM(starred = 1) as starredTrueCount,
            SUM(starred = 0) as starredFalseCount
        FROM {$this->table}"
            ),
            ARRAY_A
        );
        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_search_entries'] . __CLASS__
            );
        }
        // Convert all values to integers and provide defaults if query returns null
        return array_map('intval', $result ?: [
            'publishedTrueCount' => 0,
            'publishedFalseCount' => 0,
            'starredTrueCount' => 0,
            'starredFalseCount' => 0,
        ]);
    }

    /**
     * Get the earliest form creation timestamp.
     *
     * @return string|null DATETIME as stored in the database
     * @throws QueryExecutionException
     */
    public function getEarliestCreatedAt(): ?string
    {
        $column = self::COLUMN_DATE_CREATED;
        $result = $this->wpdb->get_var(
            "SELECT MIN(`{$column}`) FROM {$this->table} WHERE `{$column}` IS NOT NULL"
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_search_entries'] . __CLASS__
            );
        }

        if ($result === null || $result === '') {
            return null;
        }

        return (string) $result;
    }

    /**
     * Check if a form exists by its ID.
     *
     * @param int $formId
     *
     * @return bool
     * @throws QueryExecutionException
     */
    public function exists(int $formId): bool
    {
        $query = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM " . $this->table . " WHERE id = %d",
            $formId
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
     * @param list<int> $formIds
     * @return list<int>
     * @throws QueryExecutionException
     */
    public function filterExistingIds(array $formIds): array
    {
        $formIds = array_values(array_unique(array_filter(
            array_map('intval', $formIds),
            static function (int $id): bool {
                return $id > 0;
            }
        )));

        if ($formIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($formIds), '%d'));
        $query        = $this->wpdb->prepare(
            "SELECT id FROM {$this->table} WHERE id IN ({$placeholders})",
            ...$formIds
        );
        $rows = $this->wpdb->get_col($query);
        if ($rows === null) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }

        return array_map('intval', $rows);
    }

    /**
     * @return list<int>
     * @throws QueryExecutionException
     */
    public function getAllIds(): array
    {
        $rows = $this->wpdb->get_col("SELECT id FROM {$this->table} ORDER BY id ASC");
        if ($rows === null) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }

        return array_map('intval', $rows);
    }

    /**
     * @return list<array{id:int, name:string}>
     * @throws QueryExecutionException
     */
    public function getIdNamePairs(): array
    {
        $rows = $this->wpdb->get_results(
            "SELECT id, name FROM {$this->table} ORDER BY name ASC",
            ARRAY_A
        );
        if ($rows === null) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }

        $pairs = [];
        foreach ($rows as $row) {
            if (!isset($row['id'], $row['name'])) {
                continue;
            }
            $pairs[] = [
                'id'   => (int) $row['id'],
                'name' => (string) $row['name'],
            ];
        }

        return $pairs;
    }

    /**
     * @param list<int> $formIds
     * @return array<int, string>
     * @throws QueryExecutionException
     */
    public function getNamesByIds(array $formIds): array
    {
        $formIds = array_values(array_unique(array_filter(
            array_map('intval', $formIds),
            static function (int $id): bool {
                return $id > 0;
            }
        )));

        if ($formIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($formIds), '%d'));
        $query        = $this->wpdb->prepare(
            "SELECT id, name FROM {$this->table} WHERE id IN ({$placeholders})",
            ...$formIds
        );
        $rows = $this->wpdb->get_results($query, ARRAY_A);
        if ($rows === null) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }

        $names = [];
        foreach ($rows as $row) {
            if (!isset($row['id'], $row['name'])) {
                continue;
            }
            $names[(int) $row['id']] = (string) $row['name'];
        }

        return $names;
    }

    /**
     * @param array<string, mixed>|null $params
     * @param array<int, string> $whereClauses
     * @param array<int|string, mixed> $queryParams
     */
    protected function addFilterClauses(?array $params, array &$whereClauses, array &$queryParams): void
    {
        parent::addFilterClauses($params, $whereClauses, $queryParams);

        VisibleFormIdsQueryFilter::apply($params, $whereClauses, $queryParams, 'id');
    }

    /**
     * Get searchable columns
     *
     * @return array<string>
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'author', 'description'];
    }

    /**
     * Get filterable columns
     *
     * @return array<string>
     */
    protected function getFilterableColumns(): array
    {
        return ['starred', 'published'];
    }

    /**
     * Get sortable columns
     *
     * @return array<string>
     */
    protected function getSortableColumns(): array
    {
        return ['id', 'name', 'author', 'dateCreated'];
    }

    /**
     * Get the date column
     *
     * @return string
     */
    protected function getDateColumn(): string
    {
        return self::COLUMN_DATE_CREATED;
    }

    /**
     * Get allowed columns for bulk update operations.
     *
     * @return string[]
     */
    protected function getAllowedBulkUpdateColumns(): array
    {
        return [
            self::COLUMN_STARRED,
        ];
    }
}
