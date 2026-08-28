<?php

namespace IvyForms\Entity\Field;

use IvyForms\ValueObjects\Field\Field as FieldValueObject;
use IvyForms\Entity\Field\FieldGeneralSettings;
use IvyForms\Entity\Field\FieldAdvancedSettings;

/**
 * Class Field
 *
 * @package IvyForms\Entity\Field
 */
class Field
{
    /**
     * @var FieldGeneralSettings
     */
    private FieldGeneralSettings $fieldGeneralSettings;
    /**
     * @var FieldAdvancedSettings
     */
    private FieldAdvancedSettings $fieldAdvancedSettings;
    /**
     * @var FieldValueObject
     */
    private FieldValueObject $formField;

    /**
     * @var array<string, mixed> Additional properties
     */
    private array $additionalProperties = [];

    /**
     * Field constructor.
     *
     * @param FieldValueObject $formField The form field object.
     */
    public function __construct(FieldValueObject $formField)
    {
        $this->formField = $formField;
        $this->fieldGeneralSettings = new FieldGeneralSettings($formField->getGeneralSettings());
        $this->fieldAdvancedSettings = new FieldAdvancedSettings($formField->getAdvancedSettings());

        /**
         * Allows initialization of additional properties for the Field entity.
         * @since 0.1.0
         *
         * @param mixed[] $additionalProperties The current list of additional properties.
         * @param Field $field The Field entity instance.
         * @return mixed[] The modified list of additional properties.
         */
        $this->additionalProperties = apply_filters('ivyforms/field/entity/init_additional_properties', [], $this);
    }

    /**
     * Get the ID of the form to which this field belongs
     *
     * @return int The form ID.
     */
    public function getFormId(): int
    {
        return $this->formField->getFormId();
    }

    /**
     * Set the ID of the form to which this field belongs.
     *
     * @param int $formId The form ID to set.
     */
    public function setFormId(int $formId): void
    {
        $this->formField->formId = $formId;
    }

    /**
     * Get ID of the form field.
     *
     * @return int The form field id.
     */
    public function getId(): int
    {
        return $this->formField->getId();
    }

    /**
     * Set the ID of the form field.
     *
     * @param int $id The ID to set.
     */
    public function setId(int $id): void
    {
        $this->formField->id = $id;
    }

    /**
     * Get the index of the form field.
     *
     * @return int The index of the form field.
     */
    public function getIndex(): int
    {
        return $this->formField->getIndex();
    }

    /**
     * Set the index of the form field.
     *
     * @param int $index The index to set.
     */
    public function setIndex(int $index): void
    {
        $this->formField->fieldIndex = $index;
    }

    /**
     * Get the type of the form field.
     *
     * @return string The type of the form field.
     */
    public function getType(): string
    {
        return $this->formField->getType();
    }

    /**
     * Set the type of the form field.
     *
     * @param string $type The type to set.
     */
    public function setType(string $type): void
    {
        $this->formField->type = $type;
    }

    /**
     * Get the position of the form field.
     *
     * @return int The position of the form field.
     */
    public function getPosition(): int
    {
        return $this->formField->getPosition();
    }

    /**
     * Set the position of the form field.
     *
     * @param int $position The position to set.
     */
    public function setPosition(int $position): void
    {
        $this->formField->position = $position;
    }

    /**
     * Get the row index of the form field.
     *
     * @return int The row index of the form field.
     */
    public function getRowIndex(): int
    {
        return $this->formField->getRowIndex();
    }

    /**
     * Set the row index of the form field.
     *
     * @param int $rowIndex The row index to set.
     */
    public function setRowIndex(int $rowIndex): void
    {
        $this->formField->rowIndex = $rowIndex;
    }

    /**
     * Get the column index of the form field.
     *
     * @return int The column index of the form field.
     */
    public function getColumnIndex(): int
    {
        return $this->formField->getColumnIndex();
    }

    /**
     * Set the column index of the form field.
     *
     * @param int $columnIndex The column index to set.
     */
    public function setColumnIndex(int $columnIndex): void
    {
        $this->formField->columnIndex = $columnIndex;
    }

