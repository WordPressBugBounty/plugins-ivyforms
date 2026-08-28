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
 * Class StyleGeneratorFactory
 *
 * Factory for creating style generators.
 * Centralizes the creation and registration of all style generators.
 *
 * @package IvyForms\Common\Helpers\StyleHelpers
 */
class StyleGeneratorFactory
{
    /**
     * Create and return all style generators
     *
     * @return StyleGeneratorInterface[]
     */
    public static function createGenerators(): array
    {
        return [
            new FormWrapperStyleGenerator(),
            new FormTextStyleGenerator(),
            new FieldStyleGenerator(),
            new ButtonStyleGenerator(),
            new CheckboxRadioStyleGenerator(),
        ];
    }
}
