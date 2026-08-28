<?php

namespace IvyForms\Common\Helpers\DateTimeHelper;

use DateTimeImmutable;
use RuntimeException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Helper utilities for time operations.
 */
class TimeHelper
{
    /**
     * Normalize time values to Unix timestamp format.
     *
     * @param int|string|null $value Field value
     * @return string Normalized value as string
     */
    public static function normalizeTimeValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return (string) (self::timeToTimestamp($value) ?? $value);
    }

    /**
     * Format Unix timestamp back to time string (HH:MM).
     *
     * @param int $timestamp Unix timestamp
     * @return string Formatted time string without seconds
     */
    public static function timestampToTime(int $timestamp): string
    {
        $timezone = DateTimeHelper::getWpTimezone();
        $dateTime = new DateTimeImmutable('@' . $timestamp);
        $dateTime = $dateTime->setTimezone($timezone);
        return $dateTime->format('H:i');
    }

    /**
     * Format Unix timestamp back to time string with seconds (HH:MM:SS).
     *
     * @param int $timestamp Unix timestamp
     * @return string Formatted time string with seconds
     */
    public static function timestampToTimeWithSeconds(int $timestamp): string
    {
        $timezone = DateTimeHelper::getWpTimezone();
        $dateTime = new DateTimeImmutable('@' . $timestamp);
        $dateTime = $dateTime->setTimezone($timezone);
        return $dateTime->format('H:i:s');
    }

    /**
     * Convert time string (HH:MM or HH:MM:SS) to Unix timestamp using current date in WP timezone.
     *
     * @param mixed $value Time value (HH:MM, HH:MM:SS, or numeric timestamp)
     * @return int|null Unix timestamp or null if invalid
     */
    private static function timeToTimestamp($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $timeString = trim((string) $value);
        return self::parseTimeStringToTimestamp($timeString);
    }

    /**
     * Parse time string (HH:MM or HH:MM:SS) to Unix timestamp.
     *
     * @param string $timeString Time string to parse
     * @return int|null Unix timestamp or null if invalid
     */
    private static function parseTimeStringToTimestamp(string $timeString): ?int
    {
        if (!preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $timeString, $matches)) {
            return null;
        }

        $hours   = (int) $matches[1];
        $minutes = (int) $matches[2];
        $seconds = isset($matches[3]) ? (int) $matches[3] : 0;

        if (!self::isValidTimeComponents($hours, $minutes, $seconds)) {
            return null;
        }

        return self::createTimestampFromTime($hours, $minutes, $seconds);
    }

    /**
     * Validate time components.
     *
     * @param int $hours Hours (0-23)
     * @param int $minutes Minutes (0-59)
     * @param int $seconds Seconds (0-59)
     * @return bool True if valid
     */
    private static function isValidTimeComponents(int $hours, int $minutes, int $seconds): bool
    {
        return $hours <= 23 && $minutes <= 59 && $seconds <= 59;
    }

    /**
     * Create Unix timestamp from time components using today's date.
     *
     * @param int $hours Hours
     * @param int $minutes Minutes
     * @param int $seconds Seconds
     * @return int Unix timestamp
     * @throws RuntimeException If unable to create timestamp
     */
    private static function createTimestampFromTime(int $hours, int $minutes, int $seconds): int
    {
        try {
            $timezone   = DateTimeHelper::getWpTimezone();
            $today      = new DateTimeImmutable('today', $timezone);
            $dateString = $today->format('Y-m-d');
            $timeString = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
            $dateTime   = new DateTimeImmutable("$dateString $timeString", $timezone);
            return $dateTime->getTimestamp();
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                BackendStrings::getExceptionStrings()['time_conversion_failed'] . ': ' . $exception->getMessage()
            );
        }
    }
}
