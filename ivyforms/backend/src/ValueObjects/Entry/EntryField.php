<?php

namespace IvyForms\ValueObjects\Entry;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class EntryField
 *
 * Represents a single field value in a form entry.
 */
final class EntryField
{
    /**
     * @var int|null
     */
    public ?int $id;
    /**
     * @var int
     */
    public int $fieldId;
    /**
     * @var string
     */
    public string $fieldValue;
    /**
     * @var int
     */
    public int $entryId;

    /**
     * EntryField constructor.
     *
     * @param int|null $id
     * @param int $entryId
     * @param int $fieldId
     * @param string $fieldValue
     * @throws ValidationException
     */
    public function __construct(
        ?int $id,
        int $entryId,
        int $fieldId,
        string $fieldValue
    ) {
        $this->id = $id ? $this->validateId($id) : null;
        $this->entryId = $this->validateId($entryId);
        $this->fieldId = $this->validateId($fieldId);
        $this->fieldValue = $this->validateString($fieldValue, 65535, 'fieldValue');
    }

    /**
     * Get the ID of the entry field.
     *
     * @return int|null The entry field ID.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the field ID.
     *
     * @return int The field ID.
     */
    public function getFieldId(): int
    {
        return $this->fieldId;
    }

    /**
     * Get the field value.
     *
     * @return string The field value.
     */
    public function getFieldValue(): string
    {
        return $this->fieldValue;
    }

    /**
     * Get the entry ID.
     *
     * @return int The entry ID or null if not set.
     */
    public function getEntryId(): int
    {
        return $this->entryId;
    }

    /**
     * Validates the ID.
     *
     * @param int $id
     *
     * @return int
     * @throws ValidationException
     */
    private function validateId(int $id): int
    {
        if ($id < 0) {
            throw new ValidationException(BackendStrings::getExceptionStrings()['id_positive_integer']);
        }
        return $id;
    }

    /**
     * Validates a string value.
     *
     * @param string $value
     * @param int    $maxLength
     * @param string $fieldName
     *
     * @return string
     *
     * @throws ValidationException
     */
    private function validateString(string $value, int $maxLength, string $fieldName): string
    {
        if (strlen($value) > $maxLength) {
            throw new ValidationException(
                sprintf(
                /* translators: 1: String value, 2: String max length. */
                    esc_html__('%1$s must be at most %2$d characters.', 'ivyforms'),
                    esc_html($fieldName),
                    $maxLength
                )
            );
        }
        return $value;
    }

    /**
     * Convert the EntryField object to an array.
     *
     * @return array<mixed> The array representation of the EntryField.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'entryId' => $this->getEntryId(),
            'fieldId' => $this->getFieldId(),
            'fieldValue' => $this->getFieldValue(),
        ];
    }
}
