<?php

namespace IvyForms\ValueObjects\Notification;

use IvyForms\Common\Exceptions\ValidationException;

/**
 * Value Object for Notification Contact Information
 *
 * @package IvyForms\ValueObjects\Notification
 */
final class NotificationContact
{
    private string $sender;
    private string $receiver;
    private string $replyTo;
    private string $cc;
    private string $bcc;

    public function __construct(
        string $sender,
        string $receiver,
        string $replyTo = '',
        string $cc = '',
        string $bcc = ''
    ) {
        $this->sender = $sender;
        $this->receiver = $receiver;
        $this->replyTo = $replyTo;
        $this->cc = $cc;
        $this->bcc = $bcc;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function getReceiver(): string
    {
        return $this->receiver;
    }

    public function getReplyTo(): string
    {
        return $this->replyTo;
    }

    public function setSender(string $sender): void
    {
        $this->sender = $sender;
    }

    public function setReceiver(string $receiver): void
    {
        $this->receiver = $receiver;
    }

    public function setReplyTo(string $replyTo): void
    {
        $this->replyTo = $replyTo;
    }

    public function getCc(): string
    {
        return $this->cc;
    }

    public function setCc(string $cc): void
    {
        $this->cc = $cc;
    }

    public function getBcc(): string
    {
        return $this->bcc;
    }

    public function setBcc(string $bcc): void
    {
        $this->bcc = $bcc;
    }

    /**
     * Convert contact information to array
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'sender'    => $this->sender,
            'receiver'  => $this->receiver,
            'replyTo'   => $this->replyTo,
            'cc'        => $this->cc,
            'bcc'       => $this->bcc,
        ];
    }
}
