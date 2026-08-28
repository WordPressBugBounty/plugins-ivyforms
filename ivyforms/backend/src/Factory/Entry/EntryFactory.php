<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Factory\Entry;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\Entry\Entry as EntryEntity;
use IvyForms\Entity\EntryField\EntryField;
use IvyForms\Factory\EntryField\EntryFieldFactory;
use IvyForms\ValueObjects\Entry\Entry;

/**
 * Class EntryFactory
 *
 * @package IvyForms\Factory\Entry
 */
class EntryFactory
{
    /**
     * Create an EntryEntity from the provided data.
     *
     * @param array<string, mixed> $data The entry data.
     *
     * @return EntryEntity
     *
     * @throws ValidationException
     */
    public static function create(array $data): EntryEntity
    {

        $entryObject = new Entry(
            $data['id'] ?? 0,
            $data['formId'] ?? 0,
            $data['userId'] ?? null,
            $data['status'] ?? EntryEntity::DEFAULT_STATUS,
            $data['ipAddress'] ?? null,
            $data['userAgent'] ?? null,
            $data['sourceURL'] ?? null,
        );

        $entry = new EntryEntity($entryObject);

        if (isset($data['id'])) {
            $entry->setId($data['id']);
        }

        if (isset($data['formId'])) {
            $entry->setFormId($data['formId']);
        }
        if (isset($data['userId'])) {
            $entry->setUserId($data['userId']);
        }
        if (isset($data['dateCreated'])) {
            $entry->setDateCreated((string) $data['dateCreated']);
        }
        if (isset($data['dateEdited'])) {
            $entry->setDateEdited((string) $data['dateEdited']);
        }
        if (isset($data['status'])) {
            $entry->setStatus($data['status']);
        }
        if (isset($data['ipAddress'])) {
            $entry->setIpAddress($data['ipAddress']);
        }
        if (isset($data['userAgent'])) {
            $entry->setUserAgent($data['userAgent']);
        }
        if (isset($data['sourceURL'])) {
            $entry->setSourceURL($data['sourceURL']);
        }
        if (isset($data['starred'])) {
            $entry->setStarred($data['starred']);
        }

        return $entry;
    }
}
