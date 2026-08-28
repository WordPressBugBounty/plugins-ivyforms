<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Extract IvyForms field options from WS Form data grids.
 *
 * Splitting option extraction out of WsFormImportConverter keeps each class
 * focused and within complexity limits.
 */
class WsFormOptionsExtractor
{
    /**
     * Extract field options from a WS Form data grid stored in field meta.
     *
     * @param array<string, mixed> $meta
     * @param string $wsType
     * @return array<int, array<string, mixed>>
     */
    public static function extract(array $meta, string $wsType): array
    {
        $grid = $meta['data_grid_' . $wsType] ?? null;
        if (!is_array($grid)) {
            return [];
        }

        $columns = is_array($grid['columns'] ?? null) ? $grid['columns'] : [];
        [$valueIndex, $labelIndex] = self::resolveColumnIndexes($columns);

        $options = [];
        foreach (self::collectGridRows($grid) as $row) {
            $option = self::buildOption($row, $valueIndex, $labelIndex, count($options) + 1);
            if ($option !== null) {
                $options[] = $option;
            }
        }

        return $options;
    }

    /**
     * Extract consent agreement text when a checkbox is a single required option.
     *
     * WS Form has no GDPR field type; consent is modelled as a checkbox with one
     * required row. Returns null when the grid does not match that shape so the
     * field stays a regular checkbox.
     *
     * @param array<string, mixed> $meta
     * @return string|null
     */
    public static function extractConsentText(array $meta): ?string
    {
        $grid = $meta['data_grid_checkbox'] ?? null;
        if (!is_array($grid)) {
            return null;
        }

        $rows = self::collectGridRows($grid);
        $columns = is_array($grid['columns'] ?? null) ? $grid['columns'] : [];
        [, $labelIndex] = self::resolveColumnIndexes($columns);
        $consentRows = [];

        foreach ($rows as $row) {
            $label = self::extractConsentLabel($row, $labelIndex);
            if ($label !== '') {
                $consentRows[] = ['row' => $row, 'label' => $label];
            }
        }

        if (count($consentRows) !== 1) {
            return null;
        }

        $row = $consentRows[0]['row'];
        $fieldRequired = self::isEnabled($meta['required'] ?? false);
        $rowRequired = self::isEnabled($row['required'] ?? false);
        if (!$fieldRequired && !$rowRequired) {
            return null;
        }

        return $consentRows[0]['label'];
    }

    /**
     * Extract sanitized consent copy from a WS Form grid row.
     *
     * @param array<string, mixed> $row
     * @param int $labelIndex
     * @return string
     */
    private static function extractConsentLabel(array $row, int $labelIndex): string
    {
        $rowData = is_array($row['data'] ?? null) ? $row['data'] : [];
        $label = (string) ($rowData[$labelIndex] ?? $rowData[0] ?? '');

        return wp_kses_post(self::expandWsFormTokens($label));
    }

    /**
     * Normalize WS Form toggle values from saved metadata and JSON exports.
     *
     * @param mixed $value
     * @return bool
     */
    private static function isEnabled($value): bool
    {
        return in_array($value, [true, 1, '1', 'on', 'true'], true);
    }

    /**
     * Expand common WS Form tokens that appear in consent copy.
     *
     * @param string $text
     * @return string
     */
    private static function expandWsFormTokens(string $text): string
    {
        return str_replace(
            ['#blog_name', '#blog_admin_email'],
            [get_bloginfo('name'), get_bloginfo('admin_email')],
            $text
        );
    }

    /**
     * Flatten all rows across every group of a WS Form data grid.
     *
     * @param array<string, mixed> $grid
     * @return array<int, array<string, mixed>>
     */
    private static function collectGridRows(array $grid): array
    {
        $rows = [];
        foreach (($grid['groups'] ?? []) as $optionGroup) {
            if (!is_array($optionGroup)) {
                continue;
            }
            foreach (($optionGroup['rows'] ?? []) as $row) {
                if (is_array($row)) {
                    $rows[] = $row;
                }
            }
        }

        return $rows;
    }

    /**
     * Build a single option array from a WS Form data grid row.
     *
     * @param array<string, mixed> $row
     * @param int|null $valueIndex
     * @param int $labelIndex
     * @param int $position
     * @return array<string, mixed>|null
     */
    private static function buildOption(array $row, ?int $valueIndex, int $labelIndex, int $position): ?array
    {
        $rowData = is_array($row['data'] ?? null) ? $row['data'] : [];
        if (empty($rowData)) {
            return null;
        }

        $label = sanitize_text_field((string) ($rowData[$labelIndex] ?? $rowData[0] ?? ''));
        $value = $label;
        if ($valueIndex !== null && isset($rowData[$valueIndex])) {
            $value = sanitize_text_field((string) $rowData[$valueIndex]);
        }

        if ($label === '' && $value === '') {
            return null;
        }

        return [
            'id'        => $position,
            'label'     => $label !== '' ? $label : $value,
            'value'     => $value !== '' ? $value : $label,
            'isDefault' => !empty($row['default']),
            'position'  => $position,
        ];
    }

    /**
     * Resolve the value/label column indexes for a WS Form data grid.
     *
     * @param array<int, mixed> $columns
     * @return array{0: int|null, 1: int}
     */
    private static function resolveColumnIndexes(array $columns): array
    {
        $valueIndex = null;
        $labelIndex = 0;

        foreach ($columns as $index => $column) {
            if (!is_array($column)) {
                continue;
            }
            $columnId = isset($column['id']) ? (int) $column['id'] : (int) $index;
            $columnLabel = strtolower((string) ($column['label'] ?? ''));
            if ($columnLabel === 'value') {
                $valueIndex = $columnId;
            }
            if ($columnLabel === 'label') {
                $labelIndex = $columnId;
            }
        }

        return [$valueIndex, $labelIndex];
    }
}
