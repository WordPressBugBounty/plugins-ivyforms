<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\DB\Field;

use IvyForms\Services\InstallActions\DB\AbstractDatabaseTable;
use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Class FieldsTable
 *
 * @package IvyForms\Services\InstallActions\DB\Field
 */
class FieldsTable extends AbstractDatabaseTable
{
    public const TABLE = 'fields';

    /**
     * @return string
     * @throws InvalidArgumentException
     *
     */
    public static function buildTable(): string
    {
        $table = self::getTableName();
        // TODO: create enum for type ?
        return "CREATE TABLE $table (
                   `id` INT(11) NOT NULL AUTO_INCREMENT,
                   `fieldIndex` INT(11) NULL,
                   `formId` INT(11) NULL,
                   `type` VARCHAR(50) NULL, 
                   `label` VARCHAR(255) NULL,
                   `required` INT(1) NULL,
                   `defaultValue` TEXT NULL,
                   `placeholder` VARCHAR(255) NULL,
                   `position` INT(11) NULL,
                   `rowIndex` INT(11) DEFAULT 0,
                   `columnIndex` INT(11) DEFAULT 0,
                   `width` INT(11) DEFAULT 100,
                   `settings` TEXT NOT NULL,
                   `parentId` INT(11) NULL,
                   `pageId` VARCHAR(255) NULL,
                   `dateCreated` DATETIME NULL,
                    PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