    /**
     * Get the width percentage of the form field.
     *
     * @return int The width percentage of the form field.
     */
    public function getWidth(): int
    {
        return $this->formField->getWidth();
    }

    /**
     * Set the width percentage of the form field.
     *
     * @param int $width The width percentage to set (20-100).
     */
    public function setWidth(int $width): void
    {
        $this->formField->width = $width;
    }

    /**
     * Get the parent field ID.
     *
     * @return int|null The parent field ID.
     */
    public function getParentId(): ?int
    {
        return $this->formField->getParentId();
    }

    /**
     * Set the parent field ID.
     *
     * @param int|null $parentId The parent field ID to set.
     */
    public function setParentId(?int $parentId): void
    {
        $this->formField->parentId = $parentId;
    }

    /**
     * Get the page ID for multi-page forms.
     *
     * @return string|null The page ID.
     */
    public function getPageId(): ?string
    {
        return $this->formField->pageId;
    }

    /**
     * Set the page ID for multi-page forms.
     *
     * @param string|null $pageId The page ID to set.
     */
    public function setPageId(?string $pageId): void
    {
        $this->formField->pageId = $pageId;
    }

    /**
     * Set the number of rows (for textarea fields).
     *
     * @param int $rows
     */
    public function setRows(int $rows): void
    {
        $this->formField->setRows($rows);
    }

    /**
     * Get the FieldGeneralSettings entity.
     */
    public function getFieldGeneralSettings(): FieldGeneralSettings
    {
        return $this->fieldGeneralSettings;
    }

    /**
     * Get the FieldAdvancedSettings entity.
     */
    public function getFieldAdvancedSettings(): FieldAdvancedSettings
    {
        return $this->fieldAdvancedSettings;
    }

    /**
     * Set an additional property
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setAdditionalProperty(string $key, $value): void
    {
        foreach ($this->additionalProperties as &$property) {
            if (array_key_exists($key, $property)) {
                $property[$key] = $value; // update existing
                return;
            }
        }

        // if not found, append new key-value pair
        $this->additionalProperties[] = [$key => $value];
    }

    /**
     * Get an additional property
     *
     * @param string $key
     * @return mixed|null The value of the additional property, or null if not found
     */
    public function getAdditionalProperty(string $key)
    {
        foreach ($this->additionalProperties as $property) {
            if (array_key_exists($key, $property)) {
                return $property[$key];
            }
        }
        return null;
    }

    /**
     * Get all additional properties
     *
     * @return array<string, mixed> The additional properties
     */
    public function getAllAdditionalProperties(): array
    {
        return $this->additionalProperties;
    }

    /**
     * Convert the form field entity to an array.
     *
     * @return array<string, mixed> The form field entity as an array.
     */
    public function toArray(): array
    {
        $data = array_merge(
            [
                'id'              => $this->getId(),
                'formId'          => $this->getFormId(),
                'fieldIndex'      => $this->getIndex(),
                'type'            => $this->getType(),
                'position'        => $this->getPosition(),
                'rowIndex'        => $this->getRowIndex(),
                'columnIndex'     => $this->getColumnIndex(),
                'width'           => $this->getWidth(),
                'parentId'        => $this->getParentId(),
                'pageId'          => $this->getPageId(),
                'rows'            => $this->formField->getRows()
            ],
            $this->getFieldGeneralSettings()->toArray(),
            $this->getFieldAdvancedSettings()->toArray()
        );

        foreach ($this->additionalProperties as $property) {
            $data = array_merge($data, $property);
        }

        /**
         * Allows modification of the array representation of the Field entity.
         * @since 0.1.0
         *
         * @param mixed[] $data The current array representation of the Field entity.
         * @param Field $field The Field entity instance.
         * @return mixed[] The modified array representation of the Field entity.
         */
        return apply_filters('ivyforms/field/entity/add_to_array_additional_properties', $data, $this);
    }
}
