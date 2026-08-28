<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\DB;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class AbstractDatabaseTable
 *
 * @package IvyForms\Services\InstallActions\DB
 */
class AbstractDatabaseTable
{
    public const TABLE = '';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getTableName(): string
    {
        if (!static::TABLE) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['table_name_not_provided']
            );
        }

        global $wpdb;

        return $wpdb->prefix . 'ivyforms_' . static::TABLE;
    }

    /**
     * Create new table in the database
     */
    public static function init(): void
    {
        if (file_exists(ABSPATH . 'wp-admin/includes/upgrade.php')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
        // @phpstan-ignore staticMethod.notFound
        dbDelta(static::buildTable());

        global $wpdb;

        foreach (static::alterTable() as $command) {
            $wpdb->query($wpdb->prepare($command));
        }
    }

    /**
     * Delete table from the database
     *
     * @throws InvalidArgumentException
     */
    public static function delete(): void
    {
        global $wpdb;

        $table = self::getTableName();

        $sql = "DROP TABLE IF EXISTS $table;";
        $wpdb->query($wpdb->prepare($sql));
    }

    /**
     * @return boolean
     */
    public static function isValidTablePrefix(): bool
    {
        global $wpdb;

        return strlen($wpdb->prefix) <= 16;
    }

    /**
     * @return mixed[]
     */
    public static function alterTable(): array
    {
        return [];
    }
}
