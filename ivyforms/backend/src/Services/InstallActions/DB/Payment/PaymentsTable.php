<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\DB\Payment;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\AbstractDatabaseTable;

/**
 * Payments table for Square and future payment providers.
 */
class PaymentsTable extends AbstractDatabaseTable
{
    public const TABLE = 'payments';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function buildTable(): string
    {
        $table = self::getTableName();

        return "CREATE TABLE $table (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `entryId` INT(11) NULL,
                    `formId` INT(11) NOT NULL,
                    `transactionId` VARCHAR(255) NOT NULL,
                    `paymentMethod` VARCHAR(50) NULL DEFAULT 'square',
                    `customerName` VARCHAR(255) NULL,
                    `amount` INT(11) NOT NULL,
                    `currency` VARCHAR(3) NOT NULL,
                    `squareReceiptUrl` VARCHAR(500) NULL,
                    `status` VARCHAR(50) NOT NULL DEFAULT 'succeeded',
                    `providerMeta` TEXT NULL,
                    `dateCreated` DATETIME NULL,
                    PRIMARY KEY (`id`),
                    KEY `entryId` (`entryId`),
                    KEY `formId` (`formId`),
                    KEY `status` (`status`),
                    UNIQUE KEY `transactionId` (`transactionId`)
                ) DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
