<?php

/**
 * @copyright © Melograno Venture Studio. All rights.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\ValueObjects\FieldOptions;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Services\Translations\BackendStrings;

final class FieldOptions
{
    /**
     * @var int
     */
    public int $id;

    /**
     * @var int
     */
    public int $fieldId;

    /**
     * @var string
     */
    public string $label;

    /**
     * @var string
     */
    public string $value;

    /**
     * @var bool
     */
    public bool $isDefault;

    /**
     * @var int
     */
    public int $position;

    /**
     * @throws ValidationException
     */
    public function __construct(
        int $id,
        int $fieldId,
        string $label,
        string $value,
        bool $isDefault,
        int $position
    ) {
        $this->id           = $this->validatePositiveInteger($id);
        $this->fieldId      = $this->validatePositiveInteger($fieldId);
        $this->label        = $this->validateString($label, 255, 'label');
        $this->value        = $this->validateString($value, 255, 'value');
        $this->isDefault    = $this->validateBool($isDefault);
        $this->position     = $this->validatePositiveInteger($position);
    }

    /**
     * Validates the ID.
     *
     * @param int $id
     *
     * @return int
     * @throws ValidationException
     */
    private function validatePositiveInteger(int $id): int
    {
        if ($id < 0) {
            throw new ValidationException(BackendStrings::getExceptionStrings()['property_positive_integer']);
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
     * Validates a boolean field.
     *
     * @param bool $value
     *
     * @return bool
     */
    private function validateBool(bool $value): bool
    {
        return $value;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getFieldId(): int
    {
        return $this->fieldId;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }
    /**
     * @return array<string, int|string|bool>
     */
    public function toArray(): array
    {
        return [
            'id'            => $this->getId(),
            'fieldId'       => $this->getFieldId(),
            'label'         => $this->getLabel(),
            'value'         => $this->getValue(),
            'isDefault'     => $this->isDefault(),
            'position'      => $this->getPosition(),
        ];
    }
}
