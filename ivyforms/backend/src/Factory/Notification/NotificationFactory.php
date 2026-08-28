<?php

namespace IvyForms\Factory\Notification;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Entity\Notification\Notification as NotificationEntity;
use IvyForms\ValueObjects\Notification\Notification;
use IvyForms\ValueObjects\Notification\NotificationContact;

/**
 * Class NotificationFactory
 *
 * @package IvyForms\Factory\Notification
 */
class NotificationFactory
{
    /**
     * Create a NotificationEntity from an array of data.
     *
     * @param array<string, mixed> $data
     *
     * @return NotificationEntity
     * @throws InvalidArgumentException
     */
    public static function create(array $data): NotificationEntity
    {
        $smartLogic = self::normalizeSmartLogic($data['smartLogic'] ?? false);

        $contact = new NotificationContact(
            $data['sender'] ?? '',
            $data['receiver'] ?? '',
            $data['replyTo'] ?? '',
            $data['cc'] ?? '',
            $data['bcc'] ?? ''
        );

        $notificationValueObject = new Notification(
            $data['id'] ?? 0,
            $data['name'] ?? '',
            $contact,
            $data['enabled'] ?? true,
            $data['subject'] ?? '',
            $data['message'] ?? '',
            $data['showEmptyFields'] ?? false,
            $smartLogic,
            $data['formId'] ?? 0
        );

        $notificationEntity = new NotificationEntity($notificationValueObject);

        if (isset($data['id'])) {
            $notificationEntity->setId($data['id']);
        }

        if (isset($data['name'])) {
            $notificationEntity->setName($data['name']);
        }

        if (isset($data['sender'])) {
            $notificationEntity->getNotificationContact()->setSender($data['sender']);
        }

        if (isset($data['replyTo'])) {
            $notificationEntity->getNotificationContact()->setReplyTo($data['replyTo']);
        }

        if (isset($data['receiver'])) {
            $notificationEntity->getNotificationContact()->setReceiver($data['receiver']);
        }

        if (isset($data['cc'])) {
            $notificationEntity->getNotificationContact()->setCc($data['cc']);
        }

        if (isset($data['bcc'])) {
            $notificationEntity->getNotificationContact()->setBcc($data['bcc']);
        }

        if (isset($data['enabled'])) {
            $notificationEntity->setEnabled($data['enabled']);
        }

        if (isset($data['subject'])) {
            $notificationEntity->setSubject($data['subject']);
        }

        if (isset($data['message'])) {
            $notificationEntity->setMessage(wp_unslash($data['message']));
        }

        if (isset($data['showEmptyFields'])) {
            $notificationEntity->setShowEmptyFields($data['showEmptyFields']);
        }

        if (isset($data['smartLogic'])) {
            $notificationEntity->setSmartLogic($smartLogic);
        }

        if (isset($data['formId'])) {
            $notificationEntity->setFormId($data['formId']);
        }

        return $notificationEntity;
    }

    /**
     * Normalize smartLogic from DB/API payload.
     *
     * @param mixed $smartLogic
     * @return array<string, mixed>
     */
    private static function normalizeSmartLogic($smartLogic): array
    {
        if (is_array($smartLogic)) {
            $match = $smartLogic['match'] ?? 'any';

            return [
                'enabled' => (bool)($smartLogic['enabled'] ?? false),
                'match' => in_array($match, ['any', 'all'], true) ? $match : 'any',
                'rules' => is_array($smartLogic['rules'] ?? null) ? $smartLogic['rules'] : [],
            ];
        }

        if (is_string($smartLogic)) {
            $decoded = json_decode($smartLogic, true);
            if (is_array($decoded)) {
                return self::normalizeSmartLogic($decoded);
            }
            return [
                'enabled' => in_array(strtolower($smartLogic), ['1', 'true'], true),
                'match' => 'any',
                'rules' => [],
            ];
        }

        return [
            'enabled' => (bool)$smartLogic,
            'match' => 'any',
            'rules' => [],
        ];
    }
}
