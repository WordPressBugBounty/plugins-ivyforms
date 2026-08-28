<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Entity\Notification;

use IvyForms\ValueObjects\Notification\NotificationContact as NotificationContactValueObject;

/**
 * Class NotificationContact
 *
 * Entity wrapper for NotificationContact ValueObject.
 *
 * @package IvyForms\Entity\Notification
 */
class NotificationContact
{
    /**
     * @var NotificationContactValueObject
     */
    private NotificationContactValueObject $notificationContact;

    /**
     * @param NotificationContactValueObject $notificationContact
     */
    public function __construct(NotificationContactValueObject $notificationContact)
    {
        $this->notificationContact = $notificationContact;
    }

    /**
     * Get the notification sender.
     *
     * @return string
     */
    public function getSender(): string
    {
        return $this->notificationContact->getSender();
    }

    /**
     * Set the notification sender.
     *
     * @param string $sender
     */
    public function setSender(string $sender): void
    {
        $this->notificationContact->setSender($sender);
    }

    /**
     * Get the notification receiver.
     *
     * @return string
     */
    public function getReceiver(): string
    {
        return $this->notificationContact->getReceiver();
    }

    /**
     * Set the notification receiver.
     *
     * @param string $receiver
     */
    public function setReceiver(string $receiver): void
    {
        $this->notificationContact->setReceiver($receiver);
    }

    /**
     * Get the notification reply-to address.
     *
     * @return string
     */
    public function getReplyTo(): string
    {
        return $this->notificationContact->getReplyTo();
    }

    /**
     * Set the notification reply-to address.
     *
     * @param string $replyTo
     */
    public function setReplyTo(string $replyTo): void
    {
        $this->notificationContact->setReplyTo($replyTo);
    }

    /**
     * Get the notification CC addresses.
     *
     * @return string
     */
    public function getCc(): string
    {
        return $this->notificationContact->getCc();
    }

    /**
     * Set the notification CC addresses.
     *
     * @param string $cc
     */
    public function setCc(string $cc): void
    {
        $this->notificationContact->setCc($cc);
    }

    /**
     * Get the notification BCC addresses.
     *
     * @return string
     */
    public function getBcc(): string
    {
        return $this->notificationContact->getBcc();
    }

    /**
     * Set the notification BCC addresses.
     *
     * @param string $bcc
     */
    public function setBcc(string $bcc): void
    {
        $this->notificationContact->setBcc($bcc);
    }

    /**
     * Convert contact information to array.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->notificationContact->toArray();
    }
}
