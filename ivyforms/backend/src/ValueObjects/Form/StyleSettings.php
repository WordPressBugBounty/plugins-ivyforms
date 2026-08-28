<?php

namespace IvyForms\ValueObjects\Form;

// phpcs:disable PSR1.Files.SideEffects
use IvyForms\Services\Template\TemplateDefaults;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Encapsulates form style settings (quick/advanced mode, themes, classes).
 *
 * @property array<string, mixed> $settings
 */
final class StyleSettings
{
    /**
     * @var array<string, mixed>
     */
    private array $settings = [];

    /**
     * @param array<string, mixed>|null $settings Parsed style settings or null.
     */
    public function __construct($settings = null)
    {
        $defaults = TemplateDefaults::getDefaultFormStylesSafe();

        if (!is_array($settings) || !array_key_exists('stylesEnabled', $settings)) {
            $this->settings = $defaults;
            return;
        }

        $this->settings = array_replace_recursive($defaults, $settings);
    }

    /**
     * Full style settings tree (non-null after construction).
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        return $this->settings;
    }

    /**
     * Export for persistence / array merges (same key as API and DB column).
     *
     * @return array{styleSettings: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'styleSettings' => $this->settings,
        ];
    }
}
