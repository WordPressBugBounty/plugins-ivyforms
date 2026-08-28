<?php

namespace IvyForms\Repository\Field;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Repository\BaseRepositoryInterface;

interface FieldRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all fields by specific form ID.
     *
     * @param int $id
     *
     * @return mixed[]|null The array of entities
     */
    public function getFieldsById(int $id): ?array;

    /**
     * Get multiple fields by their IDs in a single query.
     *
     * @param int[] $fieldIds
     * @return array<int, object> Indexed by field ID
     */
    public function getFieldsByIds(array $fieldIds): array;

    /**
     * Delete multiple fields by their IDs in a single query.
     *
     * @param int[] $ids
     * @return int Number of deleted rows
     */
    public function deleteMany(array $ids): int;

    /**
     * Delete multiple fields by foreign key values (formId).
     *
     * @param array<int> $formIds
     * @param string $foreignColumn
     * @return int Number of deleted fields
     */
    public function deleteManyByForeignKeyValues(array $formIds, string $foreignColumn = 'formId'): int;

    /**
     * Delete fields by a single foreign key value (formId).
     *
     * @param int $formId
     * @param string $foreignColumn
     * @return int Number of deleted fields
     */
    public function deleteOneByForeignKeyValue(int $formId, string $foreignColumn = 'formId'): int;
}
