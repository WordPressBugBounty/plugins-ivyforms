<?php

namespace IvyForms\ValueObjects\Field;

/**
 * Class FieldOptions
 *
 * Encapsulates options for a field.
 */
final class FieldOptions
{
    /**
     * @var array<int, array<string, int|string|bool>>
     */
    private array $options;

    /**
     * FieldOptions constructor.
     *
     * @param array<int, array<string, int|string|bool>> $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * Get the options array.
     *
     * @return array<int, array<string, int|string|bool>>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Convert the field options to an array.
     *
     * @return array<int, array<string, int|string|bool>>
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
