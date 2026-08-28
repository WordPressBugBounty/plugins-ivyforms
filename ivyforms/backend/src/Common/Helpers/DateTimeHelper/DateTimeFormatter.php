<?php

namespace IvyForms\Common\Helpers\DateTimeHelper;

use DateTimeImmutable;

/**
 * Handles formatting of combined date-time values for display.
 */
class DateTimeFormatter
{
    /**
     * Format stored datetime values for frontend display.
     * Supports both single timestamps and comma-separated range values.
     *
     * @param mixed $value Stored field value
     * @return string Formatted datetime value
     */
    public static function formatDateTimeValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $valueString = trim((string) $value);
        if ($valueString === '') {
            return '';
        }

        if (strpos($valueString, ',') !== false) {
            return self::formatRangeParts($valueString);
        }

        return self::formatSinglePart($valueString);
    }

    /**
     * Format a stored datetime value using the field's configured date/time format.
     * Handles single values and comma-separated range values.
     *
     * @param mixed  $value      Stored field value (timestamp, canonical string, or comma-separated range)
     * @param string $dateFormat JS-style date format token (e.g. MM/DD/YYYY, DD.MM.YYYY, YYYY-MM-DD)
     * @param string $timeFormat Time format key: 'ampm', '24h', 'ampm-s', or '24h-s'
     * @return string
     */
    public static function formatDateTimeWithOptions(
        $value,
        string $dateFormat,
        string $timeFormat
    ): string {
        $valueString = trim((string) $value);
        if ($valueString === '') {
            return '';
        }

        if (strpos($valueString, ',') !== false) {
            return self::buildSubRangesWithOptions($valueString, $dateFormat, $timeFormat);
        }

        return self::formatSingleWithOptions($valueString, $dateFormat, $timeFormat);
    }

    /**
     * Format comma-separated range parts for default display.
     *
     * @param string $valueString Comma-separated value string
     * @return string Formatted range
     */
    private static function formatRangeParts(string $valueString): string
    {
        $parts     = array_values(array_filter(array_map('trim', explode(',', $valueString))));
        $subRanges = [];
        $count     = count($parts);
        for ($i = 0; $i < $count; $i += 2) {
            $start = self::formatSinglePart($parts[$i]);
            if ($start === '') {
                continue;
            }
            $end         = isset($parts[$i + 1]) ? self::formatSinglePart($parts[$i + 1]) : '';
            $subRanges[] = ($end !== '' && $end !== $start) ? $start . ' - ' . $end : $start;
        }
        return implode(', ', $subRanges);
    }

    /**
     * Format comma-separated parts as paired sub-ranges using configured date/time format.
     *
     * @param string $valueString Non-empty comma-separated value string
     * @param string $dateFormat  JS-style date format token
     * @param string $timeFormat  Time format key
     * @return string
     */
    private static function buildSubRangesWithOptions(
        string $valueString,
        string $dateFormat,
        string $timeFormat
    ): string {
        $parts     = array_values(array_filter(array_map('trim', explode(',', $valueString))));
        $subRanges = [];
        $count     = count($parts);
        for ($i = 0; $i < $count; $i += 2) {
            $start = self::formatSingleWithOptions($parts[$i], $dateFormat, $timeFormat);
            if ($start === '') {
                continue;
            }
            $end         = isset($parts[$i + 1])
                ? self::formatSingleWithOptions($parts[$i + 1], $dateFormat, $timeFormat)
                : '';
            $subRanges[] = ($end !== '' && $end !== $start) ? $start . ' - ' . $end : $start;
        }
        return implode(', ', $subRanges);
    }

    /**
     * Format a single timestamp or canonical string for default display.
     *
     * @param string $part Single value
     * @return string Formatted datetime string
     */
    private static function formatSinglePart(string $part): string
    {
        return is_numeric($part)
            ? self::timestampToDateTime((int) $part)
            : self::formatDateTimeSeparator($part);
    }

    /**
     * Format a single datetime part using configured date/time format.
     *
     * @param string $part       Single value (timestamp or canonical string)
     * @param string $dateFormat JS-style date format token
     * @param string $timeFormat Time format key
     * @return string Formatted datetime string
     */
    private static function formatSingleWithOptions(
        string $part,
        string $dateFormat,
        string $timeFormat
    ): string {
        if ($part === '') {
            return '';
        }

        $timestamp = is_numeric($part) ? (int) $part : DateTimeHelper::dateTimeToTimestamp($part);

        if ($timestamp === null) {
            return self::formatDateTimeSeparator($part);
        }

        try {
            $timezone = DateTimeHelper::getWpTimezone();
            $dateTime = (new DateTimeImmutable('@' . $timestamp))->setTimezone($timezone);
        } catch (\Throwable $exception) {
            return self::formatDateTimeSeparator($part);
        }

        return $dateTime->format(self::buildPhpDateTimeFormat($dateFormat, $timeFormat));
    }

    /**
     * Build a combined PHP date-time format string from JS-style tokens and a time format key.
     *
     * @param string $dateFormat JS-style date format (e.g. DD.MM.YYYY)
     * @param string $timeFormat Time format key: 'ampm', '24h', 'ampm-s', or '24h-s'
     * @return string PHP date() compatible format
     */
    private static function buildPhpDateTimeFormat(string $dateFormat, string $timeFormat): string
    {
        $phpDateFormat = str_replace(
            ['YYYY', 'MM', 'DD'],
            ['Y', 'm', 'd'],
            $dateFormat ?: 'Y-m-d'
        );

        $timeFormatMap = [
            'ampm'   => 'g:i A',
            'ampm-s' => 'g:i:s A',
            '24h'    => 'H:i',
            '24h-s'  => 'H:i:s',
        ];

        $phpTimeFormat = $timeFormatMap[$timeFormat] ?? 'H:i';

        return $phpDateFormat . ' ' . $phpTimeFormat;
    }

    /**
     * Format Unix timestamp back to datetime string.
     * Includes seconds when the timestamp has a non-zero seconds component.
     *
     * @param int $timestamp Unix timestamp
     * @return string Formatted datetime string
     */
    private static function timestampToDateTime(int $timestamp): string
    {
        $timezone = DateTimeHelper::getWpTimezone();
        $dateTime = new DateTimeImmutable('@' . $timestamp);
        $dateTime = $dateTime->setTimezone($timezone);

        $format = ((int) $dateTime->format('s') > 0) ? 'Y-m-d H:i:s' : 'Y-m-d H:i';

        return $dateTime->format($format);
    }

    /**
     * Normalize display spacing around the datetime separator.
     *
     * @param string $value Raw datetime string
     * @return string Datetime string with spaced separator
     */
    private static function formatDateTimeSeparator(string $value): string
    {
        return (string) preg_replace('/\s*\|\s*/', ' ', $value);
    }
}
