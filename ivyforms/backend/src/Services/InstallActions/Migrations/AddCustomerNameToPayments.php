<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Payment\PaymentsTable;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Adds customerName column to the payments table for the Transactions admin list.
 */
class AddCustomerNameToPayments
{
    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function run(): bool
    {
        global $wpdb;

        $tableName = PaymentsTable::getTableName();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $columnExists = $wpdb->get_results(
            "SHOW COLUMNS FROM `{$tableName}` LIKE 'customerName'"
        );

        if (!empty($columnExists)) {
            return true;
        }

        $alterQuery = "ALTER TABLE `{$tableName}` ADD COLUMN `customerName` VARCHAR(255) NULL AFTER `paymentMethod`";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($alterQuery);

        if ($result === false) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['migration_error_add_column'],
                'customerName',
                $tableName
            ));
            return false;
        }

        return true;
    }
}
