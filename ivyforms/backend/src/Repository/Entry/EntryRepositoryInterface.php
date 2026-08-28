<?php

namespace IvyForms\Repository\Entry;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Repository\BaseRepositoryInterface;

interface EntryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Check if an entry exists in the database.
     *
     * @param int $entryId
     * @return bool
     */
    public function exists(int $entryId): bool;

    /**
     * Get the count of entries for a specific form.
     *
     * @param int $formId
     * @return int
     */
    public function getCountByFormId(int $formId): int;

    /**
     * Get the count of entries for multiple form IDs.
     *
     * @param int[] $formIds
     * @return array<int, int> formId => count
     */
    public function getEntryCountByFormIds(array $formIds): array;

    /**
     * Get the count of entries for the filter dropdown.
     *
     * @param array<string, mixed>|null $params
     * @return array<string, int>
     */
    public function getFilterCount(?array $params = null): array;

    /**
     * Update the starred status of an entry.
     *
     * @param int $entryId
     * @param bool $value
     * @return void
     */
    public function updateEntryStarred(int $entryId, bool $value): void;

    /**
     * Update the status of an entry.
     *
     * @param int $entryId
     * @param string $status
     * @return void
     */
    public function updateEntryStatus(int $entryId, string $status): void;

    /**
     * Get the entries with their fields based on search parameters.
     *
     * @param array<int, array<string, mixed>> $entries
     * @return array<mixed>
     */
    public function getEntryFields(array $entries): array;

    /**
     * Resolve owning form IDs for the given entry IDs in a single query.
     *
     * @param int[] $entryIds
     * @return array<int, int> entryId => formId
     */
    public function getFormIdsByEntryIds(array $entryIds): array;
}
