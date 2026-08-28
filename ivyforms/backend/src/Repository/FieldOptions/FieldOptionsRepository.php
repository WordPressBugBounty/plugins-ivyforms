<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository\FieldOptions;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\FieldOptions\FieldOptions;
use IvyForms\Factory\FieldOptions\FieldOptionsFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\Translations\BackendStrings;

class FieldOptionsRepository extends AbstractRepository implements FieldOptionsRepositoryInterface
{
    public const FACTORY = FieldOptionsFactory::class;

    /**
     * @throws QueryExecutionException
     */
    public function add($option): int
    {
        $data = $option->toArray();

        $result = $this->wpdb->insert(
            $this->table,
            [
                'fieldId'    => $data['fieldId'],
                'label'      => $data['label'],
                'value'      => $data['value'],
                'isDefault'  => (int) $data['isDefault'],
                'position'   => $data['position'] ?? 1,
            ],
            ['%d', '%s', '%s', '%d', '%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__);
        }

        return $this->wpdb->insert_id;
    }

    /**
     * @throws QueryExecutionException
     */
    public function update(int $id, object $entity): bool
    {
        $data = $entity->toArray();
        $result = $this->wpdb->update(
            $this->table,
            [
                'fieldId'   => $data['fieldId'],
                'label'     => $data['label'],
                'value'     => $data['value'],
                'isDefault' => (int)$data['isDefault'],
                'position'  => $data['position'] ?? 1,
            ],
            ['id' => $id],
            ['%d', '%s', '%s', '%d', '%d'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return (bool)$result;
    }

    /**
     * Delete all options for a given field ID
     * @param int $fieldId
     * @return bool
     * @throws QueryExecutionException
     */
    public function deleteByFieldId(int $fieldId): bool
    {
        $result = $this->wpdb->delete(
            $this->table,
            ['fieldId' => $fieldId],
            ['%d']
        );
        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_delete_data'] . __CLASS__
            );
        }
        return true;
    }

    /**
     * Bulk delete all options for multiple field IDs.
     * @param array<int> $fieldIds
     * @return int Number of deleted rows
     * @throws QueryExecutionException
     */
    public function deleteByFieldIds(array $fieldIds): int
    {
        if (empty($fieldIds)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($fieldIds), '%d'));
        $query = "DELETE FROM {$this->table} WHERE fieldId IN ($placeholders)";
        $result = $this->wpdb->query($this->wpdb->prepare($query, ...$fieldIds));
        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['failed_delete_by_foreign_keys'] . __CLASS__
            );
        }
        return $result;
    }

    /**
     * Bulk delete all options for field IDs matching a column and values (safe, prevents SQL injection).
     * @param string $columnName
     * @param array<int> $values
     * @return int Number of deleted rows
     * @throws QueryExecutionException|InvalidArgumentException
     */
    public function deleteByFieldIdsByColumnValues(string $columnName, array $values): int
    {
        // Whitelist allowed columns
        $allowedColumns = ['formId'];
        if (!in_array($columnName, $allowedColumns, true)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_column_name_deletion']
            );
        }
        if (empty($values)) {
            return 0;
        }
        $fieldsTable = FieldsTable::getTableName();
        $placeholders = implode(',', array_fill(0, count($values), '%d'));
        $query = "DELETE FROM {$this->table} WHERE fieldId IN 
                        (SELECT id FROM {$fieldsTable} WHERE {$columnName} IN ($placeholders))";
        $result = $this->wpdb->query($this->wpdb->prepare($query, ...$values));
        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['failed_delete_by_foreign_keys'] . __CLASS__
            );
        }
        return $result;
    }

    /**
     * Get all options for a given field ID, ordered by position
     * @param int $fieldId
     * @return array<int, FieldOptions>
     * @throws ValidationException
     * @throws QueryExecutionException
     */
    public function getByFieldId(int $fieldId): array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM $this->table WHERE fieldId = %d ORDER BY position ASC",
                $fieldId
            ),
            ARRAY_A
        );
        if ($rows === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        if (empty($rows)) {
            return [];
        }
        return array_map(function ($row) {
            return call_user_func([static::FACTORY, 'create'], $row);
        }, $rows);
    }

    /**
     * Batch add FieldOptions entities.
     * @param array<object> $entities
     * @return array<int> Inserted IDs
     * @throws QueryExecutionException
     */
    public function addMany(array $entities): array
    {
        if (empty($entities)) {
            return [];
        }
        $insertedIds = [];
        foreach ($entities as $option) {
            $insertedIds[] = $this->add($option);
        }
        return $insertedIds;
    }

    /**
     * Batch update FieldOptions entities by ID.
     * @param array<object> $entities
     * @return int Number of updated rows
     * @throws QueryExecutionException
     */
    public function updateMany(array $entities): int
    {
        if (empty($entities)) {
            return 0;
        }
        $updatedCount = 0;
        foreach ($entities as $option) {
            if ($this->update($option->getId(), $option)) {
                $updatedCount++;
            }
        }
        return $updatedCount;
    }

    /**
     * Bulk delete options by their own IDs (not field IDs).
     * @param array<int> $optionIds
     * @return int Number of deleted rows
     * @throws QueryExecutionException
     */
    public function deleteByOptionIds(array $optionIds): int
    {
        if (empty($optionIds)) {
            return 0;
        }
        $placeholders = implode(',', array_fill(0, count($optionIds), '%d'));
        $query = "DELETE FROM {$this->table} WHERE id IN ($placeholders)";
        $result = $this->wpdb->query($this->wpdb->prepare($query, ...$optionIds));
        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['failed_delete_by_ids'] . __CLASS__
            );
        }
        return $result;
    }
}
