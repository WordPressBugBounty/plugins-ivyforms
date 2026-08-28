<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;
use IvyForms\Services\Translations\BackendStrings;

class AddFormTypeToForms
{
    /**
     * Add formType column (classic | conversational) for distinguishing form modes.
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function run(): bool
    {
        global $wpdb;

        $tableName = FormsTable::getTableName();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $columnExists = $wpdb->get_results(
            "SHOW COLUMNS FROM `{$tableName}` LIKE 'formType'"
        );

        if (!empty($columnExists)) {
            return true;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $alterQuery = "ALTER TABLE `{$tableName}` "
            . "ADD COLUMN `formType` VARCHAR(32) NOT NULL DEFAULT 'classic' AFTER `description`";
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($alterQuery);

        if ($result === false) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['migration_error_add_column'],
                'formType',
                $tableName
            ));
            return false;
        }

        return true;
    }

    /**
     * Rollback migration (for development purposes)
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function rollback(): bool
    {
        global $wpdb;

        $tableName = FormsTable::getTableName();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $alterQuery = "ALTER TABLE `{$tableName}` DROP COLUMN `formType`";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($alterQuery);

        return $result !== false;
    }
}
