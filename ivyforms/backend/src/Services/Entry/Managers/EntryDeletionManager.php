<?php

namespace IvyForms\Services\Entry\Managers;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Repository\Entry\EntryRepositoryInterface;
use IvyForms\Repository\EntryField\EntryFieldRepositoryInterface;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Handles bulk entry deletion operations across forms.
 */
class EntryDeletionManager
{
    private EntryRepositoryInterface $entryRepository;
    private EntryFieldRepositoryInterface $entryFieldRepository;

    public function __construct(
        EntryRepositoryInterface $entryRepository,
        EntryFieldRepositoryInterface $entryFieldRepository
    ) {
        $this->entryRepository = $entryRepository;
        $this->entryFieldRepository = $entryFieldRepository;
    }

    /**
     * Delete an entry by its ID.
     *
     * @param int $id
     * @return int
     */
    public function deleteEntry(int $id): int
    {
        return $this->entryRepository->delete($id);
    }

    /**
     * Delete multiple entries by their IDs in bulk.
     *
     * @param int[] $ids
     * @return int Number of deleted entries
     */
    public function deleteEntriesByIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }
        return $this->entryRepository->deleteMany($ids);
    }

    /**
     * Delete all entry fields for multiple entry IDs in bulk.
     *
     * @param int[] $entryIds
     * @return int Number of deleted entry fields
     */
    public function deleteEntryFieldsByEntryIds(array $entryIds): int
    {
        if (empty($entryIds)) {
            return 0;
        }
        $allFields = $this->entryFieldRepository->findIdsByEntryIds($entryIds);
        if (empty($allFields)) {
            return 0;
        }
        return $this->entryFieldRepository->deleteMany($allFields);
    }

    /**
     * Delete all entries for multiple form IDs in bulk.
     *
     * @param int[] $formIds
     * @return int Number of deleted entries
     * @throws InvalidArgumentException
     */
    public function deleteEntriesByFormIds(array $formIds): int
    {
        if (empty($formIds)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['no_form_ids_provided']
            );
        }

        // Delete entry fields by form IDs using a subquery
        $this->entryFieldRepository->deleteByFormIds($formIds);
        // Delete entries by formId
        return $this->entryRepository->deleteManyByForeignKeyValues($formIds);
    }
}
