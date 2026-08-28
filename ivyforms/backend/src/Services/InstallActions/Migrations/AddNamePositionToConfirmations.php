<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Confirmation\ConfirmationsTable;
use IvyForms\Services\Translations\BackendStrings;

class AddNamePositionToConfirmations
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

        $tableName = ConfirmationsTable::getTableName();
        $runBackfill = false;

        if (!self::columnExists($wpdb, $tableName, 'name')) {
            if (!self::addColumn($wpdb, $tableName, 'name', 'VARCHAR(255) NULL AFTER `formId`')) {
                return false;
            }
            $runBackfill = true;
        }

        if (!self::columnExists($wpdb, $tableName, 'position')) {
            if (!self::addColumn($wpdb, $tableName, 'position', 'INT(11) NOT NULL DEFAULT 0 AFTER `page`')) {
                return false;
            }
            $runBackfill = true;
        }

        if (!self::columnExists($wpdb, $tableName, 'isDefault')) {
            if (
                !self::addColumn(
                    $wpdb,
                    $tableName,
                    'isDefault',
                    'INT(1) NOT NULL DEFAULT 0 AFTER `position`'
                )
            ) {
                return false;
            }
            $runBackfill = true;
        }

        if ($runBackfill) {
            self::backfillNamePositionAndDefault($wpdb, $tableName);
        }

        return true;
    }

    /**
     * @param \wpdb $wpdb
     * @param string $tableName
     * @param string $column
     * @return bool
     */
    private static function columnExists($wpdb, string $tableName, string $column): bool
    {
        return !empty($wpdb->get_results("SHOW COLUMNS FROM `{$tableName}` LIKE '{$column}'"));
    }

    /**
     * @param \wpdb $wpdb
     * @param string $tableName
     * @param string $column
     * @param string $definition
     * @return bool
     */
    private static function addColumn($wpdb, string $tableName, string $column, string $definition): bool
    {
        $result = $wpdb->query("ALTER TABLE `{$tableName}` ADD COLUMN `{$column}` {$definition}");
        if ($result !== false) {
            return true;
        }

        error_log(sprintf(
            BackendStrings::getExceptionStrings()['migration_error_add_column'],
            $column,
            $tableName
        ));

        return false;
    }

    /**
     * @param \wpdb $wpdb
     * @param string $tableName
     * @return void
     */
    private static function backfillNamePositionAndDefault($wpdb, string $tableName): void
    {
        $strings = BackendStrings::getSettingsFormBuilderStrings();
        $defaultName = $strings['default_confirmation_name'] ?? 'Confirmation';

        $rows = $wpdb->get_results(
            "SELECT id, formId FROM `{$tableName}` ORDER BY formId ASC, id ASC",
            ARRAY_A
        );

        if (empty($rows)) {
            return;
        }

        $positionByForm = [];
        foreach ($rows as $row) {
            $formId = (int) $row['formId'];
            $id = (int) $row['id'];
            if (!isset($positionByForm[$formId])) {
                $positionByForm[$formId] = 0;
            }
            $position = $positionByForm[$formId];
            $wpdb->update(
                $tableName,
                [
                    'name'      => $defaultName,
                    'position'  => $position,
                    'isDefault' => $position === 0 ? 1 : 0,
                ],
                ['id' => $id],
                ['%s', '%d', '%d'],
                ['%d']
            );
            $positionByForm[$formId]++;
        }
    }

    public static function rollback(): bool
    {
        global $wpdb;

        $tableName = ConfirmationsTable::getTableName();

        $wpdb->query("ALTER TABLE `{$tableName}` DROP COLUMN IF EXISTS `isDefault`");
        $wpdb->query("ALTER TABLE `{$tableName}` DROP COLUMN IF EXISTS `position`");
        $wpdb->query("ALTER TABLE `{$tableName}` DROP COLUMN IF EXISTS `name`");

        return true;
    }
}
