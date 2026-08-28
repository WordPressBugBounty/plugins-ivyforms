<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Convert a Fluent Forms JSON export into the IvyForms import payload structure.
 *
 * A Fluent Forms export may contain multiple forms. Unsupported elements
 * (submit buttons, section breaks, payment fields, etc.) and platform-specific
 * settings are skipped gracefully so the import never fails because of them.
 */
class FluentFormsImportConverter
{
    /**
     * Convert a raw Fluent Forms export into an IvyForms import payload.
     *
     * @param array<string|int, mixed> $rawData
     * @return array<string, mixed>
     */
    public static function convert(array $rawData): array
    {
        $forms = [];
        foreach (self::normalizeToList($rawData) as $formRaw) {
            if (is_array($formRaw)) {
                $forms[] = self::convertSingleForm($formRaw);
            }
        }

        return ['forms' => $forms];
    }

    /**
     * Normalize the export into a list of form objects.
     *
     * @param array<string|int, mixed> $rawData
     * @return array<int, mixed>
     */
    private static function normalizeToList(array $rawData): array
    {
        if (isset($rawData['form_fields'])) {
            return [$rawData];
        }

        return array_values($rawData);
    }

    /**
     * Convert a single Fluent Forms form object.
     *
     * @param array<string, mixed> $formRaw
     * @return array<string, mixed>
     */
    private static function convertSingleForm(array $formRaw): array
    {
        $name = sanitize_text_field((string) ($formRaw['title'] ?? ''));
        $published = (($formRaw['status'] ?? '') === 'published');

        return [
            'form' => [
                'name'            => $name,
                'published'       => $published,
                'showTitle'       => true,
                'showDescription' => false,
                'storeEntries'    => true,
            ],
            'fields'        => self::extractFields($formRaw),
            'notifications' => [],
            'confirmation'  => self::extractConfirmation($formRaw),
        ];
    }

    /**
     * Map all Fluent Forms fields into IvyForms fields.
     *
     * @param array<string, mixed> $formRaw
     * @return array<int, array<string, mixed>>
     */
    private static function extractFields(array $formRaw): array
    {
        $formFields = $formRaw['form_fields'] ?? [];
        if (is_string($formFields)) {
            $decoded = json_decode($formFields, true);
            $formFields = is_array($decoded) ? $decoded : [];
        }

        $context = ['id' => 1, 'fieldIndex' => 1, 'position' => 1, 'rowIndex' => 0];
        $fields = [];
        foreach (($formFields['fields'] ?? []) as $rawField) {
            if (is_array($rawField)) {
                FluentFormsFieldMapper::append($rawField, $fields, $context);
            }
        }

        return $fields;
    }

    /**
     * Build the IvyForms confirmation from Fluent Forms form settings.
     *
     * @param array<string, mixed> $formRaw
     * @return array<string, mixed>
     */
    private static function extractConfirmation(array $formRaw): array
    {
        $formSettings = self::findMetaValue($formRaw, 'formSettings');
        $confirmation = is_array($formSettings['confirmation'] ?? null) ? $formSettings['confirmation'] : null;
        if ($confirmation === null) {
            return [];
        }

        $redirectTo = (string) ($confirmation['redirectTo'] ?? 'samePage');

        if ($redirectTo === 'customUrl' && !empty($confirmation['customUrl'])) {
            $url = esc_url_raw((string) $confirmation['customUrl']);
            return self::redirectConfirmation('redirectToCustomUrl', 'url', $url);
        }

        if ($redirectTo === 'customPage' && !empty($confirmation['customPage'])) {
            return self::redirectConfirmation('redirectToPage', 'page', (string) $confirmation['customPage']);
        }

        return self::messageConfirmation($confirmation);
    }

    /**
     * Build a redirect-style confirmation payload.
     *
     * @param string $type
     * @param string $targetKey 'url' or 'page'
     * @param string $targetValue
     * @return array<string, mixed>
     */
    private static function redirectConfirmation(string $type, string $targetKey, string $targetValue): array
    {
        return array_merge(
            [
                'type'     => $type,
                'enabled'  => true,
                'showForm' => false,
                'message'  => '',
                'url'      => '',
                'page'     => '',
            ],
            [$targetKey => $targetValue]
        );
    }

    /**
     * Build a success-message confirmation payload.
     *
     * @param array<string, mixed> $confirmation
     * @return array<string, mixed>
     */
    private static function messageConfirmation(array $confirmation): array
    {
        $message = (string) ($confirmation['messageToShow'] ?? '');
        if ($message === '') {
            return [];
        }

        return [
            'type'     => 'successMessage',
            'enabled'  => true,
            'showForm' => (($confirmation['samePageFormBehavior'] ?? '') !== 'hide_form'),
            'message'  => wp_kses_post($message),
            'url'      => '',
            'page'     => '',
        ];
    }

    /**
     * Find and decode a Fluent Forms meta value by key.
     *
     * @param array<string, mixed> $formRaw
     * @param string $metaKey
     * @return array<string, mixed>
     */
    private static function findMetaValue(array $formRaw, string $metaKey): array
    {
        foreach (['metas', 'form_meta'] as $metaSection) {
            $metas = $formRaw[$metaSection] ?? null;
            if (!is_array($metas)) {
                continue;
            }
            foreach ($metas as $meta) {
                if (is_array($meta) && ($meta['meta_key'] ?? '') === $metaKey) {
                    return self::decodeMetaValue($meta['value'] ?? null);
                }
            }
        }

        return [];
    }

    /**
     * Decode a Fluent Forms meta value into an array.
     *
     * @param mixed $value
     * @return array<string, mixed>
     */
    private static function decodeMetaValue($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
