<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Notification\NotificationsTable;

class ConvertNotificationSmartLogicToText
{
    /**
     * @throws InvalidArgumentException
     */
    public static function run(): bool
    {
        global $wpdb;

        $tableName = NotificationsTable::getTableName();
        $column = $wpdb->get_row("SHOW COLUMNS FROM `{$tableName}` LIKE 'smartLogic'", ARRAY_A);

        if (!$column) {
            return true;
        }

        $columnType = strtolower((string)($column['Type'] ?? ''));
        if (strpos($columnType, 'text') !== false || strpos($columnType, 'varchar') !== false) {
            return true;
        }

        $result = $wpdb->query("ALTER TABLE `{$tableName}` MODIFY COLUMN `smartLogic` TEXT NULL");

        return $result !== false;
    }
}
