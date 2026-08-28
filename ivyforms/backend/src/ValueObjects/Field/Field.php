<?php

/**
* @copyright © Melograno Venture Studio. All rights reserved.
* @licence   See LICENCE.md for license details.
*/

namespace IvyForms\ValueObjects\Field;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Services\Translations\BackendStrings;

/**
* Class Field
*
 * @SuppressWarnings(PHPMD)
* @package IvyForms\ValueObjects\Field
*/
final class Field
{
    /**
     * @var int
     */
    public int $id;
    /**
     * @var int
     */
    public int $formId;
    /**
     * @var int
     */
    public int $fieldIndex;
    /**
     * @var string
     */
    public string $type;
    /**
     * @var int
     */
    public int $position;
    /**
     * @var int Row index for grid layout (0-based)
     */
    public int $rowIndex;
    /**
     * @var int Column index within row (0-based, max 4 for 5 fields per row)
     */
    public int $columnIndex;
    /**
     * @var int Field width percentage (20, 25, 33, 50, or 100)
     */
    public int $width;
    /**
     * @var int|null
     */
    public ?int $parentId;
    /**
     * @var string|null Page ID for multi-page forms
     */
    public ?string $pageId;
    /**
     * @var FieldGeneralSettings
     */
    public FieldGeneralSettings $generalSettings;
    /**
     * @var FieldOptions
     */
    public FieldOptions $fieldOptions;
    /**
     * @var int Number of rows (for textarea fields)
     */
    public int $rows;
    /** @var bool */
    public bool $confirmFieldEnabled;
    /** @var string */
    public string $confirmFieldLabel;
    /** @var string */
    public string $confirmFieldPlaceholder;
    /** @var bool */
    public bool $confirmFieldHideLabel;
    /**
     * @var FieldAdvancedSettings
     */
    public FieldAdvancedSettings $advancedSettings;
    /**
     * @var array<string, mixed> Additional properties from Pro plugin
     */
    private array $additionalProperties = [];

