<?php

/**
 * @copyright © Melograno Venture Studio. All rights.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository\FieldOptions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Entity\FieldOptions\FieldOptions;
use IvyForms\Repository\BaseRepositoryInterface;

interface FieldOptionsRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Add a FieldOptions entity.
     * @param object $entity
     * @return int
     */
    public function add(object $entity): int;

    /**
     * Update a FieldOptions entity by ID.
     * @param int $id
     * @param object $entity
     * @return bool
     */
    public function update(int $id, object $entity): bool;

    /**
     * Delete all options by field ID.
     * @param int $fieldId
     * @return bool
     */
    public function deleteByFieldId(int $fieldId): bool;

    /**
     * Get all options by field ID.
     * @param int $fieldId
     * @return array<int, FieldOptions>
     */
    public function getByFieldId(int $fieldId): array;

    /**
     * Bulk delete all options for multiple field IDs.
     * @param array<int> $fieldIds
     * @return int Number of deleted rows
     */
    public function deleteByFieldIds(array $fieldIds): int;

    /**
     * Bulk delete all options for multiple field IDs based on specific column values.
     * @param string $columnName
     * @param array<int> $values
     * @return int Number of deleted rows
     */
    public function deleteByFieldIdsByColumnValues(string $columnName, array $values): int;

    /**
     * Batch add FieldOptions entities.
     * @param array<object> $entities
     * @return array<int> Inserted IDs
     */
    public function addMany(array $entities): array;

    /**
     * Batch update FieldOptions entities by ID.
     * @param array<object> $entities
     * @return int Number of updated rows
     */
    public function updateMany(array $entities): int;

    /**
     * Bulk delete options by their own IDs (not field IDs).
     * @param array<int> $optionIds
     * @return int Number of deleted rows
     */
    public function deleteByOptionIds(array $optionIds): int;
}
