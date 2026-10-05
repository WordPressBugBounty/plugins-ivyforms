<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Payment\PaymentsTable;

/**
 * Creates the payments table on existing installs that were not re-activated after the feature shipped.
 */
class EnsurePaymentsTable
{
    /**
     * @return bool
     */
    public static function run(): bool
    {
        global $wpdb;

        try {
            $tableName = PaymentsTable::getTableName();
        } catch (InvalidArgumentException $e) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $tableName)) === $tableName) {
            return true;
        }

        PaymentsTable::init();

        return true;
    }
}
