<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\DB\Notification;

use IvyForms\Services\InstallActions\DB\AbstractDatabaseTable;
use IvyForms\Common\Exceptions\InvalidArgumentException;

/**
 * Class NotificationsTable
 *
 * @package IvyForms\Services\InstallActions\DB\Notification
 */
class NotificationsTable extends AbstractDatabaseTable
{
    public const TABLE = 'notifications';

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
                   `formId` INT(11) NULL,
                   `name` VARCHAR(255) NULL,
                   `sender` VARCHAR(255) NULL,
                   `replyTo` VARCHAR(255) NULL,
                   `receiver` VARCHAR(500) NULL,
                   `cc` VARCHAR(500) NULL,
                   `bcc` VARCHAR(500) NULL,
                   `enabled` INT(1) NULL,
                   `subject` VARCHAR(255) NULL,
                   `message` TEXT NOT NULL,
                   `showEmptyFields` INT(1) NULL DEFAULT 0,
                   `smartLogic` TEXT NULL,
                    PRIMARY KEY (`id`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
