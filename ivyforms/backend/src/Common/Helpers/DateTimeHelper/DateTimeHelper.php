<?php

namespace IvyForms\Common\Helpers\DateTimeHelper;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Helper utilities for combined date-time operations.
 */
class DateTimeHelper
{
    /**
     * Get WordPress timezone.
     *
     * @return DateTimeZone
     * @throws RuntimeException If unable to get valid timezone
     */
    public static function getWpTimezone(): DateTimeZone
    {
        if (!function_exists('wp_timezone')) {
            throw new RuntimeException(BackendStrings::getExceptionStrings()['wp_timezone_not_exist']);
        }

        return wp_timezone();
    }

    /**
     * Convert datetime string (YYYY-MM-DD|HH:MM[:SS] or YYYY-MM-DD HH:MM[:SS]) to Unix timestamp.
     *
     * @param mixed $value DateTime value or numeric timestamp
     * @return int|null Unix timestamp or null if invalid
     */
    public static function dateTimeToTimestamp($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $dateTimeString = str_replace('|', ' ', trim((string) $value));

        try {
            $timezone = self::getWpTimezone();
            $expectedFormats = [
                'Y-m-d H:i:s',
                'Y-m-d H:i',
            ];

            foreach ($expectedFormats as $expectedFormat) {
                $timestamp = self::parseTimestampByFormat($dateTimeString, $expectedFormat, $timezone);

                if ($timestamp !== null) {
                    return $timestamp;
                }
            }

            return null;
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * Parse a datetime string against one format and return timestamp when fully valid.
     *
     * @param string       $dateTimeString Datetime string to parse
     * @param string       $expectedFormat Expected datetime format
     * @param DateTimeZone $timezone       Timezone used for parsing
     * @return int|null Unix timestamp or null when parsing fails
     */
    private static function parseTimestampByFormat(
        string $dateTimeString,
        string $expectedFormat,
        DateTimeZone $timezone
    ): ?int {
        $dateTime = DateTimeImmutable::createFromFormat($expectedFormat, $dateTimeString, $timezone);

        if ($dateTime === false || self::hasParsingIssues(DateTimeImmutable::getLastErrors())) {
            return null;
        }

        if ($dateTime->format($expectedFormat) !== $dateTimeString) {
            return null;
        }

        return $dateTime->getTimestamp();
    }

    /**
     * Check if DateTime parsing reported warnings or errors.
     *
     * @param array<string, mixed>|false $errors Parsing errors from DateTimeImmutable::getLastErrors()
     * @return bool
     */
    private static function hasParsingIssues($errors): bool
    {
        if (!is_array($errors)) {
            return false;
        }

        return $errors['warning_count'] > 0 || $errors['error_count'] > 0;
    }

    /**
     * Normalize datetime values to Unix timestamp format.
     * Accepts single values or range arrays and returns the storage string representation.
     *
     * @param mixed $value Field value
     * @return string Normalized value as string
     * @throws InvalidArgumentException If an array value is not a valid two-element start/end range
     */
    public static function normalizeDateTimeValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_array($value)) {
            if (self::isEmptyDateTimeRangeValue($value)) {
                return '';
            }

            return self::normalizeArrayOfDateTimes($value);
        }

        return (string) (self::dateTimeToTimestamp($value) ?? $value);
    }

    /**
     * Whether a two-element range array represents an empty (unset) range.
     *
     * @param mixed $value
     * @return bool
     */
    public static function isEmptyDateTimeRangeValue($value): bool
    {
        if (!is_array($value) || count($value) !== 2) {
            return false;
        }

        $items = array_values($value);

        return self::isEmptyDateTimeRangeItem($items[0] ?? null)
            && self::isEmptyDateTimeRangeItem($items[1] ?? null);
    }

    /**
     * Whether a single range element represents an unset (empty) value.
     * Non-string falsey values (e.g. false) are not treated as empty.
     *
     * @param mixed $item
     * @return bool
     */
    private static function isEmptyDateTimeRangeItem($item): bool
    {
        if ($item === null || $item === '') {
            return true;
        }

        if (!is_string($item)) {
            return false;
        }

        return trim($item) === '';
    }

    /**
     * Format stored datetime values for frontend display.
     * Delegates to DateTimeFormatter.
     *
     * @param mixed $value Stored field value
     * @return string Formatted datetime value
     */
    public static function formatDateTimeValue($value): string
    {
        return DateTimeFormatter::formatDateTimeValue($value);
    }

    /**
     * Format a stored datetime value using the field's configured date/time format.
     * Delegates to DateTimeFormatter.
     *
     * @param mixed  $value      Stored field value (timestamp, canonical string, or comma-separated range)
     * @param string $dateFormat JS-style date format token (e.g. MM/DD/YYYY, DD.MM.YYYY, YYYY-MM-DD)
     * @param string $timeFormat 'ampm', '24h', 'ampm-s', or '24h-s'
     * @return string
     */
    public static function formatDateTimeWithOptions(
        $value,
        string $dateFormat,
        string $timeFormat
    ): string {
        return DateTimeFormatter::formatDateTimeWithOptions($value, $dateFormat, $timeFormat);
    }

    /**
     * Normalize a two-element datetime range array to a comma-separated timestamp string.
     * The array must contain exactly two elements: a valid start and a valid end datetime.
     *
     * @param array<int, mixed> $items Array of exactly two datetime values (start, end)
     * @return string Comma-separated timestamp string
     * @throws InvalidArgumentException If the array does not have exactly two elements or either is invalid
     */
    private static function normalizeArrayOfDateTimes(array $items): string
    {
        $values = array_values($items);

        if (count($values) !== 2) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_datetime_range']
            );
        }

        $start = self::dateTimeToTimestamp($values[0]);
        $end   = self::dateTimeToTimestamp($values[1]);

        if ($start === null || $end === null) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_datetime_range']
            );
        }

        return $start . ', ' . $end;
    }
}
