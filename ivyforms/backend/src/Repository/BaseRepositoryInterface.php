<?php

namespace IvyForms\Repository;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Interface BaseRepositoryInterface
 *
 * @package IvyForms\Repository
 */
interface BaseRepositoryInterface
{
    /**
     * Get an entity by ID.
     *
     * @param int $id
     *
     * @return object|null
     */
    public function getById(int $id): ?object;

    /**
     * Get all entities.
     *
     * @return mixed[]|null
    */
    public function getAll(): ?array;

    /**
     * Add an entity.
     *
     * @param object $entity
     * @return int
     */
    public function add(object $entity): int;

    /**
     * Update an entity by ID.
     *
     * @param int $id
     * @param object $entity
     * @return bool
     */
    public function update(int $id, object $entity): bool;

    /**
     * Delete an entity by ID.
     *
     * @param int $id
     * @return int
     */
    public function delete(int $id): int;

    /**
     * Delete multiple entities by their IDs in bulk.
     *
     * @param array<int> $ids
     * @return int Number of deleted entities
     */
    public function deleteMany(array $ids): int;

    /**
     * Delete multiple entities by foreign key values (e.g., form IDs).
     *
     * @param array<int> $foreignIds Array of foreign key values (e.g., form IDs)
     * @param string $foreignColumn Foreign key column name (default: 'formId')
     * @return int Number of deleted entities
     * @throws InvalidArgumentException If no foreign IDs are provided
     */
    public function deleteManyByForeignKeyValues(array $foreignIds, string $foreignColumn = 'formId'): int;

    /**
     * Delete entities by a single foreign key value (e.g., form ID).
     *
     * @param int $foreignId Foreign key value (e.g., form ID)
     * @param string $foreignColumn Foreign key column name (default: 'formId')
     * @return int Number of deleted entities
     * @throws InvalidArgumentException If invalid foreign column is provided
     */
    public function deleteOneByForeignKeyValue(int $foreignId, string $foreignColumn = 'formId'): int;

    /**
     * Bulk update a column for multiple entities by their IDs.
     *
     * @param string $column Column name to update
     * @param mixed $value Value to set
     * @param array<int> $ids Array of entity IDs
     * @return int Number of updated entities
     * @throws InvalidArgumentException If column is not allowed or IDs are empty
     */
    public function bulkUpdateColumn(string $column, $value, array $ids): int;

    /**
     * Search for entities based on parameters.
     *
     * @param array<string, mixed> $params
     * @return array<mixed>
     */
    public function search(array $params): array;

    /**
     * Select query for retrieving data.
     *
     * @return string
     */
    public function selectQuery(): string;

    /**
     * Begin a database transaction.
     *
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * Commit a database transaction.
     *
     * @return bool
     */
    public function commit(): bool;

    /**
     * Rollback a database transaction.
     *
     * @return bool
     */
    public function rollback(): bool;
}
