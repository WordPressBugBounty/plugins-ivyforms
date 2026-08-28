<?php

namespace IvyForms\Services\InstallActions\DB\GoogleSheets;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\AbstractDatabaseTable;

/**
 * Google Sheets integrations table.
 */
class GoogleSheetsTable extends AbstractDatabaseTable
{
    public const TABLE = 'google_sheets';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function buildTable(): string
    {
        $table = self::getTableName();

        return "CREATE TABLE $table (
                   `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                   `form_id` BIGINT(20) UNSIGNED NOT NULL,
                   `spreadsheet_id` VARCHAR(255) NOT NULL,
                   `spreadsheet_name` VARCHAR(255) NOT NULL DEFAULT '',
                   `worksheet_name` VARCHAR(255) NOT NULL DEFAULT '',
                   `field_mapping` LONGTEXT,
                   `smart_logic` LONGTEXT,
                   `enabled` TINYINT(1) NOT NULL DEFAULT 1,
                   `last_status` VARCHAR(255) DEFAULT NULL,
                   `last_run_at` DATETIME DEFAULT NULL,
                   `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                   `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `form_id` (`form_id`),
                    KEY `enabled` (`enabled`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
