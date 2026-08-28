<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\DB\Entry;

use IvyForms\Services\InstallActions\DB\AbstractDatabaseTable;
use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Class EntriesTable
 *
 * @package IvyForms\Services\InstallActions\DB\Form
 */
class EntriesTable extends AbstractDatabaseTable
{
    public const TABLE = 'entries';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function buildTable(): string
    {
        $table = self::getTableName();

        return "CREATE TABLE $table (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `formId` INT(11) NOT NULL,
                    `dateCreated` DATETIME NULL,
                    `dateEdited` DATETIME NULL,
                    `status` VARCHAR(20) NULL DEFAULT 'unread',
                    `userId` INT(11) NULL,
                    `ipAddress` VARCHAR(255) NULL,
                    `userAgent` VARCHAR(255) NULL,
                    `sourceURL` VARCHAR(255) NULL,
                    `starred` INT(11) NULL,
                    PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
