<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Entity\Notification;

use IvyForms\ValueObjects\Notification\Notification as NotificationValueObject;
use IvyForms\Entity\Notification\NotificationContact;

/**
 * Class Notification
 *
 * @package IvyForms\Entity\Notification
 */
class Notification
{
    /**
     * @var NotificationValueObject
     */
    private NotificationValueObject $notification;

    /**
     * @var NotificationContact
     */
    private NotificationContact $contact;

    /**
     * Notification constructor.
     *
     * @param NotificationValueObject $notification The notification object.
     */
    public function __construct(NotificationValueObject $notification)
    {
        $this->notification = $notification;
        $this->contact = new NotificationContact($notification->contact);
    }

    /**
     * Get the notification ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->notification->getId();
    }

    /**
     * Set the notification ID.
     *
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->notification->id = $id;
    }

    /**
     * Get the notification name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->notification->getName();
    }

    /**
     * Set the notification name.
     *
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->notification->name = $name;
    }

    /**
     * Get the NotificationContact entity.
     *
     * @return NotificationContact
     */
    public function getNotificationContact(): NotificationContact
    {
        return $this->contact;
    }

    /**
     * Get the notification status.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->notification->isEnabled();
    }

    /**
     * Set the notification status.
     *
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->notification->enabled = $enabled;
    }

    /**
     * Get the notification subject.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->notification->getSubject();
    }

    /**
     * Set the notification subject.
     *
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->notification->subject = $subject;
    }

    /**
     * Get the notification message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->notification->getMessage();
    }

    /**
     * Set the notification message.
     *
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->notification->message = $message;
    }

    /**
     * Get the show empty fields status.
     *
     * @return bool
     */
    public function isShowEmptyFields(): bool
    {
        return $this->notification->isShowEmptyFields();
    }

    /**
     * Set the show empty fields status.
     *
     * @param bool $showEmptyFields
     */
    public function setShowEmptyFields(bool $showEmptyFields): void
    {
        $this->notification->showEmptyFields = $showEmptyFields;
    }

    /**
     * Get the smart logic status.
     *
     * @return array<string, mixed>
     */
    public function getSmartLogic(): array
    {
        return $this->notification->getSmartLogic();
    }

    /**
     * Set the smart logic status.
     *
     * @param array<string, mixed> $smartLogic
     */
    public function setSmartLogic(array $smartLogic): void
    {
        $this->notification->smartLogic = $smartLogic;
    }

    /**
     * Get the form ID.
     *
     * @return int
     */
    public function getFormId(): int
    {
        return $this->notification->getFormId();
    }

    /**
     * Set the form ID.
     *
     * @param int $formId
     */
    public function setFormId(int $formId): void
    {
        $this->notification->formId = $formId;
    }

    /**
     * Convert the notification entity to an array.
     *
     * @return array<string, mixed> The notification entity as an array.
     */
    public function toArray(): array
    {
        return array_merge(
            [
                'id'              => $this->getId(),
                'name'            => $this->getName(),
                'enabled'         => $this->isEnabled(),
                'subject'         => $this->getSubject(),
                'message'         => $this->getMessage(),
                'showEmptyFields' => $this->isShowEmptyFields(),
                'smartLogic'      => $this->getSmartLogic(),
                'formId'          => $this->getFormId(),
            ],
            $this->getNotificationContact()->toArray()
        );
    }
}
