<?php

/**
 * @copyright © Melograno Venture Studio. All rights.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\DB\FieldOptions;

use IvyForms\Services\InstallActions\DB\AbstractDatabaseTable;
use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Class FieldOptionsTable
 *
 * @package IvyForms\Services\InstallActions\DB\FieldOptions
 */
class FieldOptionsTable extends AbstractDatabaseTable
{
    public const TABLE = 'field_options';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function buildTable(): string
    {
        $table = self::getTableName();
        return "CREATE TABLE $table (
                   `id` INT(11) NOT NULL AUTO_INCREMENT,
                   `fieldId` INT(11) NOT NULL,
                   `label` VARCHAR(255) NULL,
                   `value` VARCHAR(255) NULL,
                   `isDefault` TINYINT(1) NULL,
                   `position` INT(11) NULL,
                   PRIMARY KEY (`id`),
                   KEY `fieldId` (`fieldId`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
