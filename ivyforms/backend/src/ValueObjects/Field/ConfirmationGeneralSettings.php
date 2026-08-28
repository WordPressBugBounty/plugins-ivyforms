<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\ValueObjects\Field;

/**
 * Class ConfirmationGeneralSettings
 *
 * Encapsulates confirmation field settings (email/password confirmation).
 */
final class ConfirmationGeneralSettings
{
    /**
     * @var bool
     */
    private bool $confirmFieldEnabled;

    /**
     * @var string
     */
    private string $confirmFieldLabel;

    /**
     * @var string
     */
    private string $confirmFieldPlaceholder;

    /**
     * @var bool
     */
    private bool $confirmFieldHideLabel;

    /**
     * @param bool   $confirmFieldEnabled
     * @param string $confirmFieldLabel
     * @param string $confirmFieldPlaceholder
     * @param bool   $confirmFieldHideLabel
     *
     */
    public function __construct(
        bool $confirmFieldEnabled,
        string $confirmFieldLabel,
        string $confirmFieldPlaceholder,
        bool $confirmFieldHideLabel
    ) {
        $this->confirmFieldEnabled = $confirmFieldEnabled;
        $this->confirmFieldLabel = $confirmFieldLabel;
        $this->confirmFieldPlaceholder = $confirmFieldPlaceholder;
        $this->confirmFieldHideLabel = $confirmFieldHideLabel;
    }

    /**
     * Get confirmFieldEnabled.
     */
    public function isConfirmFieldEnabled(): bool
    {
        return $this->confirmFieldEnabled;
    }

    /**
     * Set confirmFieldEnabled.
     */
    public function setConfirmFieldEnabled(bool $enabled): void
    {
        $this->confirmFieldEnabled = $enabled;
    }

    /**
     * Get confirmFieldLabel.
     */
    public function getConfirmFieldLabel(): string
    {
        return $this->confirmFieldLabel;
    }

    /**
     * Set confirmFieldLabel.
     */
    public function setConfirmFieldLabel(string $label): void
    {
        $this->confirmFieldLabel = $label;
    }

    /**
     * Get confirmFieldPlaceholder.
     */
    public function getConfirmFieldPlaceholder(): string
    {
        return $this->confirmFieldPlaceholder;
    }

    /**
     * Set confirmFieldPlaceholder.
     */
    public function setConfirmFieldPlaceholder(string $placeholder): void
    {
        $this->confirmFieldPlaceholder = $placeholder;
    }

    /**
     * Get confirmFieldHideLabel.
     */
    public function isConfirmFieldHideLabel(): bool
    {
        return $this->confirmFieldHideLabel;
    }

    /**
     * Set confirmFieldHideLabel.
     */
    public function setConfirmFieldHideLabel(bool $hideLabel): void
    {
        $this->confirmFieldHideLabel = $hideLabel;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'confirmFieldEnabled'     => $this->isConfirmFieldEnabled(),
            'confirmFieldLabel'       => $this->getConfirmFieldLabel(),
            'confirmFieldPlaceholder' => $this->getConfirmFieldPlaceholder(),
            'confirmFieldHideLabel'   => $this->isConfirmFieldHideLabel(),
        ];
    }
}
