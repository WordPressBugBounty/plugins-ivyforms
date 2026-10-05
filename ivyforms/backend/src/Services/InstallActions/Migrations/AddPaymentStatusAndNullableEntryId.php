<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Payment\PaymentsTable;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Makes payments.entryId nullable and adds status for failed/orphan rows.
 */
class AddPaymentStatusAndNullableEntryId
{
    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function run(): bool
    {
        global $wpdb;

        $tableName = PaymentsTable::getTableName();
        $ok = true;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $entryIdColumn = $wpdb->get_row("SHOW COLUMNS FROM `{$tableName}` LIKE 'entryId'", ARRAY_A);
        if (is_array($entryIdColumn) && strtoupper((string) ($entryIdColumn['Null'] ?? '')) !== 'YES') {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $result = $wpdb->query(
                "ALTER TABLE `{$tableName}` MODIFY COLUMN `entryId` INT(11) NULL"
            );
            if ($result === false) {
                error_log(sprintf(
                    BackendStrings::getExceptionStrings()['migration_error_add_column'],
                    'entryId (nullable)',
                    $tableName
                ));
                $ok = false;
            }
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $statusExists = $wpdb->get_results("SHOW COLUMNS FROM `{$tableName}` LIKE 'status'");
        if (empty($statusExists)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $alterStatus = "ALTER TABLE `{$tableName}` "
                . "ADD COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'succeeded' AFTER `squareReceiptUrl`";
            $result = $wpdb->query($alterStatus);
            if ($result === false) {
                error_log(sprintf(
                    BackendStrings::getExceptionStrings()['migration_error_add_column'],
                    'status',
                    $tableName
                ));
                $ok = false;
            }
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $statusIndex = $wpdb->get_var("SHOW INDEX FROM `{$tableName}` WHERE Key_name = 'status'");
        if ($statusIndex === null) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
            $result = $wpdb->query("ALTER TABLE `{$tableName}` ADD KEY `status` (`status`)");
            if ($result === false) {
                error_log(sprintf(
                    BackendStrings::getExceptionStrings()['migration_error_add_column'],
                    'status (index)',
                    $tableName
                ));
                $ok = false;
            }
        }

        return $ok;
    }
}
