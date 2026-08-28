<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Extract IvyForms field options from Fluent Forms elements.
 *
 * Splitting option extraction out of FluentFormsFieldMapper keeps each class
 * focused and within complexity limits.
 */
class FluentFormsOptionsExtractor
{
    /**
     * Extract field options for an option-based Fluent Forms element.
     *
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $attributes
     * @return array<int, array<string, mixed>>
     */
    public static function extract(array $settings, array $attributes): array
    {
        $rawOptions = self::resolveRawOptions($settings, $attributes);
        $selectedValues = self::resolveSelectedValues($attributes);

        $options = [];
        foreach ($rawOptions as $rawOption) {
            $option = self::buildOption($rawOption, count($options) + 1, $selectedValues);
            if ($option !== null) {
                $options[] = $option;
            }
        }

        return $options;
    }

    /**
     * Resolve the raw options list from settings or attributes.
     *
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $attributes
     * @return array<int, mixed>
     */
    private static function resolveRawOptions(array $settings, array $attributes): array
    {
        $advanced = $settings['advanced_options'] ?? null;
        if (is_array($advanced) && !empty($advanced)) {
            return array_values($advanced);
        }

        $attributeOptions = $attributes['options'] ?? null;
        if (is_array($attributeOptions) && !empty($attributeOptions)) {
            return self::normalizeAttributeOptions($attributeOptions);
        }

        return [];
    }

    /**
     * Resolve the pre-selected option values of a Fluent Forms element.
     *
     * Fluent Forms keeps the selection on the field itself rather than on the
     * options: a list for multi-value elements (checkbox, multi-select) and a
     * single scalar for radio/select.
     *
     * @param array<string, mixed> $attributes
     * @return array<int, string>
     */
    private static function resolveSelectedValues(array $attributes): array
    {
        $rawValues = $attributes['value'] ?? null;
        if (!is_array($rawValues)) {
            $rawValues = [$rawValues];
        }

        $selectedValues = [];
        foreach ($rawValues as $rawValue) {
            $value = self::scalarText($rawValue);
            if ($value !== '') {
                $selectedValues[] = $value;
            }
        }

        return $selectedValues;
    }

    /**
     * Build a single option array from a raw Fluent Forms option.
     *
     * @param mixed $rawOption
     * @param int $position
     * @param array<int, string> $selectedValues
     * @return array<string, mixed>|null
     */
    private static function buildOption($rawOption, int $position, array $selectedValues): ?array
    {
        if (!is_array($rawOption)) {
            return null;
        }

        $label = self::scalarText($rawOption['label'] ?? '');
        $value = array_key_exists('value', $rawOption) ? self::scalarText($rawOption['value']) : $label;
        if ($label === '' && $value === '') {
            return null;
        }

        $resolvedValue = $value !== '' ? $value : $label;

        return [
            'id'        => $position,
            'label'     => $label !== '' ? $label : $value,
            'value'     => $resolvedValue,
            'isDefault' => !empty($rawOption['default']) || in_array($resolvedValue, $selectedValues, true),
            'position'  => $position,
        ];
    }

    /**
     * Normalize a value=>label options map into a list of option arrays.
     *
     * @param array<string|int, mixed> $options
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeAttributeOptions(array $options): array
    {
        $normalized = [];
        foreach ($options as $value => $label) {
            $normalized[] = ['label' => $label, 'value' => $value];
        }

        return $normalized;
    }

    /**
     * Sanitize a value to string, discarding non-scalars.
     *
     * Fluent Forms stores multi-value data (such as checkbox defaults) as arrays,
     * so casting those to string would emit a PHP warning.
     *
     * @param mixed $value
     * @return string
     */
    private static function scalarText($value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return sanitize_text_field((string) $value);
    }
}
