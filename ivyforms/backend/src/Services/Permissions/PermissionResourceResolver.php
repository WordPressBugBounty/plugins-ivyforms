<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Services\Permissions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

use IvyForms\Repository\Confirmation\ConfirmationRepositoryInterface;
use IvyForms\Repository\Entry\EntryRepositoryInterface;
use IvyForms\Repository\Notification\NotificationRepositoryInterface;

/**
 * Resolves owning form IDs for REST resources keyed by entity id (entry, notification, confirmation).
 */
final class PermissionResourceResolver
{
    private NotificationRepositoryInterface $notificationRepository;

    private EntryRepositoryInterface $entryRepository;

    private ConfirmationRepositoryInterface $confirmationRepository;

    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        EntryRepositoryInterface $entryRepository,
        ConfirmationRepositoryInterface $confirmationRepository
    ) {
        $this->notificationRepository  = $notificationRepository;
        $this->entryRepository         = $entryRepository;
        $this->confirmationRepository    = $confirmationRepository;
    }

    public function formIdFromNotificationId(int $notificationId): ?int
    {
        if ($notificationId <= 0) {
            return null;
        }

        $notification = $this->notificationRepository->getById($notificationId);
        if ($notification === null || !method_exists($notification, 'getFormId')) {
            return null;
        }

        $formId = (int) $notification->getFormId();

        return $formId > 0 ? $formId : null;
    }

    public function formIdFromEntryId(int $entryId): ?int
    {
        if ($entryId <= 0) {
            return null;
        }

        $entry = $this->entryRepository->getById($entryId);
        if ($entry === null || !method_exists($entry, 'getFormId')) {
            return null;
        }

        $formId = (int) $entry->getFormId();

        return $formId > 0 ? $formId : null;
    }

    /**
     * Resolve owning form IDs for many entries in a single query.
     *
     * @param list<int|string> $entryIds
     * @return array<int, int> entryId => formId (only found entries with a positive formId)
     */
    public function formIdsFromEntryIds(array $entryIds): array
    {
        $ids = [];
        foreach ($entryIds as $entryId) {
            $id = (int) $entryId;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        if ($ids === []) {
            return [];
        }

        return $this->entryRepository->getFormIdsByEntryIds($ids);
    }

    public function formIdFromConfirmationId(int $confirmationId): ?int
    {
        if ($confirmationId <= 0) {
            return null;
        }

        $confirmation = $this->confirmationRepository->getById($confirmationId);
        if ($confirmation === null || !method_exists($confirmation, 'getFormId')) {
            return null;
        }

        $formId = (int) $confirmation->getFormId();

        return $formId > 0 ? $formId : null;
    }
}
