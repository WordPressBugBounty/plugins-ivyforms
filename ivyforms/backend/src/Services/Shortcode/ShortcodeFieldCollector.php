<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Shortcode;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Factory\Field\FieldFactory;

/**
 * Collects field entities from shortcode form payloads, skipping invalid fields.
 */
class ShortcodeFieldCollector
{
    /**
     * @param array<string, array<string, mixed>> $formList
     *
     * @return array<object>
     */
    public static function collect(array $formList): array
    {
        $allFields = [];

        foreach ($formList as $formData) {
            foreach ($formData['fields'] as $fieldData) {
                try {
                    $allFields[] = FieldFactory::create($fieldData);
                } catch (ValidationException $e) {
                    continue;
                }
            }
        }

        return $allFields;
    }
}
