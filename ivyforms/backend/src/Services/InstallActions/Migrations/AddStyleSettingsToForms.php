<?php

namespace IvyForms\Services\InstallActions\Migrations;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;
use IvyForms\Services\Translations\BackendStrings;

class AddStyleSettingsToForms
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
        $tableName = FormsTable::getTableName();
        // Check if styleSettings column already exists
        $columnExists = $wpdb->get_results(
            "SHOW COLUMNS FROM `{$tableName}` LIKE 'styleSettings'"
        );
        if (!empty($columnExists)) {
            return true; // Already migrated
        }
        // Add styleSettings column
        $alterQuery = "ALTER TABLE `{$tableName}` ADD COLUMN `styleSettings` TEXT NULL AFTER `integrationSettings`";
        $result = $wpdb->query($alterQuery);
        if ($result === false) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['migration_error_add_column'],
                'styleSettings',
                $tableName
            ));
            return false;
        }
        return true;
    }

    public static function rollback(): bool
    {
        global $wpdb;
        $tableName = FormsTable::getTableName();
        $alterQuery = "ALTER TABLE `{$tableName}` DROP COLUMN `styleSettings`";
        $result = $wpdb->query($alterQuery);
        return $result !== false;
    }
}
