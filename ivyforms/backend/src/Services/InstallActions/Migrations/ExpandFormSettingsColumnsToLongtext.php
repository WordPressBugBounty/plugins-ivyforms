<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;

/**
 * Form settings JSON includes every field. TEXT caps that blob at 64 KB,
 * which fails the save once a form reaches about 55 fields.
 */
class ExpandFormSettingsColumnsToLongtext
{
    /**
     * @var array<string, string>
     */
    private const COLUMNS = [
        'settings'            => 'LONGTEXT NOT NULL',
        'integrationSettings' => 'LONGTEXT NOT NULL',
        'styleSettings'       => 'LONGTEXT NULL',
    ];

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function run(): bool
    {
        global $wpdb;

        $tableName = FormsTable::getTableName();

        foreach (self::COLUMNS as $column => $definition) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $columnInfo = $wpdb->get_row("SHOW COLUMNS FROM `{$tableName}` LIKE '{$column}'", ARRAY_A);

            if (!$columnInfo) {
                continue;
            }

            $columnType = strtolower((string) ($columnInfo['Type'] ?? ''));
            if (strpos($columnType, 'longtext') !== false) {
                continue;
            }

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
            $result = $wpdb->query("ALTER TABLE `{$tableName}` MODIFY COLUMN `{$column}` {$definition}");

            if ($result === false) {
                error_log(sprintf(
                    'IvyForms migration error: failed to expand %s on %s',
                    $column,
                    $tableName
                ));
                return false;
            }
        }

        return true;
    }
}
