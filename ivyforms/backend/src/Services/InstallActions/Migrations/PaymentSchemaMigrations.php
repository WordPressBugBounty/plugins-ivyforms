<?php

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;

/**
 * Groups payment-table migrations so activation stays under PHPMD CBO.
 */
class PaymentSchemaMigrations
{
    /**
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public static function run(): void
    {
        EnsurePaymentsTable::run();
        EnsureOrdersTable::run();
        EnsureUniquePaymentTransactionId::run();
        AddCustomerNameToPayments::run();
        AddPaymentStatusAndNullableEntryId::run();
        if (!AddProviderMetaToPayments::run()) {
            throw new QueryExecutionException('Failed to add providerMeta to payments.');
        }
    }
}
