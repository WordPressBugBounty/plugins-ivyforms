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
 * Class CornerRadiusResolver
 *
 * Resolves corner radius settings into CSS values
 *
 * @package IvyForms\Services\Form\StyleHelpers
 */
class CornerRadiusResolver
{
    /**
     * Resolve linked or per-corner border radius settings into a CSS value
     *
     * @param mixed $cornerSetting
     * @param string|null $fallbackValue
     * @return string|null
     */
    public static function resolve($cornerSetting, ?string $fallbackValue = null): ?string
    {
        if (empty($cornerSetting)) {
            return $fallbackValue;
        }

        if (is_string($cornerSetting)) {
            return $cornerSetting;
        }

        if (!is_array($cornerSetting)) {
            return $fallbackValue;
        }

        $isLinked = !isset($cornerSetting['isLinked']) || $cornerSetting['isLinked'] !== false;
        $linkedValue = $cornerSetting['value'] ?? $fallbackValue;

        if ($isLinked) {
            return $linkedValue ?: $fallbackValue;
        }

        return self::buildIndividualCorners($cornerSetting, $linkedValue, $fallbackValue);
    }

    /**
     * Build CSS value from individual corner values
     *
     * @param array<string, mixed> $cornerSetting
     * @param string|null $linkedValue
     * @param string|null $fallbackValue
     * @return string|null
     */
    private static function buildIndividualCorners(
        array $cornerSetting,
        ?string $linkedValue,
        ?string $fallbackValue
    ): ?string {
        $topLeft = $cornerSetting['topLeft'] ?? $linkedValue ?? $fallbackValue;
        $topRight = $cornerSetting['topRight'] ?? $topLeft;
        $bottomRight = $cornerSetting['bottomRight'] ?? $topLeft;
        $bottomLeft = $cornerSetting['bottomLeft'] ?? $topLeft;

        if (empty($topLeft) && empty($topRight) && empty($bottomRight) && empty($bottomLeft)) {
            return $fallbackValue;
        }

        return trim($topLeft . ' ' . $topRight . ' ' . $bottomRight . ' ' . $bottomLeft);
    }
}
