<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Services\InstallActions\DB\Notification\NotificationsTable;
use IvyForms\Services\Translations\BackendStrings;

class AddCcBccToNotifications
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

        $ccColumnExists = self::columnExists($wpdb, $tableName, 'cc');
        $bccColumnExists = self::columnExists($wpdb, $tableName, 'bcc');

        if ($ccColumnExists === null || $bccColumnExists === null) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['migration_error_add_column'],
                'cc/bcc',
                $tableName
            ));

            return false;
        }

        $columnsToAdd = [];
        if (!$ccColumnExists) {
            $columnsToAdd[] = '`cc` VARCHAR(500) NULL AFTER `receiver`';
        }
        if (!$bccColumnExists) {
            $columnsToAdd[] = '`bcc` VARCHAR(500) NULL AFTER `cc`';
        }

        if ($columnsToAdd === []) {
            return true;
        }

        $alterQuery = "ALTER TABLE `{$tableName}` ADD COLUMN "
            . implode(', ADD COLUMN ', $columnsToAdd);

        $result = $wpdb->query($alterQuery);

        if ($result === false) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['migration_error_add_column'],
                'cc/bcc',
                $tableName
            ));
            return false;
        }

        return true;
    }

    public static function rollback(): bool
    {
        global $wpdb;

        $tableName = NotificationsTable::getTableName();

        $ccExists = self::columnExists($wpdb, $tableName, 'cc');
        if ($ccExists === null) {
            return false;
        }

        if ($ccExists && $wpdb->query("ALTER TABLE `{$tableName}` DROP COLUMN `cc`") === false) {
            return false;
        }

        $bccExists = self::columnExists($wpdb, $tableName, 'bcc');
        if ($bccExists === null) {
            return false;
        }

        if ($bccExists && $wpdb->query("ALTER TABLE `{$tableName}` DROP COLUMN `bcc`") === false) {
            return false;
        }

        return true;
    }

    /**
     * @param \wpdb $wpdb
     * @return bool|null True when present, false when absent, null when lookup failed
     */
    private static function columnExists($wpdb, string $tableName, string $column): ?bool
    {
        $results = $wpdb->get_results(
            "SHOW COLUMNS FROM `{$tableName}` LIKE '{$column}'"
        );

        if ($results === null) {
            return null;
        }

        return !empty($results);
    }
}
