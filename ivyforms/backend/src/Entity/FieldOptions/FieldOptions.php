<?php

/**
 * @copyright © Melograno Venture Studio. All rights.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Entity\FieldOptions;

use IvyForms\ValueObjects\FieldOptions\FieldOptions as FieldOptionsValueObject;

class FieldOptions
{
    private FieldOptionsValueObject $fieldOptions;
    public function __construct(FieldOptionsValueObject $fieldOptions)
    {
        $this->fieldOptions = $fieldOptions;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->fieldOptions->getId();
    }

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->fieldOptions->id = $id;
    }

    /**
     * @return int
     */
    public function getFieldId(): int
    {
        return $this->fieldOptions->getFieldId();
    }

    /**
     * @param int $fieldId
     * @return void
     */
    public function setFieldId(int $fieldId): void
    {
        $this->fieldOptions->fieldId = $fieldId;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->fieldOptions->getLabel();
    }

    /**
     * @param string $label
     * @return void
     */
    public function setLabel(string $label): void
    {
        $this->fieldOptions->label = $label;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->fieldOptions->getValue();
    }

    /**
     * @param string $value
     * @return void
     */
    public function setValue(string $value): void
    {
        $this->fieldOptions->value = $value;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->fieldOptions->isDefault();
    }

    /**
     * @param bool $isDefault
     * @return void
     */
    public function setDefault(bool $isDefault): void
    {
        $this->fieldOptions->isDefault = $isDefault;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->fieldOptions->getPosition();
    }

    /**
     * @param int $position
     * @return void
     */
    public function setPosition(int $position): void
    {
        $this->fieldOptions->position = $position;
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
