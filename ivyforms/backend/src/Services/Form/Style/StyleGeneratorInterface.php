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
 * Interface StyleGeneratorInterface
 *
 * Contract for all style generators.
 * Generators implement this interface to generate CSS rules for a specific aspect of form styling.
 *
 * @package IvyForms\Common\Helpers\StyleHelpers
 */
interface StyleGeneratorInterface
{
    /**
     * Generate CSS rules for the specific styling aspect
     *
     * @param CssBuilder $css
     * @param StyleContext $context
     * @return void
     */
    public function generate(CssBuilder $css, StyleContext $context): void;
}
