<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Entity\Entry;

use IvyForms\Common\Helpers\EntryHelper;
use IvyForms\ValueObjects\Entry\Entry as EntryValueObject;

/**
 * Class Entry
 *
 * @package IvyForms\Entity\Entry
 */
class Entry
{
    public const DEFAULT_STATUS = 'unread';

    /**
     * @var EntryValueObject
     */
    private EntryValueObject $entry;

    /**
     * Entry constructor.
     *
     * @param EntryValueObject $entry The entry object.
     */
    public function __construct(EntryValueObject $entry)
    {
        $this->entry = $entry;
    }

    /**
     * Get the ID of the entry.
     *
     * @return int The entry ID.
     */
    public function getId(): int
    {
        return $this->entry->getId();
    }

    /**
     * Set the ID of the entry.
     *
     * @param int $entryId The entry ID to set.
     */
    public function setId(int $entryId): void
    {
        $this->entry->id = $entryId;
    }

    /**
     * Get the form ID associated with the entry.
     *
     * @return int The form ID.
     */
    public function getFormId(): int
    {
        return $this->entry->getFormId();
    }

    /**
     * Set the form ID associated with the entry.
     *
     * @param int $formId The form ID to set.
     */
    public function setFormId(int $formId): void
    {
        $this->entry->formId = $formId;
    }

    /**
     * Get the user ID associated with the entry.
     *
     * @return int|null The user ID or null if not set.
     */
    public function getUserId(): ?int
    {
        return $this->entry->getUserId();
    }

    /**
     * Set the user ID associated with the entry.
     *
     * @param int|null $userId The user ID to set.
     */
    public function setUserId(?int $userId): void
    {
        $this->entry->userId = $userId;
    }

    /**
     * Get the date the entry was created.
     *
     * @return string The date the entry was created.
     */
    public function getDateCreated(): string
    {
        return $this->entry->getDateCreated();
    }

    /**
     * Set the date the entry was created.
     *
     * @param string $dateCreated The persisted creation timestamp.
     */
    public function setDateCreated(string $dateCreated): void
    {
        $this->entry->dateCreated = $dateCreated;
    }

    /**
     * Get the date the entry was last edited.
     *
     * @return string|null The date the entry was last edited or null if not set.
     */
    public function getDateEdited(): ?string
    {
        return $this->entry->getDateEdited();
    }

    /**
     * Set the date the entry was last edited.
     *
     * @param string $dateEdited The persisted last-edited timestamp.
     */
    public function setDateEdited(string $dateEdited): void
    {
        $this->entry->dateEdited = $dateEdited;
    }

    /**
     * Get the status of the entry.
     *
     * @return string The status of the entry.
     */
    public function getStatus(): string
    {
        return $this->entry->getStatus();
    }

    /**
     * Set the status of the entry.
     *
     * @param string $status The status to set.
     */
    public function setStatus(string $status): void
    {
        $this->entry->status = $status;
    }

    /**
     * Get the IP address associated with the entry.
     *
     * @return string|null The IP address or null if not set.
     */
    public function getIpAddress(): ?string
    {
        return $this->entry->getIpAddress();
    }

    /**
     * Set the IP address associated with the entry.
     *
     * @param string|null $ipAddress The IP address to set.
     */
    public function setIpAddress(?string $ipAddress): void
    {
        $this->entry->ipAddress = $ipAddress;
    }

    /**
     * Get the user agent associated with the entry.
     *
     * @return string|null The user agent or null if not set.
     */
    public function getUserAgent(): ?string
    {
        return $this->entry->getUserAgent();
    }
    /**
     * Set the user agent associated with the entry.
     *
     * @param string|null $userAgent The user agent to set.
     */
    public function setUserAgent(?string $userAgent): void
    {
        $this->entry->userAgent = $userAgent;
    }

    /**
     * Get the source URL of the entry.
     *
     * @return string|null The source URL or null if not set.
     */
    public function getSourceURL(): ?string
    {
        return $this->entry->getSourceURL();
    }

    /**
     * Set the source URL of the entry.
     *
     * @param string|null $sourceURL The source URL to set.
     */
    public function setSourceURL(?string $sourceURL): void
    {
        $this->entry->sourceURL = $sourceURL;
    }

    /**
     * Set the starred status of the entry.
     *
     * @param bool $starred True if the entry is starred, false otherwise.
     */
    public function setStarred(bool $starred): void
    {
        $this->entry->starred = $starred;
    }

    /**
     * Check if the entry is starred.
     *
     * @return bool True if the entry is starred, false otherwise.
     */
    public function isStarred(): bool
    {
        return $this->entry->isStarred();
    }

    /**
     * Convert the entry entity to an array.
     *
     * @param bool $raw If true, returns raw values (for DB), otherwise formatted for response.
     * @return array<string, mixed> The form entity as an array.
     */
    public function toArray(bool $raw = false): array
    {
        return [
            'id'            => $this->getId(),
            'formId'        => $this->getFormId(),
            'userId'        => $this->getUserId(),
            'author'        => get_userdata($this->getUserId())->user_nicename ?? '',
            'dateCreated'   => $this->getDateCreated(),
            'dateEdited'    => $this->getDateEdited(),
            'status'        => $this->getStatus(),
            'ipAddress'     => $this->getIpAddress(),
            'userAgent'     => $raw ? $this->getUserAgent() : EntryHelper::getBrowserName($this->getUserAgent()),
            'sourceURL'     => $this->getSourceURL(),
            'starred'       => $this->isStarred(),
        ];
    }
}
