<?php

namespace IvyForms\ValueObjects\Entry;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Helpers\EntryHelper;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class Entry
 *
 * @package IvyForms\ValueObjects\Entry
 */
final class Entry
{
    /**
     * @var int
     */
    public int $id;
    /**
     * @var int
     */
    public int $formId;
    /**
     * @var int|null
     */
    public ?int $userId = null;
    /**
     * @var string
     */
    public string $dateCreated;
    /**
     * @var string|null
     */
    public ?string $dateEdited = null;
    /**
     * @var string
     */
    public string $status;
    /**
     * @var string|null
     */
    public ?string $ipAddress = null;
    /**
     * @var string|null
     */
    public ?string $userAgent = null;
    /**
     * @var string|null
     */
    public ?string $sourceURL = null;
    /**
     * @var bool
     */
    public bool $starred = false;

    /**
     * Entry constructor.
     *
     * @param int $id
     * @param int $formId
     * @param int|null $userId
     * @param string $status
     * @param string|null $ipAddress
     * @param string|null $userAgent
     * @param string|null $sourceURL
     * @throws ValidationException
     */
    public function __construct(
        int $id,
        int $formId,
        ?int $userId,
        string $status,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $sourceURL
    ) {
        $this->id              = $this->validateId($id);
        $this->formId          = $this->validateId($formId);
        $this->userId          = $this->validateUserId($userId);
        $this->dateCreated     = gmdate('Y-m-d H:i:s');
        $this->dateEdited      = gmdate('Y-m-d H:i:s');
        $this->status          = $this->validateString($status, 45, 'status');
        $this->ipAddress       = $this->validateString($ipAddress, 45, 'ipAddress');
        $this->userAgent       = $this->validateString($userAgent, 255, 'userAgent');
        $this->sourceURL       = $this->validateString($sourceURL, 255, 'sourceURL');
        $this->starred         = false;
    }

    /**
     * Get the ID of the entry.
     *
     * @return int The entry ID.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the form ID associated with the entry.
     *
     * @return int The form ID.
     */
    public function getFormId(): int
    {
        return $this->formId;
    }

    /**
     * Get the user ID associated with the entry.
     *
     * @return int|null The user ID or null if not set.
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }
    /**
     * Get the date the entry was created.
     *
     * @return string The creation date in 'Y-m-d H:i:s' format.
     */
    public function getDateCreated(): string
    {
        return $this->dateCreated;
    }

    /**
     * Get the date the entry was last edited.
     *
     * @return string|null The last edited date or null if not set.
     */
    public function getDateEdited(): ?string
    {
        return $this->dateEdited;
    }

    /**
     * Get the status of the entry.
     *
     * @return string The status.
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get the IP address associated with the entry.
     *
     * @return string|null The IP address or null if not set.
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * Get the user agent string associated with the entry.
     *
     * @return string|null The user agent string or null if not set.
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
    /**
     * Get the source URL associated with the entry.
     *
     * @return string|null The source URL or null if not set.
     */
    public function getSourceURL(): ?string
    {
        return $this->sourceURL;
    }
    /**
     * Check if the entry is starred.
     *
     * @return bool True if the entry is starred, false otherwise.
     */
    public function isStarred(): bool
    {
        return $this->starred;
    }

    /**
     * Get the author name for the entry.
     *
     * @return string The author name or 'Guest' if no user is associated.
     */
    public function getAuthor(): string
    {
        if ($this->getUserId() === null) {
            return esc_html__('Guest', 'ivyforms');
        }

        $userData = get_userdata($this->getUserId());
        return $userData ? $userData->user_nicename : esc_html__('Guest', 'ivyforms');
    }

    /**
     * Validates the ID.
     *
     * @param int $id
     *
     * @return int
     * @throws ValidationException
     */
    private function validateId(int $id): int
    {
        if ($id < 0) {
            throw new ValidationException(BackendStrings::getExceptionStrings()['id_positive_integer']);
        }
        return $id;
    }

    /**
     * Validates the user ID (nullable).
     *
     * @param int|null $userId
     *
     * @return int|null
     * @throws ValidationException
     */
    private function validateUserId(?int $userId): ?int
    {
        if ($userId === null) {
            return null;
        }
        if ($userId < 0) {
            throw new ValidationException(BackendStrings::getExceptionStrings()['id_positive_integer']);
        }
        return $userId;
    }
    /**
     * Validates a string value (nullable).
     *
     * @param string|null $value
     * @param int    $maxLength
     * @param string $fieldName
     *
     * @return string|null
     *
     * @throws ValidationException
     */
    private function validateString(?string $value, int $maxLength, string $fieldName): ?string
    {
        if ($value === null) {
            return null;
        }
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
     * Convert the Entry object to an array.
     *
     * @return array<mixed> The array representation of the Entry.
     */
    public function toArray(): array
    {
        return [
            'id'            => $this->getId(),
            'formId'        => $this->getFormId(),
            'userId'        => $this->getUserId(),
            'author'        => $this->getAuthor(),
            'dateCreated'   => $this->getDateCreated(),
            'dateEdited'    => $this->getDateEdited(),
            'status'        => $this->getStatus(),
            'ipAddress'     => $this->getIpAddress(),
            'userAgent'     => EntryHelper::getBrowserName($this->getUserAgent()),
            'sourceURL'     => $this->getSourceURL(),
            'starred'       => $this->isStarred(),
        ];
    }
}
