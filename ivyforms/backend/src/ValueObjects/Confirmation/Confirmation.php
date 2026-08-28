<?php

namespace IvyForms\ValueObjects\Confirmation;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class Confirmation
 *
 * @package IvyForms\ValueObjects\Confirmation
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
final class Confirmation
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
     * @var string
     */
    public string $name;
    /**
     * @var string
     */
    public string $type;
    /**
     * @var bool
     */
    public bool $enabled;
    /**
     * @var bool
     */
    public bool $showForm;
    /**
     * @var string
     */
    public string $message;
    /**
     * @var string
     */
    public string $url;
    /**
     * @var string
     */
    public string $page;
    /**
     * @var int
     */
    public int $position;
    /**
     * @var bool
     */
    public bool $isDefault;
    /**
     * @var array<string, mixed>
     */
    public array $smartLogic;

    /**
     * Confirmation constructor.
     *
     * @param int $id
     * @param int $formId
     * @param string $name
     * @param string $type
     * @param bool $enabled
     * @param bool $showForm
     * @param string $message
     * @param string $url
     * @param string $page
     * @param int $position
     * @param bool $isDefault
     * @param array<string, mixed> $smartLogic
     * @throws InvalidArgumentException|ValidationException
     */
    public function __construct(
        int $id,
        int $formId,
        string $name,
        string $type,
        bool $enabled,
        bool $showForm,
        string $message,
        string $url,
        string $page,
        int $position = 0,
        bool $isDefault = false,
        array $smartLogic = []
    ) {
        $this->id         = $this->validateId($id);
        $this->formId     = $this->validateId($formId);
        $this->name       = $this->validateString($name, 255, 'name');
        $this->type       = $this->validateString($type, 255, 'type');
        $this->enabled    = $this->validateBool($enabled);
        $this->showForm   = $this->validateBool($showForm);
        $this->message    = $this->validateString($message, 4294967295, 'message');
        $this->url        = $this->validateString($url, 255, 'url');
        $this->page       = $this->validateString($page, 255, 'page');
        $this->position   = $position >= 0 ? $position : 0;
        $this->isDefault  = $this->validateBool($isDefault);
        $this->smartLogic = $this->validateSmartLogic($smartLogic);
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
     * Get the confirmation ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the confirmation name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the form ID.
     *
     * @return int
     */
    public function getFormId(): int
    {
        return $this->formId;
    }

    /**
     * Get the confirmation type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the confirmation status.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get the show form flag.
     *
     * @return bool
     */
    public function isShowForm(): bool
    {
        return $this->showForm;
    }

    /**
     * Get the confirmation message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the confirmation URL.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get the confirmation page.
     *
     * @return string
     */
    public function getPage(): string
    {
        return $this->page;
    }

    /**
     * Get the confirmation list position.
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Whether this confirmation is the default for the form.
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSmartLogic(): array
    {
        return $this->smartLogic;
    }

    /**
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
     * Convert the confirmation to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'        => $this->getId(),
            'formId'    => $this->getFormId(),
            'name'      => $this->getName(),
            'type'      => $this->getType(),
            'enabled'   => $this->isEnabled(),
            'showForm'  => $this->isShowForm(),
            'message'   => $this->getMessage(),
            'url'       => $this->getUrl(),
            'page'      => $this->getPage(),
            'position'  => $this->getPosition(),
            'isDefault' => $this->isDefault(),
            'smartLogic' => $this->getSmartLogic(),
        ];
    }
}
