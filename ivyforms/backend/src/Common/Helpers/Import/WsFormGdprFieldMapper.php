<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Detect WS Form consent checkboxes and map them to IvyForms GDPR fields.
 *
 * WS Form has no GDPR field type; consent is modelled as a checkbox with a
 * single required option. IvyForms has a dedicated GDPR field, so that shape
 * is remapped during import.
 */
class WsFormGdprFieldMapper
{
    /**
     * Whether a mapped checkbox should become an IvyForms GDPR field.
     *
     * @param string $wsType
     * @param array<string, mixed> $meta
     * @return bool
     */
    public static function matches(string $wsType, array $meta): bool
    {
        return $wsType === 'checkbox'
            && WsFormOptionsExtractor::extractConsentText($meta) !== null;
    }

    /**
     * Apply GDPR-specific properties to an already-built field payload.
     *
     * @param array<string, mixed> $field
     * @param array<string, mixed> $meta
     * @return void
     */
    public static function apply(array &$field, array $meta): void
    {
        $agreementText = WsFormOptionsExtractor::extractConsentText($meta);
        $field['type'] = 'gdpr';
        $field['required'] = true;
        $field['defaultValue'] = '';
        $field['agreementText'] = $agreementText !== null ? $agreementText : '';
        // WS Form hides the field label for consent checkboxes (label_render off);
        // IvyForms shows label and agreement text separately.
        $field['hideLabel'] = false;
    }
}
