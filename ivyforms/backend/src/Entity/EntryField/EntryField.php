<?php

namespace IvyForms\Entity\EntryField;

use IvyForms\ValueObjects\Entry\EntryField as EntryFieldValueObject;

/**
 * Class EntryField
 *
 * @package IvyForms\Entity\Entry
 */
class EntryField
{
    /**
     * @var EntryFieldValueObject
     */
    private EntryFieldValueObject $field;

    /**
     * EntryField constructor.
     *
     * @param EntryFieldValueObject $field The entry field value object.
     */
    public function __construct(EntryFieldValueObject $field)
    {
        $this->field = $field;
    }

    public function getId(): ?int
    {
        return $this->field->id;
    }
    public function setId(int $id): void
    {
        $this->field->id = $id;
    }

    public function getFieldId(): int
    {
        return $this->field->fieldId;
    }
    public function setFieldId(int $fieldId): void
    {
        $this->field->fieldId = $fieldId;
    }

    public function getFieldValue(): string
    {
        return $this->field->fieldValue;
    }
    public function setFieldValue(string $fieldValue): void
    {
        $this->field->fieldValue = $fieldValue;
    }
    public function getField(): EntryFieldValueObject
    {
        return $this->field;
    }
    /**
     * Get the entry ID associated with the entry field.
     *
     * @return int|null The entry ID or null if not set.
     */

    public function getEntryId(): ?int
    {
        return $this->field->entryId;
    }
    /**
     * Set the entry ID associated with the entry field.
     *
     * @param int|null $entryId The entry ID to set.
     */
    public function setEntryId(?int $entryId): void
    {
        $this->field->entryId = $entryId;
    }

    /**
     * Convert the entry field to an array representation.
     *
     * @return array<mixed> The array representation of the entry field.
     */
    public function toArray(): array
    {
        return $this->field->toArray();
    }
}
