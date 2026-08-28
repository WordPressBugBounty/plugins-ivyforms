<?php

namespace IvyForms\Services\Entry;

use IvyForms\Services\Entry\Managers\EntryManager;
use IvyForms\Services\Entry\Managers\EntryFieldManager;
use IvyForms\Services\Entry\Managers\EntryDeletionManager;

/**
 * Main entry service - provides access to entry managers.
 * Controllers access managers directly for operations.
 */
class EntryService
{
    private EntryManager $entryManager;
    private EntryFieldManager $entryFieldManager;
    private EntryDeletionManager $deletionManager;

    public function __construct(
        EntryManager $entryManager,
        EntryFieldManager $entryFieldManager,
        EntryDeletionManager $deletionManager
    ) {
        $this->entryManager = $entryManager;
        $this->entryFieldManager = $entryFieldManager;
        $this->deletionManager = $deletionManager;
    }

    /**
     * Get the EntryManager for entry-related operations.
     *
     * @return EntryManager
     */
    public function getEntryManager(): EntryManager
    {
        return $this->entryManager;
    }

    /**
     * Get the EntryFieldManager for entry field operations.
     *
     * @return EntryFieldManager
     */
    public function getEntryFieldManager(): EntryFieldManager
    {
        return $this->entryFieldManager;
    }

    /**
     * Get the EntryDeletionManager for deletion operations.
     *
     * @return EntryDeletionManager
     */
    public function getDeletionManager(): EntryDeletionManager
    {
        return $this->deletionManager;
    }
}
