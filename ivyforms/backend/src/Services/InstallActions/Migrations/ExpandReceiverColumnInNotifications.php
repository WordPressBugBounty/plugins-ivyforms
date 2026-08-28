<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Services\InstallActions\DB\Notification\NotificationsTable;

class ExpandReceiverColumnInNotifications
{
    /**
     * Run the migration
     *
     * @return bool
     */
    public static function run(): bool
    {
        global $wpdb;

        $tableName = NotificationsTable::getTableName();
        $column = $wpdb->get_row("SHOW COLUMNS FROM `{$tableName}` LIKE 'receiver'", ARRAY_A);

        if (!$column) {
            return true;
        }

        $columnType = strtolower((string) ($column['Type'] ?? ''));
        if (preg_match('/varchar\((\d+)\)/', $columnType, $matches) && (int) $matches[1] >= 500) {
            return true;
        }

        $result = $wpdb->query(
            "ALTER TABLE `{$tableName}` MODIFY COLUMN `receiver` VARCHAR(500) NULL"
        );

        return $result !== false;
    }
}
