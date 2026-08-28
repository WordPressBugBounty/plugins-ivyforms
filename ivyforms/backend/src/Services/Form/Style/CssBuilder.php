<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Form\Style;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Class CssBuilder
 *
 * Helper for building CSS rules
 *
 * @package IvyForms\Services\Form\StyleHelpers
 */
class CssBuilder
{
    /**
     * @var string[]
     */
    private array $css = [];

    /**
     * Add a CSS rule block
     *
     * @param string|string[] $selectors
     * @param string[] $properties
     * @return void
     */
    public function addRule($selectors, array $properties): void
    {
        if (empty($properties)) {
            return;
        }

        $selectorString = is_array($selectors) ? implode(',', $selectors) : $selectors;
        $this->css[] = $selectorString . ' {';
        $this->css = array_merge($this->css, $properties);
        $this->css[] = '}';
    }

    /**
     * Add raw CSS lines
     *
     * @param string[] $lines
     * @return void
     */
    public function addLines(array $lines): void
    {
        $this->css = array_merge($this->css, $lines);
    }

    /**
     * Get the generated CSS
     *
     * @return string
     */
    public function build(): string
    {
        return implode("\n", $this->css);
    }
}
