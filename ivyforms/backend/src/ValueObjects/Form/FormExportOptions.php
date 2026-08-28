<?php

namespace IvyForms\ValueObjects\Form;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Options controlling which sections are included in a form export.
 */
final class FormExportOptions
{
    private bool $includeStyles;
    private bool $includeEntries;

    private function __construct(bool $includeStyles, bool $includeEntries)
    {
        $this->includeStyles = $includeStyles;
        $this->includeEntries = $includeEntries;
    }

    public static function withStyles(): self
    {
        return new self(true, false);
    }

    public static function withoutStyles(): self
    {
        return new self(false, false);
    }

    public static function withEntries(): self
    {
        return new self(true, true);
    }

    public static function withoutEntries(): self
    {
        return new self(true, false);
    }

    public function includesStyles(): bool
    {
        return $this->includeStyles;
    }

    public function includesEntries(): bool
    {
        return $this->includeEntries;
    }

    public function withIncludeEntries(bool $includeEntries): self
    {
        return new self($this->includeStyles, $includeEntries);
    }

    public function withoutEntriesIncluded(): self
    {
        return new self($this->includeStyles, false);
    }

    /**
     * @param mixed $includeStylesParam Request value for includeStyles.
     * @param mixed $includeEntriesParam Request value for includeEntries.
     */
    public static function fromRequestParams($includeStylesParam, $includeEntriesParam): self
    {
        $options = self::fromIncludeStylesParam($includeStylesParam);

        if (filter_var($includeEntriesParam, FILTER_VALIDATE_BOOLEAN)) {
            return $options->withIncludeEntries(true);
        }

        return $options;
    }

    /**
     * @param mixed $includeStylesParam Request value for includeStyles.
     */
    public static function fromIncludeStylesParam($includeStylesParam): self
    {
        if ($includeStylesParam === null) {
            return self::withStyles();
        }

        if (filter_var($includeStylesParam, FILTER_VALIDATE_BOOLEAN)) {
            return self::withStyles();
        }

        return self::withoutStyles();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $includeStyles = filter_var($data['includeStyles'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $includeEntries = filter_var($data['includeEntries'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return new self($includeStyles, $includeEntries);
    }

    /**
     * @return array{includeStyles: bool, includeEntries: bool}
     */
    public function toArray(): array
    {
        return [
            'includeStyles'  => $this->includeStyles,
            'includeEntries' => $this->includeEntries,
        ];
    }
}
