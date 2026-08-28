<?php

namespace IvyForms\Services\Submission;

use DateTime;

/**
 * Submission Field Type Formatter
 *
 * Applies type-specific value transformations for the 'fields' integration payload.
 * Only actively transforms types that require normalization; all other types are
 * returned unchanged with TODO comments documenting planned future work.
 *
 * Extracted from SubmissionDataFormatterService to keep class complexity manageable.
 *
 * @package IvyForms\Services\Submission
 */
class SubmissionFieldTypeFormatter
{
    /**
     * Format a raw field value for the 'fields' root key of an integration payload.
     *
     * The 'user_inputs' key is NOT affected — it always retains the value exactly
     * as the user entered it in the form.
     *
     * @param mixed       $value Raw submission value
     * @param string      $type  Field type slug (e.g. 'phone', 'date', 'email')
     * @param object|null $field Field object for accessing field-level settings
     * @return mixed Formatted value
     */
    public function format($value, string $type, ?object $field = null)
    {
        if ($type === 'phone') {
            return $this->formatPhoneValue($value);
        }

        if ($type === 'date') {
            return $this->formatDateValue($value, $field);
        }

        // email        — TODO: normalize to lowercase for consistent deduplication
        // url          — TODO: normalize https:// prefix, trim trailing slash
        // number       — TODO: locale-independent decimal formatting
        // time         — TODO: normalize to 24-hour HH:MM:SS for RFC compliance
        // text/textarea — TODO: optionally strip HTML tags or normalize whitespace
        // name/address — compound array fields; returned as-is
        // checkbox, radio, select, multiselect — TODO: trim individual option values
        // rating       — TODO: cast to integer for consistent numeric representation
        return $value;
    }

    /**
     * Strip all whitespace from a phone value.
     *
     * @param mixed $value
     * @return mixed
     */
    private function formatPhoneValue($value)
    {
        if (is_string($value)) {
            return self::stripPhoneWhitespace($value);
        }

        return $value;
    }

    /**
     * Strip all whitespace characters from a phone string.
     *
     * Single canonical implementation shared by both the 'fields' formatter
     * (SubmissionFieldTypeFormatter) and the 'user_inputs' formatter
     * (SubmissionFieldFormatter) so the stripping rule is defined once.
     * e.g. '+345 34345 234' → '+34534345234'
     *
     * @param string $value
     * @return string
     */
    public static function stripPhoneWhitespace(string $value): string
    {
        return preg_replace('/\s+/', '', $value) ?? $value;
    }

    /**
     * Convert a date value (or array of date values) from display format to ISO 8601.
     *
     * @param mixed       $value
     * @param object|null $field
     * @return mixed
     */
    private function formatDateValue($value, ?object $field)
    {
        if (is_string($value) && $value !== '') {
            return $this->convertDateToIso($value, $field);
        }

        if (is_array($value)) {
            // Date-range: format both start and end dates.
            return array_map(function ($dateItem) use ($field) {
                return (is_string($dateItem) && $dateItem !== '')
                    ? $this->convertDateToIso($dateItem, $field)
                    : $dateItem;
            }, $value);
        }

        return $value;
    }

    /**
     * Convert a date string from the field's display format to ISO 8601 (Y-m-d).
     *
     * The display format uses moment.js-style tokens (e.g. 'MM/DD/YYYY'),
     * which are converted to PHP DateTime format tokens before parsing.
     *
     * @param string      $value Raw date string as submitted by the user
     * @param object|null $field Field object used to read the configured dateFormat
     * @return string ISO 8601 date string ('Y-m-d'), or the original value if parsing fails
     */
    private function convertDateToIso(string $value, ?object $field): string
    {
        $displayFormat = $this->resolveDisplayFormat($field);

        // If the stored format is already ISO, nothing to convert.
        if ($displayFormat === 'YYYY-MM-DD' || $displayFormat === 'YYYY-M-D') {
            return $value;
        }

        $phpFormat = $this->momentFormatToPhpFormat($displayFormat);
        $date      = DateTime::createFromFormat($phpFormat, $value);

        // Fall back to the original value if the date cannot be parsed.
        return $date === false ? $value : $date->format('Y-m-d');
    }

    /**
     * Resolve the date display format from the field's general settings.
     *
     * Falls back to the US default ('MM/DD/YYYY') if the field is null,
     * does not expose settings, or has no configured dateFormat.
     *
     * Supported display formats:
     *   MM/DD/YYYY, M/D/YYYY   → m/d/Y  (US)
     *   DD/MM/YYYY, D/M/YYYY   → d/m/Y  (European slash)
     *   DD.MM.YYYY, D.M.YYYY   → d.m.Y  (European dot)
     *   YYYY-MM-DD, YYYY-M-D   → Y-m-d  (ISO — returned unchanged)
     *
     * @param object|null $field
     * @return string Moment.js-style format token (e.g. 'MM/DD/YYYY')
     */
    private function resolveDisplayFormat(?object $field): string
    {
        if ($field === null || !method_exists($field, 'getFieldGeneralSettings')) {
            return 'MM/DD/YYYY';
        }

        $gs = $field->getFieldGeneralSettings();

        if ($gs === null || !method_exists($gs, 'toArray')) {
            return 'MM/DD/YYYY';
        }

        $format = $gs->toArray()['dateFormat'] ?? '';

        return $format !== '' ? $format : 'MM/DD/YYYY';
    }

    /**
     * Convert a moment.js-style date format string to a PHP DateTime format string.
     *
     * Token mapping (longest tokens first to avoid partial substitutions):
     *   YYYY → Y   (4-digit year)
     *   YY   → y   (2-digit year)
     *   MM   → m   (zero-padded month)
     *   M    → n   (month without leading zero)
     *   DD   → d   (zero-padded day)
     *   D    → j   (day without leading zero)
     *
     * @param string $momentFormat e.g. 'MM/DD/YYYY'
     * @return string PHP DateTime format e.g. 'm/d/Y'
     */
    private function momentFormatToPhpFormat(string $momentFormat): string
    {
        // Order matters: replace longer tokens before shorter ones.
        $map = [
            'YYYY' => 'Y',
            'YY'   => 'y',
            'MM'   => 'm',
            'M'    => 'n',
            'DD'   => 'd',
            'D'    => 'j',
        ];

        $phpFormat = $momentFormat;
        foreach ($map as $moment => $php) {
            $phpFormat = str_replace($moment, $php, $phpFormat);
        }

        return $phpFormat;
    }
}
