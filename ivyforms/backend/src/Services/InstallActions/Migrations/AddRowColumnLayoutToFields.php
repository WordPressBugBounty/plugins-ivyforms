<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Migration: Add row and column layout columns to fields table
 *
 * Adds rowIndex, columnIndex, and width columns to support grid-based field layout
 * where fields can be organized in rows with up to 5 fields per row.
 *
 * @package IvyForms\Services\InstallActions\Migrations
 */
class AddRowColumnLayoutToFields
{
    /**
     * Run the migration
     *
     * @return bool True if successful, false otherwise
     * @throws InvalidArgumentException
     */
    public static function run(): bool
    {
        global $wpdb;

        $tableName = FieldsTable::getTableName();

        // Check if columns already exist
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rowIndexExists = $wpdb->get_results(
            "SHOW COLUMNS FROM `{$tableName}` LIKE 'rowIndex'"
        );

        if (!empty($rowIndexExists)) {
            return true; // Already migrated
        }

        // Add new columns
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $alterQuery = "ALTER TABLE `{$tableName}` 
            ADD COLUMN `rowIndex` INT(11) NOT NULL DEFAULT 0 AFTER `position`,
            ADD COLUMN `columnIndex` INT(11) NOT NULL DEFAULT 0 AFTER `rowIndex`,
            ADD COLUMN `width` INT(3) NOT NULL DEFAULT 100 AFTER `columnIndex`";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($alterQuery);

        if ($result === false) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['migration_error_add_column'],
                'rowIndex, columnIndex, width',
                $tableName
            ));
            return false;
        }

        // Migrate existing data: convert position-based layout to row-based layout
        // Each existing field gets its own row (rowIndex = position - 1)
        // All fields start at columnIndex = 0, width = 100%
        //
        // We need to update ALL parent fields (parentId IS NULL or parentId = 0)
        // Group by formId and assign sequential row indexes based on position
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $updateQuery = "UPDATE `{$tableName}` 
            SET `rowIndex` = `position` - 1,
                `columnIndex` = 0,
                `width` = 100
            WHERE (`parentId` IS NULL OR `parentId` = 0)";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $wpdb->query($updateQuery);

        return true;
    }

    /**
     * Rollback the migration (for development purposes)
     *
     * @return bool True if successful, false otherwise
     * @throws InvalidArgumentException
     */
    public static function rollback(): bool
    {
        global $wpdb;

        $tableName = FieldsTable::getTableName();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $alterQuery = "ALTER TABLE `{$tableName}` 
            DROP COLUMN `rowIndex`,
            DROP COLUMN `columnIndex`,
            DROP COLUMN `width`";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($alterQuery);

        return $result !== false;
    }
}
