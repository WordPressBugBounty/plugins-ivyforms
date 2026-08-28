<?php

namespace IvyForms\Repository\EntryField;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Entity\EntryField\EntryField;
use IvyForms\Repository\BaseRepositoryInterface;

interface EntryFieldRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Check if an entry field exists in the database.
     *
     * @param int $entryFieldId
     * @return bool
     */
    public function exists(int $entryFieldId): bool;

    /**
     * @param array<string, string> $array
     * @return array<int, EntryField>
     */
    public function findBy(array $array): array;

    /**
     * Get all entry field IDs for the given entry IDs.
     *
     * @param int[] $entryIds
     * @return int[]
     */
    public function findIdsByEntryIds(array $entryIds): array;

    /**
     * Delete multiple entry fields by their IDs in a single query.
     *
     * @param int[] $formIds
     * @return int Number of deleted rows
     */
    public function deleteByFormIds(array $formIds): int;

    /**
     * Check if a value already exists in entry fields for a specific field.
     *
     * @param int $formId
     * @param int $fieldId
     * @param string $value
     * @return bool
     */
    public function checkDuplicateValue(
        int $formId,
        int $fieldId,
        string $value,
        ?int $excludeEntryId = null
    ): bool;

    /**
     * Check if a normalized value matches any stored value after applying a normalizer.
     *
     * @param int $formId
     * @param int $fieldId
     * @param string $normalizedValue
     * @param callable(string): string $normalizeStoredValue
     * @return bool
     */
    public function checkDuplicateValueNormalized(
        int $formId,
        int $fieldId,
        string $normalizedValue,
        callable $normalizeStoredValue,
        ?int $excludeEntryId = null
    ): bool;

    /**
     * Batch-update fieldValue for existing entry_field rows of one entry.
     *
     * @param int $entryId
     * @param list<array{id: int, fieldValue: string}> $updates
     */
    public function batchUpdateValues(int $entryId, array $updates): void;

    /**
     * Multi-row insert of entry_field rows. Sets generated IDs on the entities.
     *
     * @param list<EntryField> $entryFields
     */
    public function batchInsert(array $entryFields): void;
}
