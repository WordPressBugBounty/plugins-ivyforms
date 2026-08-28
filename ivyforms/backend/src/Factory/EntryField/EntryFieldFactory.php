<?php

namespace IvyForms\Factory\EntryField;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Entity\EntryField\EntryField as EntryFieldEntity;
use IvyForms\ValueObjects\Entry\EntryField as EntryFieldValueObject;

class EntryFieldFactory
{
    /**
     * Create an EntryField entity from array data.
     *
     * @param array<mixed> $data
     * @return EntryFieldEntity
     * @throws ValidationException
     */
    public static function create(array $data): EntryFieldEntity
    {
        $normalizedFieldValue = Sanitizer::normalizeFieldValue($data['fieldValue'] ?? '');
        $entryFieldObject = new EntryFieldValueObject(
            $data['id'] ?? null,
            $data['entryId'] ?? null,
            $data['fieldId'] ?? '',
            $normalizedFieldValue
        );

        $entryField = new EntryFieldEntity($entryFieldObject);

        if (isset($data['id'])) {
            $entryField->setId($data['id']);
        }
        if (isset($data['entryId'])) {
            $entryField->setEntryId($data['entryId']);
        }
        if (isset($data['fieldId'])) {
            $entryField->setFieldId($data['fieldId']);
        }
        if (isset($data['fieldValue'])) {
            $entryField->setFieldValue($normalizedFieldValue);
        }
        return $entryField;
    }
}
