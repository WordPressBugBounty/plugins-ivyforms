<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\DB\EntryField;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\AbstractDatabaseTable;

/**
 * Class EntryFieldsTable
 *
 * @package IvyForms\Services\InstallActions\DB\Entry
 */
class EntryFieldsTable extends AbstractDatabaseTable
{
    public const TABLE = 'entry_fields';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function buildTable(): string
    {
        $table = self::getTableName();

        return "CREATE TABLE $table (
                   `id` INT(11) NOT NULL AUTO_INCREMENT,
                   `entryId` INT(11) NOT NULL,
                   `fieldId` INT(11) NOT NULL,
                   `fieldValue` TEXT NOT NULL,
                    PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
