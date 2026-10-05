<?php

namespace IvyForms\Repository\Payment;

use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Entity\Payment\Payment;
use IvyForms\Factory\Payment\PaymentFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Services\InstallActions\DB\EntryField\EntryFieldsTable;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;
use IvyForms\Services\Translations\BackendStrings;

class PaymentRepository extends AbstractRepository implements PaymentRepositoryInterface
{
    public const FACTORY = PaymentFactory::class;

    /**
     * @param Payment $entity
     * @return int
     * @throws QueryExecutionException
     */
    public function add($entity): int
    {
        $data = $entity->toArray();
        $transactionId = (string) ($data['transactionId'] ?? '');

        if ($transactionId === '' || $this->existsByTransactionId($transactionId)) {
            throw new QueryExecutionException(
                BackendStrings::getPaymentStrings()['duplicate_payment_transaction']
            );
        }

        $entryId = $data['entryId'] ?? null;
        $this->writeDroppingUnknownColumns([
            'entryId' => $entryId !== null && (int) $entryId > 0 ? (int) $entryId : null,
            'formId' => $data['formId'],
            'transactionId' => $transactionId,
            'paymentMethod' => $data['paymentMethod'],
            'customerName' => $data['customerName'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'squareReceiptUrl' => $data['squareReceiptUrl'],
            'status' => $data['status'] ?? 'succeeded',
            'providerMeta' => $data['providerMeta'] ?? null,
            'dateCreated' => current_time('mysql'),
        ], 0);

        return (int) $this->wpdb->insert_id;
    }

    public function getByEntryId(int $entryId): ?Payment
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                $this->selectQuery() . " WHERE {$this->table}.entryId = %d LIMIT 1",
                $entryId
            ),
            ARRAY_A
        );

        return $this->createFromRow($row);
    }

    public function existsByTransactionId(string $transactionId): bool
    {
        if ($transactionId === '') {
            return false;
        }

        $found = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT id FROM {$this->table} WHERE transactionId = %s LIMIT 1",
                $transactionId
            )
        );

        return $found !== null;
    }

    public function getByTransactionId(string $transactionId): ?Payment
    {
        if ($transactionId === '') {
            return null;
        }

        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                $this->selectQuery() . " WHERE {$this->table}.transactionId = %s LIMIT 1",
                $transactionId
            ),
            ARRAY_A
        );

        return $this->createFromRow($row);
    }

    public function findByProviderOrderId(string $orderId): ?Payment
    {
        if ($orderId === '') {
            return null;
        }

        $like = '%' . $this->wpdb->esc_like('"orderId":"' . $orderId . '"') . '%';
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                $this->selectQuery() . " WHERE {$this->table}.providerMeta LIKE %s LIMIT 1",
                $like
            ),
            ARRAY_A
        );

        return $this->createFromRow($row);
    }

    public function updateStatus(int $id, string $status): bool
    {
        if ($id <= 0 || $status === '') {
            return false;
        }

        $result = $this->wpdb->update(
            $this->table,
            ['status' => $status],
            ['id' => $id],
            ['%s'],
            ['%d']
        );

        return $result !== false;
    }

    public function update(Payment $entity): void
    {
        $data = $entity->toArray();
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        $entryId = $data['entryId'] ?? null;
        $this->writeDroppingUnknownColumns(
            [
                'entryId' => $entryId !== null && (int) $entryId > 0 ? (int) $entryId : null,
                'formId' => $data['formId'],
                'transactionId' => $data['transactionId'],
                'paymentMethod' => $data['paymentMethod'],
                'customerName' => $data['customerName'] ?? null,
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'squareReceiptUrl' => $data['squareReceiptUrl'],
                'status' => $data['status'] ?? 'succeeded',
                'providerMeta' => $data['providerMeta'] ?? null,
            ],
            $id
        );
    }

    /**
     * @param array<string, mixed>|null $params
     * @return array<string, mixed>
     */
    public function search(?array $params): array
    {
        $formsTable = FormsTable::getTableName();
        $paymentsTable = $this->table;
        $queryParams = [];
        $whereClauses = $this->buildPaymentWhereClauses($params, $queryParams);

        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($params['perPage'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $orderBy = $this->resolveOrderColumn($params);
        $order = strtoupper((string) ($params['order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT p.*, f.name AS formName, f.integrationSettings AS formIntegrationSettings
                FROM {$paymentsTable} p
                LEFT JOIN {$formsTable} f ON f.id = p.formId
                WHERE {$whereClauses}
                ORDER BY {$orderBy} {$order}
                LIMIT %d OFFSET %d";

        $mainParams = array_merge($queryParams, [$perPage, $offset]);
        $results = $this->wpdb->get_results($this->wpdb->prepare($sql, $mainParams), ARRAY_A);

        if ($results === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_search_entries'] . __CLASS__
            );
        }

        $countSql = "SELECT COUNT(*) FROM {$paymentsTable} p
                     LEFT JOIN {$formsTable} f ON f.id = p.formId
                     WHERE {$whereClauses}";
        $total = empty($queryParams)
            ? (int) $this->wpdb->get_var($countSql)
            : (int) $this->wpdb->get_var($this->wpdb->prepare($countSql, $queryParams));

        return [
            'data' => is_array($results) ? $results : [],
            'meta' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
            ],
        ];
    }

    /**
     * @param array<string, mixed>|null $params
     * @return int
     */
    public function getCount(?array $params): int
    {
        $formsTable = FormsTable::getTableName();
        $paymentsTable = $this->table;
        $queryParams = [];
        $whereClauses = $this->buildPaymentWhereClauses($params, $queryParams);

        $countSql = "SELECT COUNT(*) FROM {$paymentsTable} p
                     LEFT JOIN {$formsTable} f ON f.id = p.formId
                     WHERE {$whereClauses}";

        return empty($queryParams)
            ? (int) $this->wpdb->get_var($countSql)
            : (int) $this->wpdb->get_var($this->wpdb->prepare($countSql, $queryParams));
    }

    /**
     * @param array<string, mixed>|null $params
     * @param array<int|string, mixed> $queryParams
     */
    private function buildPaymentWhereClauses(?array $params, array &$queryParams): string
    {
        $whereClauses = ['1=1'];

        if (!empty($params['search'])) {
            $searchEscaped = '%' . $this->wpdb->esc_like($params['search']) . '%';
            $whereClauses[] = '(p.transactionId LIKE %s OR f.name LIKE %s OR '
                . $this->customerNameSearchSql()
                . ')';
            $queryParams[] = $searchEscaped;
            $queryParams[] = $searchEscaped;
            $queryParams[] = $searchEscaped;
            $queryParams[] = $searchEscaped;
        }

        if (!empty($params['filters']['formId'])) {
            $whereClauses[] = 'p.formId = %d';
            $queryParams[] = (int) $params['filters']['formId'];
        }

        if (!empty($params['filters']['status'])) {
            $whereClauses[] = 'p.status = %s';
            $queryParams[] = sanitize_key((string) $params['filters']['status']);
        }

        if (!empty($params['dateRange']) && !empty($params['dateRange'][0])) {
            $dateRange = $params['dateRange'];
            if (empty($dateRange[1])) {
                $dateRange[1] = $dateRange[0];
            }
            $start = substr((string) $dateRange[0], 0, 10) . ' 00:00:00';
            $end = substr((string) $dateRange[1], 0, 10) . ' 23:59:59';
            $whereClauses[] = 'p.dateCreated >= %s';
            $queryParams[] = $start;
            $whereClauses[] = 'p.dateCreated <= %s';
            $queryParams[] = $end;
        }

        return implode(' AND ', $whereClauses);
    }

    /**
     * Matches persisted customerName or the same entry name/email fields used
     * when that column is empty. Two %s placeholders.
     */
    private function customerNameSearchSql(): string
    {
        $entryFieldsTable = EntryFieldsTable::getTableName();
        $fieldsTable = FieldsTable::getTableName();

        return "(p.customerName LIKE %s
            OR (
                (p.customerName IS NULL OR TRIM(p.customerName) = '')
                AND p.entryId > 0
                AND EXISTS (
                    SELECT 1
                    FROM {$entryFieldsTable} ef
                    INNER JOIN {$fieldsTable} fld ON fld.id = ef.fieldId
                    WHERE ef.entryId = p.entryId
                      AND (fld.parentId IS NULL OR fld.parentId = 0)
                      AND fld.type IN ('name', 'email')
                      AND ef.fieldValue LIKE %s
                )
            ))";
    }

    /**
     * @param array<string, mixed>|null $params
     */
    private function resolveOrderColumn(?array $params): string
    {
        $orderBy = (string) ($params['orderBy'] ?? 'dateCreated');
        $map = [
            'id' => 'p.id',
            'transactionId' => 'p.transactionId',
            'formName' => 'f.name',
            'customerName' => 'p.customerName',
            'amount' => 'p.amount',
            'paymentMethod' => 'p.paymentMethod',
            'status' => 'p.status',
            'dateCreated' => 'p.dateCreated',
        ];

        return $map[$orderBy] ?? 'p.dateCreated';
    }

    public function selectQuery(): string
    {
        return "SELECT {$this->table}.* FROM {$this->table}";
    }

    /**
     * @param array<string, mixed> $row
     */
    private function writeDroppingUnknownColumns(array $row, int $existingId): void
    {
        $formatMap = [
            'entryId' => '%d',
            'formId' => '%d',
            'transactionId' => '%s',
            'paymentMethod' => '%s',
            'customerName' => '%s',
            'amount' => '%d',
            'currency' => '%s',
            'squareReceiptUrl' => '%s',
            'status' => '%s',
            'providerMeta' => '%s',
            'dateCreated' => '%s',
        ];

        while ($row !== []) {
            $formats = [];
            foreach (array_keys($row) as $column) {
                $formats[] = $formatMap[$column] ?? '%s';
            }

            $result = $existingId > 0
                ? $this->wpdb->update($this->table, $row, ['id' => $existingId], $formats, ['%d'])
                : $this->wpdb->insert($this->table, $row, $formats);
            if ($result !== false) {
                return;
            }

            if (
                preg_match("/Unknown column '([^']+)'/", (string) $this->wpdb->last_error, $matches) !== 1
                || !array_key_exists($matches[1], $row)
            ) {
                break;
            }

            unset($row[$matches[1]]);
        }

        $dbError = (string) $this->wpdb->last_error;
        throw new QueryExecutionException(
            BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__ . ': ' . $dbError
        );
    }

    /**
     * @param array<string, mixed>|null $row
     */
    private function createFromRow(?array $row): ?Payment
    {
        return empty($row) ? null : PaymentFactory::create($row);
    }

    /**
     * @return string[]
     */
    protected function getAllowedForeignColumns(): array
    {
        return ['formId', 'entryId'];
    }

    protected function getDateColumn(): string
    {
        return 'dateCreated';
    }
}
