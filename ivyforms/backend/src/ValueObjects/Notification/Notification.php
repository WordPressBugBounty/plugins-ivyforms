<?php

namespace IvyForms\ValueObjects\Notification;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class Notification
 *
 * @package IvyForms\ValueObjects\Notification
 */
final class Notification
{
    /**
     * @var int
     */
    public int $id;
    /**
     * @var string
     */
    public string $name;
    /**
     * @var NotificationContact
     */
    public NotificationContact $contact;
    /**
     * @var bool
     */
    public bool $enabled;
    /**
     * @var string
     */
    public string $subject;
    /**
     * @var string
     */
    public string $message;
    /**
     * @var bool
     */
    public bool $showEmptyFields;
    /**
     * @var array<string, mixed>
     */
    public array $smartLogic;
    /**
     * @var int
     */
    public int $formId;

    /**
     * Form constructor.
     *
     * @param int $id
     * @param string $name
     * @param NotificationContact $contact
     * @param bool $enabled
     * @param string $subject
     * @param string $message
     * @param bool $showEmptyFields
     * @param array<string, mixed> $smartLogic
     * @param int $formId
     * @throws InvalidArgumentException|ValidationException
     */
    public function __construct(
        int $id,
        string $name,
        NotificationContact $contact,
        bool $enabled,
        string $subject,
        string $message,
        bool $showEmptyFields,
        array $smartLogic,
        int $formId
    ) {
        $this->id              = $this->validateId($id);
        $this->name            = $this->validateString($name, 255, 'name');
        $this->contact         = $contact;
        $this->enabled         = $this->validateBool($enabled);
        $this->subject         = $this->validateString($subject, 255, 'subject');
        $this->message         = $this->validateString($message, 4294967295, 'message');
        $this->showEmptyFields = $this->validateBool($showEmptyFields);
        $this->smartLogic      = $this->validateSmartLogic($smartLogic);
        $this->formId          = $this->validateId($formId);
    }

    /**
     * Validates the ID.
     *
     * @param int $id
     *
     * @return int
     * @throws InvalidArgumentException
     */
    private function validateId(int $id): int
    {
        if ($id < 0) {
            throw new InvalidArgumentException(BackendStrings::getExceptionStrings()['id_positive_integer']);
        }
        return $id;
    }

    /**
     * Validates a string value.
     *
     * @param string $value
     * @param int $maxLength
     * @param string $fieldName
     *
     * @return string
     *
     * @throws ValidationException
     */
    private function validateString(string $value, int $maxLength, string $fieldName): string
    {
        if (strlen($value) > $maxLength) {
            throw new ValidationException(
                sprintf(
                    /* translators: 1: String value, 2: String max length. */
                    esc_html__('%1$s must be at most %2$d characters.', 'ivyforms'),
                    esc_html($fieldName),
                    $maxLength
                )
            );
        }
        return $value;
    }

    /**
     * Validates a boolean field.
     *
     * @param bool $value
     *
     * @return bool
     */
    private function validateBool(bool $value): bool
    {
        return $value;
    }

    /**
     * Get the notification ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the notification name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the notification sender.
     *
     * @return string
     */
    public function getSender(): string
    {
        return $this->contact->getSender();
    }

    /**
     * Get the notification reply-to address.
     *
     * @return string
     */
    public function getReplyTo(): string
    {
        return $this->contact->getReplyTo();
    }

    /**
     * Get the notification receiver.
     *
     * @return string
     */
    public function getReceiver(): string
    {
        return $this->contact->getReceiver();
    }

    /**
     * Get the notification CC addresses.
     *
     * @return string
     */
    public function getCc(): string
    {
        return $this->contact->getCc();
    }

    /**
     * Get the notification BCC addresses.
     *
     * @return string
     */
    public function getBcc(): string
    {
        return $this->contact->getBcc();
    }

    /**
     * Get the notification status.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get the notification subject.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Get the notification message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the show empty fields status.
     *
     * @return bool
     */
    public function isShowEmptyFields(): bool
    {
        return $this->showEmptyFields;
    }

    /**
     * Get the smart logic status.
     *
     * @return array<string, mixed>
     */
    public function getSmartLogic(): array
    {
        return $this->smartLogic;
    }

    /**
     * Normalize smart logic payload structure.
     *
     * @param array<string, mixed> $smartLogic
     * @return array<string, mixed>
     */
    private function validateSmartLogic(array $smartLogic): array
    {
        $match = $smartLogic['match'] ?? 'any';

        return [
            'enabled' => (bool)($smartLogic['enabled'] ?? false),
            'match' => in_array($match, ['any', 'all'], true) ? $match : 'any',
            'rules' => is_array($smartLogic['rules'] ?? null) ? $smartLogic['rules'] : [],
        ];
    }

    /**
     * Get the form ID associated with the notification.
     *
     * @return int
     */
    public function getFormId(): int
    {
        return $this->formId;
    }

    /**
     * Convert the notification to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'          => $this->getId(),
            'name'        => $this->getName(),
            'sender'      => $this->getSender(),
            'replyTo'     => $this->getReplyTo(),
            'receiver'    => $this->getReceiver(),
            'cc'          => $this->getCc(),
            'bcc'         => $this->getBcc(),
            'enabled'     => $this->isEnabled(),
            'subject'     => $this->getSubject(),
            'message'     => $this->getMessage(),
            'showEmptyFields' => $this->isShowEmptyFields(),
            'smartLogic'      => $this->getSmartLogic(),
            'formId'          => $this->getFormId(),
        ];
    }
}
