<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Repository\Notification;

use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Entity\Notification\Notification;
use IvyForms\Factory\Notification\NotificationFactory;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Repository\Notification\NotificationRepositoryInterface;
use IvyForms\Services\Translations\BackendStrings;

class NotificationRepository extends AbstractRepository implements NotificationRepositoryInterface
{
    public const FACTORY = NotificationFactory::class;

    /**
     * Add notification to the database
     *
     * @param Notification $entity
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
                'formId'          => (int) $data['formId'],
                'name'            => $data['name'],
                'sender'          => $data['sender'],
                'replyTo'         => $data['replyTo'],
                'receiver'        => $data['receiver'],
                'cc'              => $data['cc'],
                'bcc'             => $data['bcc'],
                'enabled'         => (int) $data['enabled'],
                'subject'         => $data['subject'],
                'message'         => $data['message'],
                'showEmptyFields' => (int) $data['showEmptyFields'],
                'smartLogic'      => wp_json_encode($data['smartLogic']),
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s']
        );

        if ($result === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_to_add_data'] . __CLASS__
            );
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Update form in the database
     *
     * @param int  $id
     * @param Notification $entity
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
                'formId'          => (int) $data['formId'],
                'name'            => $data['name'],
                'sender'          => $data['sender'],
                'replyTo'         => $data['replyTo'],
                'receiver'        => $data['receiver'],
                'cc'              => $data['cc'],
                'bcc'             => $data['bcc'],
                'enabled'         => (int) $data['enabled'],
                'subject'         => $data['subject'],
                'message'         => $data['message'],
                'showEmptyFields' => (int) $data['showEmptyFields'],
                'smartLogic'      => wp_json_encode($data['smartLogic']),
            ],
            ['id' => $id],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s'],
            ['%d']
        );

        if ($result === false) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['unable_to_save_data'] . __CLASS__);
        }

        return true;
    }

    /**
     * Get all notifications by form ID.
     *
     * @param int $id
     *
     * @return array<Notification>
     * @throws QueryExecutionException
     */
    public function getAllById(int $id): array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                $this->selectQuery() . " WHERE $this->table.formId = %d",
                [$id]
            ),
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
     * Get all active notifications by form ID.
     *
     * @param int $id
     *
     * @return array<Notification>
     * @throws QueryExecutionException
     */
    public function getAllActiveById(int $id): array
    {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                $this->selectQuery() . " WHERE $this->table.formId = %d AND $this->table.enabled = 1",
                $id
            ),
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
     * Define searchable columns for notifications
     *
     * @return string[]
     */
    protected function getSearchableColumns(): array
    {
        return ['name', 'receiver', 'subject'];
    }

    /**
     * Define filterable columns for notifications
     *
     * @return string[]
     */
    protected function getFilterableColumns(): array
    {
        return ['formId'];
    }

    /**
     * Define sortable columns for notifications
     *
     * @return string[]
     */
    protected function getSortableColumns(): array
    {
        return [
            'id',
            'name',
            'receiver',
            'subject',
            'enabled'
        ];
    }
}
