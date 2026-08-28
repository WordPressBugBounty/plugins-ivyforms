<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Notification\NotificationsTable;
use IvyForms\Services\Translations\BackendStrings;

class AddShowEmptyFieldsToNotifications
{
    /**
     * Run the migration
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function run(): bool
    {
        global $wpdb;

        $tableName = NotificationsTable::getTableName();

        // Check if showEmptyFields column already exists
        $columnExists = $wpdb->get_results(
            "SHOW COLUMNS FROM `{$tableName}` LIKE 'showEmptyFields'"
        );

        if (!empty($columnExists)) {
            return true; // Already migrated
        }

        // Add showEmptyFields column after message
        $alterQuery = "ALTER TABLE `{$tableName}` ADD COLUMN `showEmptyFields` INT(1) NULL DEFAULT 0 AFTER `message`";

        $result = $wpdb->query($alterQuery);

        if ($result === false) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['migration_error_add_column'],
                'showEmptyFields',
                $tableName
            ));
            return false;
        }

        return true;
    }

    /**
     * Rollback the migration
     *
     * @return bool
     */
    public static function rollback(): bool
    {
        global $wpdb;

        $tableName = NotificationsTable::getTableName();

        $alterQuery = "ALTER TABLE `{$tableName}` DROP COLUMN `showEmptyFields`";

        $result = $wpdb->query($alterQuery);

        return $result !== false;
    }
}
