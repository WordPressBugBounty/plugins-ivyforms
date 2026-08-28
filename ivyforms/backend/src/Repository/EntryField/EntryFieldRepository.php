<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository\EntryField;

use Exception;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Helpers\EntryQueryHelper;
use IvyForms\Factory\EntryField\EntryFieldFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Services\InstallActions\DB\Entry\EntriesTable;
use IvyForms\Services\InstallActions\DB\EntryField\EntryFieldsTable as EntryFieldsTable;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\Translations\BackendStrings;

class EntryFieldRepository extends AbstractRepository implements EntryFieldRepositoryInterface
{
    public const FACTORY = EntryFieldFactory::class;

    private const DUPLICATE_NORMALIZED_CHECK_BATCH_SIZE = 500;

    private const BATCH_INSERT_CHUNK_SIZE = 50;

    /**
     * Check if an entry field exists in the database.
     *
     * @param int $entryFieldId
     * @return bool
     * @throws QueryExecutionException
     */
    public function exists(int $entryFieldId): bool
    {
        $query = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE id = %d",
            $entryFieldId
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
     * @throws Exception
     */
    public function add(object $entity): int
    {
        $data = $entity->toArray();

        $result = $this->wpdb->insert(
            $this->table,
            [
                'id'        => $data['id'] ?? null,
                'entryId'  => $data['entryId'],
                'fieldId'   => $data['fieldId'],
                'fieldValue' => $data['fieldValue'],
            ],
            [
                '%d',
                '%d',
                '%d',
                '%s',
            ],
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        return  $this->wpdb->insert_id;
    }

    /**
     * @throws Exception
     */
    public function update(int $id, object $entity): bool
    {
        $data = $entity->toArray();

        $result = $this->wpdb->update(
            $this->table,
            [
                'fieldId' => $data['fieldId'],
                'fieldValue' => $data['fieldValue'],
            ],
            ['id' => $id],
            [
                '%d',
                '%s',
            ],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_update_data'] . __CLASS__
            );
        }

        return (bool) $result;
    }

    /**
     * Find an entry field by its ID.
     *
     * @param array<string, string> $array
     * @return array<int, \IvyForms\Entity\EntryField\EntryField>
     * @throws QueryExecutionException
     */
    public function findBy(array $array): array
    {
        $sql = $this->selectQuery() . " {$this->table}";
        $where = [];
        $values = [];

        foreach ($array as $field => $value) {
            $where[] = "{$field} = %s";
            $values[] = $value;
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $query = $this->wpdb->prepare($sql, ...$values);
        $rows = $this->wpdb->get_results($query, ARRAY_A);
        if ($rows === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        $results = [];
        foreach ($rows as $row) {
            $results[] = call_user_func([static::FACTORY, 'create'], $row);
        }

        return $results;
    }

    /**
     * Get all entry field IDs for the given entry IDs in a single query.
     *
     * @param int[] $entryIds
     * @return int[]
     * @throws QueryExecutionException
     */
    public function findIdsByEntryIds(array $entryIds): array
    {
        if (empty($entryIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($entryIds), '%d'));
        $query = "SELECT id FROM {$this->table} WHERE entryId IN ($placeholders)";
        $results = $this->wpdb->get_results($this->wpdb->prepare($query, ...$entryIds), ARRAY_A);
        if ($results === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        return array_map(fn($row) => (int)$row['id'], $results);
    }

    /**
     * Delete entry fields by form IDs using a subquery for fieldId.
     *
     * @param int[] $formIds
     * @return int Number of deleted entry_fields
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function deleteByFormIds(array $formIds): int
    {
        if (empty($formIds)) {
            return 0;
        }
        $formIds = array_map('intval', $formIds);
        $placeholders = implode(',', array_fill(0, count($formIds), '%d'));
        $fieldsTable = FieldsTable::getTableName();
        $query = "DELETE FROM {$this->table} WHERE fieldId IN 
                        (SELECT id FROM {$fieldsTable} WHERE formId IN ($placeholders))";
        $result = $this->wpdb->query($this->wpdb->prepare($query, ...$formIds));
        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['failed_delete_by_foreign_keys']
            );
        }
        return $result;
    }

    /**
     * Check if a field value already exists in the database
     *
     * @param int $formId
     * @param int $fieldId
     * @param string $value
     * @return bool
     * @throws QueryExecutionException
     */
    public function checkDuplicateValue(
        int $formId,
        int $fieldId,
        string $value,
        ?int $excludeEntryId = null
    ): bool {
        global $wpdb;

        $entriesTable = EntriesTable::getTableName();

        $sql = "SELECT 1 
            FROM {$entriesTable} e
            INNER JOIN {$this->table} ef ON e.id = ef.entryId
            WHERE e.formId = %d
              AND ef.fieldId = %d
              AND ef.fieldValue = %s";

        $params = [$formId, $fieldId, $value];

        if ($excludeEntryId !== null && $excludeEntryId > 0) {
            $sql .= ' AND e.id != %d';
            $params[] = $excludeEntryId;
        }

        $sql .= ' LIMIT 1';

        $exists = $wpdb->get_var($wpdb->prepare($sql, $params));

        if ($exists === false) {
            throw new QueryExecutionException(
                sprintf(
                    BackendStrings::getExceptionStrings()['failed_check_duplicates'],
                    $wpdb->last_error ?: BackendStrings::getExceptionStrings()['unknown_error']
                )
            );
        }

        return !empty($exists);
    }

    /**
     * Check if a normalized value matches any stored value after applying a normalizer.
     *
     * @param int $formId
     * @param int $fieldId
     * @param string $normalizedValue
     * @param callable(string): string $normalizeStoredValue
     * @return bool
     * @throws QueryExecutionException
     */
    public function checkDuplicateValueNormalized(
        int $formId,
        int $fieldId,
        string $normalizedValue,
        callable $normalizeStoredValue,
        ?int $excludeEntryId = null
    ): bool {
        global $wpdb;

        $query = $this->buildNormalizedDuplicateCheckQuery(
            EntriesTable::getTableName(),
            $formId,
            $fieldId,
            $excludeEntryId
        );
        $offset = 0;
        $limit = self::DUPLICATE_NORMALIZED_CHECK_BATCH_SIZE;

        while (true) {
            $rows = $wpdb->get_col($wpdb->prepare($query['sql'], ...array_merge($query['params'], [$limit, $offset])));

            if ($rows === false) {
                throw new QueryExecutionException(
                    sprintf(
                        BackendStrings::getExceptionStrings()['failed_check_duplicates'],
                        $wpdb->last_error ?: BackendStrings::getExceptionStrings()['unknown_error']
                    )
                );
            }

            if ($rows === []) {
                break;
            }

            if ($this->normalizedValueExistsInRows($rows, $normalizedValue, $normalizeStoredValue)) {
                return true;
            }

            if (count($rows) < $limit) {
                break;
            }

            $offset += $limit;
        }

        return false;
    }

    /**
     * @return array{sql: string, params: array<int, int>}
     */
    private function buildNormalizedDuplicateCheckQuery(
        string $entriesTable,
        int $formId,
        int $fieldId,
        ?int $excludeEntryId
    ): array {
        $sql = "SELECT ef.fieldValue
            FROM {$entriesTable} e
            INNER JOIN {$this->table} ef ON e.id = ef.entryId
            WHERE e.formId = %d
              AND ef.fieldId = %d";

        $params = [$formId, $fieldId];

        if ($excludeEntryId !== null && $excludeEntryId > 0) {
            $sql .= ' AND e.id != %d';
            $params[] = $excludeEntryId;
        }

        $sql .= ' LIMIT %d OFFSET %d';

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * @param string[] $rows
     */
    private function normalizedValueExistsInRows(
        array $rows,
        string $normalizedValue,
        callable $normalizeStoredValue
    ): bool {
        foreach ($rows as $stored) {
            if ($normalizeStoredValue((string) $stored) === $normalizedValue) {
                return true;
            }
        }

        return false;
    }

    /**
     * Batch-update fieldValue for existing entry_field rows of one entry.
     *
     * @param int $entryId
     * @param list<array{id: int, fieldValue: string}> $updates
     * @throws QueryExecutionException
     */
    public function batchUpdateValues(int $entryId, array $updates): void
    {
        if ($updates === []) {
            return;
        }

        $caseFragments = [];
        $caseValues = [];
        $ids = [];

        foreach ($updates as $update) {
            $id = (int) $update['id'];
            if ($id <= 0) {
                continue;
            }
            $caseFragments[] = 'WHEN %d THEN %s';
            $caseValues[] = $id;
            $caseValues[] = (string) $update['fieldValue'];
            $ids[] = $id;
        }

        if ($ids === []) {
            return;
        }

        $idPlaceholders = implode(',', array_fill(0, count($ids), '%d'));
        $sql = "UPDATE {$this->table}
            SET fieldValue = CASE id " . implode(' ', $caseFragments) . " END
            WHERE entryId = %d AND id IN ({$idPlaceholders})";

        $params = array_merge($caseValues, [$entryId], $ids);
        $result = $this->wpdb->query($this->wpdb->prepare($sql, ...$params));

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_update_data'] . __CLASS__
            );
        }
    }

    /**
     * Multi-row insert of entry_field rows. Sets generated IDs on the entities.
     * Inserts in chunks to stay under database packet / query-size limits.
     *
     * @param list<\IvyForms\Entity\EntryField\EntryField> $entryFields
     * @throws QueryExecutionException
     */
    public function batchInsert(array $entryFields): void
    {
        if ($entryFields === []) {
            return;
        }

        foreach (array_chunk($entryFields, self::BATCH_INSERT_CHUNK_SIZE) as $chunk) {
            $this->insertEntryFieldChunk($chunk);
        }
    }

    /**
     * @param list<\IvyForms\Entity\EntryField\EntryField> $entryFields
     * @throws QueryExecutionException
     */
    private function insertEntryFieldChunk(array $entryFields): void
    {
        $placeholders = [];
        $values = [];

        foreach ($entryFields as $entryField) {
            $data = $entryField->toArray();
            $placeholders[] = '(%d, %d, %s)';
            $values[] = (int) $data['entryId'];
            $values[] = (int) $data['fieldId'];
            $values[] = (string) $data['fieldValue'];
        }

        $sql = "INSERT INTO {$this->table} (entryId, fieldId, fieldValue) VALUES "
            . implode(', ', $placeholders);
        $result = $this->wpdb->query($this->wpdb->prepare($sql, ...$values));

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        $firstId = (int) $this->wpdb->insert_id;
        if ($firstId <= 0) {
            return;
        }

        $step = EntryQueryHelper::getAutoIncrementStep($this->wpdb);
        foreach ($entryFields as $index => $entryField) {
            $entryField->setId($firstId + $index * $step);
        }
    }
}
