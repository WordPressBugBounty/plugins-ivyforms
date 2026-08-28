<?php

namespace IvyForms\ValueObjects\Form;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class FormMetadata
 * Groups form metadata (author, dates, status flags)
 *
 * @package IvyForms\ValueObjects\Form
 */
final class FormMetadata
{
    /**
     * @var string
     */
    public string $author;

    /**
     * @var bool
     */
    public bool $starred;

    /**
     * @var bool
     */
    public bool $published;

    /**
     * @var string
     */
    public string $dateCreated;

    /**
     * @var string
     */
    public string $dateEdited;

    /**
     * FormMetadata constructor.
     *
     * @param string      $author
     * @param bool        $starred
     * @param bool        $published
     * @param string|null $dateCreated
     * @param string|null $dateEdited
     * @throws ValidationException
     */
    public function __construct(
        string $author,
        bool $starred,
        bool $published,
        ?string $dateCreated = null,
        ?string $dateEdited = null
    ) {
        $now = gmdate('Y-m-d H:i:s');

        $this->author       = $this->validateString($author, 1000, 'author');
        $this->starred      = $starred;
        $this->published    = $published;
        $this->dateCreated  = $dateCreated ?? $now;
        $this->dateEdited   = $dateEdited ?? $now;
    }

    /**
     * Validates a string value.
     *
     * @param string $value
     * @param int    $maxLength
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
     * @return string
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * @return bool
     */
    public function isStarred(): bool
    {
        return $this->starred;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * @return string
     */
    public function getDateCreated(): string
    {
        return $this->dateCreated;
    }

    /**
     * @return string
     */
    public function getDateEdited(): string
    {
        return $this->dateEdited;
    }

    /**
     * Convert to array
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'author'      => $this->author,
            'starred'     => $this->starred,
            'published'   => $this->published,
            'dateCreated' => $this->dateCreated,
            'dateEdited'  => $this->dateEdited,
        ];
    }
}
