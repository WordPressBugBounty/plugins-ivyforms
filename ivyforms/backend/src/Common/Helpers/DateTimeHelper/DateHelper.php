<?php

namespace IvyForms\Common\Helpers\DateTimeHelper;

use DateTimeImmutable;

/**
 * Helper utilities for date operations.
 */
class DateHelper
{
    /**
     * Normalize date values to Unix timestamp format.
     *
     * @param int|string|null $value Field value
     * @return string Normalized value as string
     */
    public static function normalizeDateValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return (string) (self::dateToTimestamp($value) ?? $value);
    }

    /**
     * Parse date string to Unix timestamp.
     *
     * @param string $dateString Date string to parse
     * @return int|null Unix timestamp or null if invalid
     */
    public static function parseDateStringToTimestamp(string $dateString): ?int
    {
        try {
            $timezone = DateTimeHelper::getWpTimezone();
            $dateTime = new DateTimeImmutable($dateString, $timezone);

            // Set time to midnight (00:00:00) for date-only values
            $dateTime = $dateTime->setTime(0, 0, 0);

            return $dateTime->getTimestamp();
        } catch (\Throwable $exception) {
            return null;
        }
    }

    /**
     * Format Unix timestamp back to date string (YYYY-MM-DD).
     *
     * @param int $timestamp Unix timestamp
     * @return string Formatted date string
     */
    public static function timestampToDate(int $timestamp): string
    {
        $timezone = DateTimeHelper::getWpTimezone();
        $dateTime = new DateTimeImmutable('@' . $timestamp);
        $dateTime = $dateTime->setTimezone($timezone);
        return $dateTime->format('Y-m-d');
    }

    /**
     * Format Unix timestamp to a custom date format.
     *
     * @param int $timestamp Unix timestamp
     * @param string $format PHP date format string
     * @return string Formatted date string
     */
    public static function timestampToDateFormat(int $timestamp, string $format): string
    {
        $timezone = DateTimeHelper::getWpTimezone();
        $dateTime = new DateTimeImmutable('@' . $timestamp);
        $dateTime = $dateTime->setTimezone($timezone);
        return $dateTime->format($format);
    }

    /**
     * Convert date string (YYYY-MM-DD or various formats) to Unix timestamp.
     *
     * @param mixed $value Date value (YYYY-MM-DD, or numeric timestamp)
     * @return int|null Unix timestamp or null if invalid
     */
    private static function dateToTimestamp($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $dateString = trim((string) $value);
        return self::parseDateStringToTimestamp($dateString);
    }
}
