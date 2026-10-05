<?php

namespace IvyForms\Repository\Payment;

use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Entity\Payment\Order;
use IvyForms\Factory\Payment\OrderFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Services\Translations\BackendStrings;

class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    public const FACTORY = OrderFactory::class;

    /**
     * @param Order $entity
     * @return int
     * @throws QueryExecutionException
     */
    public function add($entity): int
    {
        $data = $entity->toArray();

        $result = $this->wpdb->insert(
            $this->table,
            [
                'entryId'          => $data['entryId'],
                'squareOrderId'    => $data['squareOrderId'],
                'status'           => $data['status'],
                'squareCustomerId' => $data['squareCustomerId'],
                'amount'           => $data['amount'],
                'currency'         => $data['currency'],
                'dateCreated'      => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s', '%d', '%s', '%s']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        return (int) $this->wpdb->insert_id;
    }

    public function getBySquareOrderId(string $squareOrderId): ?Order
    {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                $this->selectQuery() . " WHERE {$this->table}.squareOrderId = %s LIMIT 1",
                $squareOrderId
            ),
            ARRAY_A
        );

        if (!$row) {
            return null;
        }

        return OrderFactory::create($row);
    }

    public function updateStatus(string $squareOrderId, string $status): bool
    {
        $result = $this->wpdb->update(
            $this->table,
            ['status' => $status],
            ['squareOrderId' => $squareOrderId],
            ['%s'],
            ['%s']
        );

        return $result !== false;
    }

    public function selectQuery(): string
    {
        return "SELECT {$this->table}.* FROM {$this->table}";
    }

    /**
     * @return string[]
     */
    protected function getAllowedForeignColumns(): array
    {
        return ['entryId'];
    }

    protected function getDateColumn(): string
    {
        return 'dateCreated';
    }
}
