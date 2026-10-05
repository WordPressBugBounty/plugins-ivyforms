<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Payment\PaymentsTable;

/**
 * Enforces UNIQUE(transactionId) on existing payments tables.
 */
class EnsureUniquePaymentTransactionId
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
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $tableName)) !== $tableName) {
            return true;
        }

        $indexes = self::getTransactionIdIndexes($tableName);
        if ($indexes === null) {
            return false;
        }

        if (self::hasUniqueTransactionIdIndex($indexes)) {
            return true;
        }

        return self::enforceUniqueTransactionId($tableName, $indexes);
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private static function getTransactionIdIndexes(string $tableName): ?array
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $indexes = $wpdb->get_results("SHOW INDEX FROM `{$tableName}` WHERE Column_name = 'transactionId'", ARRAY_A);

        return is_array($indexes) ? $indexes : null;
    }

    /**
     * @param array<int, array<string, mixed>> $indexes
     */
    private static function hasUniqueTransactionIdIndex(array $indexes): bool
    {
        foreach ($indexes as $index) {
            if (isset($index['Non_unique']) && (int) $index['Non_unique'] === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, array<string, mixed>> $indexes
     */
    private static function enforceUniqueTransactionId(string $tableName, array $indexes): bool
    {
        global $wpdb;

        // Keep the earliest row per transactionId so UNIQUE can be applied safely.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query(
            "DELETE p1 FROM `{$tableName}` p1
             INNER JOIN `{$tableName}` p2
               ON p1.transactionId = p2.transactionId AND p1.id > p2.id"
        );

        self::dropNonPrimaryIndexes($tableName, $indexes);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.SchemaChange
        $result = $wpdb->query(
            "ALTER TABLE `{$tableName}` ADD UNIQUE KEY `transactionId` (`transactionId`)"
        );

        if ($result === false) {
            error_log('IvyForms migration error: failed to add UNIQUE transactionId on payments table');

            return false;
        }

        return true;
    }

    /**
     * @param array<int, array<string, mixed>> $indexes
     */
    private static function dropNonPrimaryIndexes(string $tableName, array $indexes): void
    {
        global $wpdb;

        foreach ($indexes as $index) {
            $keyName = (string) ($index['Key_name'] ?? '');
            if ($keyName === '' || $keyName === 'PRIMARY') {
                continue;
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("ALTER TABLE `{$tableName}` DROP INDEX `{$keyName}`");
        }
    }
}
