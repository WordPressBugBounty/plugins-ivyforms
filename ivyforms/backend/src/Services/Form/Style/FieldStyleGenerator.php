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
 * Class FieldStyleGenerator
 *
 * Generates CSS for form fields
 *
 * @package IvyForms\Services\Form\StyleHelpers
 */
class FieldStyleGenerator implements StyleGeneratorInterface
{
    /**
     * Generate field styles
     *
     * @param CssBuilder $css
     * @param StyleContext $context
     * @return void
     */
    public function generate(CssBuilder $css, StyleContext $context): void
    {
        $baseStylesApplier = new FieldBaseStylesApplier();
        $stateStylesApplier = new FieldStateStylesApplier();

        $baseStylesApplier->apply(
            $css,
            $context->getBaseSelector(),
            $context->getActiveColors(),
            $context->getActiveStyles()
        );

        $stateStylesApplier->apply(
            $css,
            $context->getBaseSelector(),
            $context->getStyleSettings()
        );
    }
}
