<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository\Amelia;

/**
 * Repository for Amelia booking data linked to IvyForms entries.
 */
class AmeliaRepository
{
    /** @var \wpdb */
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
     * Fetch Amelia bookings linked to an IvyForms entry.
     *
     * @return array<int, array{
     *     appointmentId: int|string|null,
     *     id: int|string|null,
     *     purchaseId: int|string|null
     * }>
     */
    public function getBookingsByEntryId(int $entryId): array
    {
        $prefix = $this->wpdb->prefix;
        $customerBookingsTable = $prefix . 'amelia_customer_bookings';
        $packagesToCustomersTable = $prefix . 'amelia_packages_to_customers';

        if (
            !$this->tableExists($customerBookingsTable)
            || !$this->tableExists($packagesToCustomersTable)
        ) {
            return [];
        }

        $query = $this->wpdb->prepare(
            "SELECT appointmentId, id, NULL AS purchaseId
            FROM {$customerBookingsTable}
            WHERE ivyEntryId = %d
            UNION ALL
            SELECT NULL AS appointmentId, NULL AS id, id AS purchaseId
            FROM {$packagesToCustomersTable}
            WHERE ivyEntryId = %d",
            $entryId,
            $entryId
        );

        $rows = $this->wpdb->get_results($query, ARRAY_A);

        return is_array($rows) ? $rows : [];
    }

    private function tableExists(string $tableName): bool
    {
        $like = $this->wpdb->esc_like($tableName);
        $result = $this->wpdb->get_var(
            $this->wpdb->prepare('SHOW TABLES LIKE %s', $like)
        );

        return $result === $tableName;
    }
}
