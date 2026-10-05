<?php

namespace IvyForms\Common\Helpers\Import;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Read Gravity Forms submit copy for IvyForms button settings.
 */
class GravityFormsSubmitLabelMapper
{
    /**
     * Gravity stores submit copy on form.button.text. Empty values still show as
     * "Submit" in the Gravity editor, and some exports only keep the text on the
     * submit field label.
     *
     * @param array<string, mixed> $formRaw
     * @return string
     */
    public static function extract(array $formRaw): string
    {
        $button = is_array($formRaw['button'] ?? null) ? $formRaw['button'] : [];
        $label = sanitize_text_field((string) ($button['text'] ?? ''));
        if ($label !== '') {
            return $label;
        }

        foreach (($formRaw['fields'] ?? []) as $rawField) {
            if (!is_array($rawField) || ($rawField['type'] ?? '') !== 'submit') {
                continue;
            }

            $label = sanitize_text_field((string) ($rawField['label'] ?? ''));
            if ($label !== '') {
                return $label;
            }
        }

        return BackendStrings::getCommonStrings()['submit'];
    }
}
