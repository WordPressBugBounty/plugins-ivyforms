<?php

namespace IvyForms\Services\Entry\Managers;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Entity\Entry\Entry;
use IvyForms\Repository\Entry\EntryRepositoryInterface;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Manages Entry repository operations.
 */
class EntryManager
{
    private EntryRepositoryInterface $entryRepository;

    public function __construct(EntryRepositoryInterface $entryRepository)
    {
        $this->entryRepository = $entryRepository;
    }

    /**
     * Create a new entry.
     *
     * @param Entry $entryData
     * @return int Entry ID
     */
    public function createEntry(Entry $entryData): int
    {
        return $this->entryRepository->add($entryData);
    }

    /**
     * Get a specific entry by its ID.
     *
     * @param int $entryId
     * @return object
     * @throws NotFoundException If the entry is not found
     */
    public function getEntry(int $entryId): object
    {
        $entry = $this->entryRepository->getById($entryId);

        if (!$entry) {
            throw new NotFoundException(
                BackendStrings::getExceptionStrings()['entry_not_found']
            );
        }

        return $entry;
    }

    /**
     * Search for entries based on parameters.
     *
     * @param array<string, mixed> $params
     * @return array<mixed>
     */
    public function searchEntries(array $params): array
    {
        return $this->entryRepository->search($params);
    }

    /**
     * Get the count of entry filters.
     *
     * @param array<string, mixed>|null $params
     * @return array<mixed>
     */
    public function getFilterCount(?array $params = null): array
    {
        return $this->entryRepository->getFilterCount($params);
    }

    /**
     * Get the count of entries for multiple form IDs.
     *
     * @param int[] $formIds
     * @return array<int, int> formId => count
     */
    public function getEntryCountByFormIds(array $formIds): array
    {
        return $this->entryRepository->getEntryCountByFormIds($formIds);
    }

    /**
     * Update the starred status of an entry.
     *
     * @param int $entryId
     * @param bool $value
     * @return void
     */
    public function updateEntryStarred(int $entryId, bool $value): void
    {
        $this->entryRepository->updateEntryStarred($entryId, $value);
    }

    /**
     * Update the status of an entry.
     *
     * @param int $entryId
     * @param string $status
     * @return void
     */
    public function updateEntryStatus(int $entryId, string $status): void
    {
        $this->entryRepository->updateEntryStatus($entryId, $status);
    }

    /**
     * Bulk update a column for multiple entries.
     *
     * @param string $column Column name to update
     * @param mixed $value Value to set
     * @param array<int> $ids Array of entry IDs
     * @return int Number of updated entries
     */
    public function bulkUpdateColumn(string $column, $value, array $ids): int
    {
        return $this->entryRepository->bulkUpdateColumn($column, $value, $ids);
    }

    /**
     * Get all possible entry columns
     *
     * @return array<string, string>
     */
    public static function getAllEntryColumns(): array
    {
        return [
            'id'            => BackendStrings::getEntriesStrings()['id'],
            'formId'        => BackendStrings::getEntriesStrings()['form_id'],
            'userId'        => BackendStrings::getEntriesStrings()['user_id'],
            'dateCreated'   => BackendStrings::getEntriesStrings()['date_created'],
            'status'        => BackendStrings::getEntriesStrings()['status'],
            'ipAddress'     => BackendStrings::getEntriesStrings()['ip_address'],
            'userAgent'     => BackendStrings::getEntriesStrings()['user_agent'],
            'sourceURL'     => BackendStrings::getEntriesStrings()['source_url'],
            'starred'       => BackendStrings::getEntriesStrings()['starred'],
        ];
    }
}
