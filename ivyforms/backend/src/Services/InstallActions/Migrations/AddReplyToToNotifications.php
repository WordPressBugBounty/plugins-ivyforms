<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Notification\NotificationsTable;
use IvyForms\Services\Translations\BackendStrings;

class AddReplyToToNotifications
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

        // Check if replyTo column already exists
        $columnExists = $wpdb->get_results(
            "SHOW COLUMNS FROM `{$tableName}` LIKE 'replyTo'"
        );

        if (!empty($columnExists)) {
            return true; // Already migrated
        }

        // Add replyTo column
        $alterQuery = "ALTER TABLE `{$tableName}` ADD COLUMN `replyTo` VARCHAR(255) NULL AFTER `sender`";

        $result = $wpdb->query($alterQuery);

        if ($result === false) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['migration_error_add_column'],
                'replyTo',
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

        $alterQuery = "ALTER TABLE `{$tableName}` DROP COLUMN `replyTo`";

        $result = $wpdb->query($alterQuery);

        return $result !== false;
    }
}
