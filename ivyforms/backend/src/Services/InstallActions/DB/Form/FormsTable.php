<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\DB\Form;

use IvyForms\Services\InstallActions\DB\AbstractDatabaseTable;
use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Class FormsTable
 *
 * @package IvyForms\Services\InstallActions\DB\Form
 */
class FormsTable extends AbstractDatabaseTable
{
    public const TABLE = 'forms';

    /**
     * @return string
     * @throws InvalidArgumentException
     *
     */
    public static function buildTable(): string
    {
        $table = self::getTableName();

        return "CREATE TABLE $table (
                   `id` INT(11) NOT NULL AUTO_INCREMENT,
                   `name` VARCHAR(255) NULL,
                   `description` VARCHAR(255) NULL,
                   `formType` VARCHAR(32) NOT NULL DEFAULT 'classic',
                   `author` VARCHAR(255) NULL,
                   `starred` INT(1) NULL,
                   `published` INT(1) NULL,
                   `dateCreated` DATETIME NULL,
                   `dateEdited` DATETIME NULL,
                   `settings` TEXT NOT NULL,
                   `integrationSettings` TEXT NOT NULL,
                   `styleSettings` TEXT NULL,
                    PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
