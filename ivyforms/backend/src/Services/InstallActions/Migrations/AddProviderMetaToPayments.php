<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Payment\PaymentsTable;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Stores gateway-specific ids (PayPal orderId / attemptId) off the entries table.
 */
class AddProviderMetaToPayments
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
            "SHOW COLUMNS FROM `{$tableName}` LIKE 'providerMeta'"
        );

        if (!empty($columnExists)) {
            return true;
        }

        $alterQuery = "ALTER TABLE `{$tableName}` ADD COLUMN `providerMeta` TEXT NULL AFTER `status`";

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
        $result = $wpdb->query($alterQuery);

        if ($result === false) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['migration_error_add_column'],
                'providerMeta',
                $tableName
            ));
            return false;
        }

        return true;
    }
}
