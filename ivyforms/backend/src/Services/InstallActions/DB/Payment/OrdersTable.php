<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\DB\Payment;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\AbstractDatabaseTable;

/**
 * Orders table for tracking Square orders linked to form submissions.
 */
class OrdersTable extends AbstractDatabaseTable
{
    public const TABLE = 'orders';

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
                    `squareOrderId` VARCHAR(255) NOT NULL,
                    `status` VARCHAR(50) NOT NULL DEFAULT 'OPEN',
                    `squareCustomerId` VARCHAR(255) NULL,
                    `amount` INT(11) NOT NULL,
                    `currency` VARCHAR(3) NOT NULL,
                    `dateCreated` DATETIME NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `squareOrderId` (`squareOrderId`),
                    KEY `entryId` (`entryId`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
