<?php

namespace IvyForms\Repository\Confirmation;

use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Entity\Confirmation\Confirmation;
use IvyForms\Factory\Confirmation\ConfirmationFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Repository\Confirmation\ConfirmationRepositoryInterface;
use IvyForms\Services\Translations\BackendStrings;

class ConfirmationRepository extends AbstractRepository implements ConfirmationRepositoryInterface
{
    public const FACTORY = ConfirmationFactory::class;

    /**
     * Add confirmation to the database
     *
     * @param Confirmation $entity
     *
     * @return int
     *
     * @throws QueryExecutionException
     */
    public function add($entity): int
    {
        $data = $entity->toArray();

        $result = $this->wpdb->insert(
            $this->table,
            [
                'formId'    => (int) $data['formId'],
                'name'      => $data['name'],
                'type'      => $data['type'],
                'enabled'   => (int) $data['enabled'],
                'showForm'  => (int) $data['showForm'],
                'message'   => $data['message'],
                'url'       => $data['url'],
                'page'      => $data['page'],
                'position'  => (int) $data['position'],
                'isDefault' => (int) $data['isDefault'],
                'smartLogic' => wp_json_encode($data['smartLogic']),
            ],
            ['%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%s']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Update confirmation in the database
     *
     * @param int $id
     * @param Confirmation $entity
     *
     * @return bool
     *
     * @throws QueryExecutionException
     */
    public function update(int $id, $entity): bool
    {
        $data = $entity->toArray();

        $result = $this->wpdb->update(
            $this->table,
            [
                'formId'    => (int) $data['formId'],
                'name'      => $data['name'],
                'type'      => $data['type'],
                'enabled'   => (int) $data['enabled'],
                'showForm'  => (int) $data['showForm'],
                'message'   => $data['message'],
                'url'       => $data['url'],
                'page'      => $data['page'],
                'position'  => (int) $data['position'],
                'isDefault' => (int) $data['isDefault'],
                'smartLogic' => wp_json_encode($data['smartLogic']),
            ],
            ['id' => $id],
            ['%d', '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%d', '%d', '%s'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return $result;
    }

    /**
     * Get all confirmations by form ID ordered by position.
     *
     * @param int $id
     *
     * @return array<Confirmation>
     * @throws QueryExecutionException
     */
    public function getAllById(int $id): array
    {
        $sql = $this->selectQuery()
            . " WHERE $this->table.formId = %d"
            . " ORDER BY $this->table.position ASC, $this->table.id ASC";

        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $id),
            ARRAY_A
        );
        if ($rows === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }
        if (empty($rows)) {
            return [];
        }
        return array_map(function ($row) {
            return call_user_func([static::FACTORY, 'create'], $row);
        }, $rows);
    }

    /**
     * Count confirmations for a form.
     *
     * @param int $formId
     * @return int
     */
    public function countByFormId(int $formId): int
    {
        return (int) $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM $this->table WHERE formId = %d",
                $formId
            )
        );
    }

    /**
     * Get the next position value for a new confirmation on a form.
     *
     * @param int $formId
     * @return int
     */
    public function getNextPosition(int $formId): int
    {
        $max = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT MAX(position) FROM $this->table WHERE formId = %d",
                $formId
            )
        );

        return $max !== null ? ((int) $max) + 1 : 0;
    }

    /**
     * Update positions for confirmations on a form.
     *
     * @param int $formId
     * @param array<int, int> $orderedIds Confirmation IDs in desired order
     * @return void
     */
    public function updatePositions(int $formId, array $orderedIds): void
    {
        foreach ($orderedIds as $position => $confirmationId) {
            $this->wpdb->update(
                $this->table,
                ['position' => (int) $position],
                [
                    'id'     => (int) $confirmationId,
                    'formId' => $formId,
                ],
                ['%d'],
                ['%d', '%d']
            );
        }
    }

    /**
     * Set which confirmation is the default for a form.
     *
     * @param int $formId
     * @param int $defaultConfirmationId
     * @return void
     */
    public function syncDefaultFlag(int $formId, int $defaultConfirmationId): void
    {
        $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE {$this->table} SET isDefault = CASE WHEN id = %d THEN 1 ELSE 0 END WHERE formId = %d",
                $defaultConfirmationId,
                $formId
            )
        );
    }

    /**
     * @return string[]
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'type'];
    }

    /**
     * @return string[]
     */
    protected function getFilterableColumns(): array
    {
        return ['formId'];
    }

    /**
     * @return string[]
     */
    protected function getSortableColumns(): array
    {
        return [
            'id',
            'name',
            'type',
            'enabled',
            'position',
        ];
    }
}
