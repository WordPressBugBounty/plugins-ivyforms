<?php

namespace IvyForms\Repository\EntryField;

interface EntryFieldNameEmailRepositoryInterface
{
    /**
     * Name and email field values keyed by entry ID, then type_fieldIndex.
     *
     * @param int[] $entryIds
     * @return array<int, array<string, string>>
     */
    public function findNameEmailValuesByEntryIds(array $entryIds): array;
}