    /**
     * Field constructor.
     * @SuppressWarnings(PHPMD)
     *
     * @param int $id
     * @param int $formId
     * @param int $fieldIndex
     * @param string $type
     * @param int $position
     * @param int|null $parentId
     * @param FieldGeneralSettings $generalSettings
     * @param FieldOptions $fieldOptions
     * @param FieldAdvancedSettings $advancedSettings
     * @param int $rows
     * @param int $rowIndex
     * @param int $columnIndex
     * @param int $width
     * @param string|null $pageId
     *
     * @throws ValidationException
     */
    public function __construct(
        int $id,
        int $formId,
        int $fieldIndex,
        string $type,
        int $position,
        FieldGeneralSettings $generalSettings,
        FieldOptions $fieldOptions,
        FieldAdvancedSettings $advancedSettings,
        ?int $parentId = null,
        int $rows = 0,
        int $rowIndex = 0,
        int $columnIndex = 0,
        int $width = 100,
        ?string $pageId = null
    ) {
        $this->id = $this->validateId($id);
        $this->formId = $this->validateId($formId);
        $this->fieldIndex = $this->validateId($fieldIndex);
        $this->type = $this->validateString($type, 50, 'type');
        $this->position = $this->validatePosition($position);
        $this->rowIndex = $this->validateRowIndex($rowIndex);
        $this->columnIndex = $this->validateColumnIndex($columnIndex);
        $this->width = $this->validateWidth($width);
        $this->parentId = $this->validateParentId($parentId);
        $this->pageId = $pageId;
        $this->generalSettings = $generalSettings;
        $this->fieldOptions = $fieldOptions;
        $this->advancedSettings = $advancedSettings;
        $this->rows = $this->validateRows($rows);

        /**
         * Allows initialization of additional properties for the Field value object.
         * @since 0.1.0
         *
         * @param mixed[] $additionalProperties The current list of additional properties.
         * @param Field $field The Field value object instance.
         * @return mixed[] The modified list of additional properties.
         */
        $this->additionalProperties =
            apply_filters('ivyforms/field/value_object/init_additional_properties', [], $this);
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
     * Validates the ID.
     *
     * @param int $id
     *
     * @return int
     * @throws ValidationException
     */
    public function validateId(int $id): int
    {
        if ($id < 0) {
            throw new ValidationException(BackendStrings::getExceptionStrings()['id_positive_integer']);
        }
        return $id;
    }

    /**
     * Validates parent id which can be null or non-negative int.
     *
     * @param int|null $parentId
     *
     * @return int|null
     * @throws ValidationException
     */
    private function validateParentId(?int $parentId): ?int
    {
        if ($parentId === null) {
            return null;
        }
        if ($parentId < 0) {
            throw new ValidationException(BackendStrings::getExceptionStrings()['parentId_positive_integer']);
        }
        return $parentId;
    }

    /**
     * Validates a string value.
     *
     * @param string $value
     * @param int $maxLength
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
     * Validates the position.
     *
     * @param int $value
     *
     * @return int
     *
     * @throws ValidationException
     */
    private function validatePosition(int $value): int
    {
        if ($value < 0) {
            throw new ValidationException(
                BackendStrings::getExceptionStrings()['position_non_negative']
            );
        }
        return $value;
    }

    /**
     * Validates the row index.
     *
     * @param int $value
     *
     * @return int
     *
     * @throws ValidationException
     */
    private function validateRowIndex(int $value): int
    {
        if ($value < 0) {
            throw new ValidationException(
                BackendStrings::getExceptionStrings()['row_index_non_negative']
            );
        }
        return $value;
    }

    /**
     * Validates the column index (0-4 for max 5 fields per row).
     *
     * @param int $value
     *
     * @return int
     *
     * @throws ValidationException
     */
    private function validateColumnIndex(int $value): int
    {
        if ($value < 0 || $value > 4) {
            throw new ValidationException(
                BackendStrings::getExceptionStrings()['column_index_range']
            );
        }
        return $value;
    }

    /**
     * Validates the width percentage.
     *
     * @param int $value
     *
     * @return int
     *
     * @throws ValidationException
     */
    private function validateWidth(int $value): int
    {
        if ($value < 20 || $value > 100) {
            throw new ValidationException(
                BackendStrings::getExceptionStrings()['width_range']
            );
        }
        return $value;
    }

    /**
     * Validate rows.
     *
     * @param int $rows
     * @return int
     * @throws ValidationException
     */
    private function validateRows(int $rows): int
    {
        if ($rows < 0) {
            throw new ValidationException(
                BackendStrings::getExceptionStrings()['rows_non_negative']
            );
        }
        return $rows;
    }

    /**
     * Get the form ID.
     *
     * @return int
     */
    public function getFormId(): int
    {
        return $this->formId;
    }

    /**
     * Get the field ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the field index.
     *
     * @return int
     */
    public function getIndex(): int
    {
        return $this->fieldIndex;
    }

    /**
     * Get the field type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the field position.
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Get the row index.
     *
     * @return int
     */
    public function getRowIndex(): int
    {
        return $this->rowIndex;
    }

    /**
     * Get the column index.
     *
     * @return int
     */
    public function getColumnIndex(): int
    {
        return $this->columnIndex;
    }

    /**
     * Get the field width percentage.
     *
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Get the parent field id.
     *
     * @return int|null
     */
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    /**
     * Get the general settings object.
     *
     * @return FieldGeneralSettings
     */
    public function getGeneralSettings(): FieldGeneralSettings
    {
        return $this->generalSettings;
    }

    /**
     * Get the field options.
     *
     * @return FieldOptions
     */
    public function getFieldOptions(): FieldOptions
    {
        return $this->fieldOptions;
    }

    /**
     * Get number of rows.
     * @return int
     */
    public function getRows(): int
    {
        return $this->rows;
    }

    /**
     * Set number of rows.
     * @param int $rows
     */
    public function setRows(int $rows): void
    {
        $this->rows = $rows;
    }

    /**
     * Get the general settings object.
     *
     * @return FieldAdvancedSettings
     */
    public function getAdvancedSettings(): FieldAdvancedSettings
    {
        return $this->advancedSettings;
    }

    /**
     * Convert the field to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id'                    => $this->id,
            'formId'                => $this->formId,
            'fieldIndex'            => $this->fieldIndex,
            'type'                  => $this->type,
            'position'              => $this->position,
            'rowIndex'              => $this->rowIndex,
            'columnIndex'           => $this->columnIndex,
            'width'                 => $this->width,
            'parentId'              => $this->parentId,
            'generalSettings'       => $this->generalSettings->toArray(),
            'fieldOptions'          => $this->fieldOptions->toArray(),
            'rows'                  => $this->rows,
            // Advanced options
            'advancedSettings'      => $this->advancedSettings->toArray()
        ];

        /**
         * Allows modification of the array representation of the Field value object.
         * @since 0.1.0
         *
         * @param mixed[] $data The current array representation of the Field value object.
         * @param Field $field The Field value object instance.
         * @return mixed[] The modified array representation of the Field value object.
         */
        return apply_filters('ivyforms/field/value_object/add_to_array_additional_properties', $data, $this);
    }
}
