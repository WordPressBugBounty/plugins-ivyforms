<?php

namespace IvyForms\Factory\GoogleSheets;

use DateTime;
use IvyForms\Entity\GoogleSheets\GoogleSheetIntegration;
use IvyForms\ValueObjects\GoogleSheets\GoogleSheetIntegration as GoogleSheetIntegrationValueObject;

/**
 * Creates Google Sheet integration entities from database rows.
 */
class GoogleSheetIntegrationFactory
{
    /**
     * @param array<string, mixed> $row
     */
    public static function create(array $row): GoogleSheetIntegration
    {
        $valueObject = new GoogleSheetIntegrationValueObject(
            isset($row['id']) ? (int) $row['id'] : null,
            (int) $row['form_id'],
            (string) $row['spreadsheet_id'],
            (string) ($row['spreadsheet_name'] ?? ''),
            (string) $row['worksheet_name'],
            self::parseFieldMapping($row),
            self::parseSmartLogic($row),
            (bool) $row['enabled'],
            !empty($row['last_status']) ? (string) $row['last_status'] : null,
            self::parseDateTime($row, 'last_run_at'),
            self::parseDateTime($row, 'created_at'),
            self::parseDateTime($row, 'updated_at')
        );

        return new GoogleSheetIntegration($valueObject);
    }

    /**
     * @param array<string, mixed> $row
     * @return list<array{column: string, value: string}>
     */
    private static function parseFieldMapping(array $row): array
    {
        $fieldMapping = json_decode($row['field_mapping'] ?? '[]', true);

        if (!is_array($fieldMapping)) {
            return [];
        }

        $normalized = [];
        foreach ($fieldMapping as $item) {
            if (!is_array($item) || empty($item['column'])) {
                continue;
            }

            $normalized[] = [
                'column' => (string) $item['column'],
                'value'  => (string) ($item['value'] ?? ''),
            ];
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function parseSmartLogic(array $row): array
    {
        $smartLogic = json_decode($row['smart_logic'] ?? '[]', true);

        return is_array($smartLogic) ? $smartLogic : [];
    }

    /**
     * @param array<string, mixed> $row
     */
    private static function parseDateTime(array $row, string $field): ?DateTime
    {
        if (empty($row[$field])) {
            return null;
        }

        return new DateTime((string) $row[$field]);
    }
}
