<?php

namespace IvyForms\Common\Helpers\Import;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Merge adjacent WS Form text fields into an IvyForms composite name field.
 *
 * WS Form has no name element; forms collect names through separate text fields.
 * IvyForms models a name as a parent `name` field plus `text` sub-fields, so the
 * parts must be detected and merged during import.
 *
 * Detection prefers the explicit `autocomplete` token and falls back to exact
 * label matching. Both signals are deliberately strict: a run is only merged
 * when it holds a first and a last part, so unrelated text fields keep their
 * original shape rather than being collapsed on a guess.
 */
class WsFormNameFieldExtractor
{
    /**
     * HTML autocomplete tokens to IvyForms name sub-field types.
     *
     * @var array<string, string>
     */
    private const AUTOCOMPLETE_MAP = [
        'given-name'      => 'nameField1',
        'family-name'     => 'nameField2',
        'additional-name' => 'nameField3',
    ];

    /**
     * Autocomplete values that carry no field-purpose information.
     *
     * @var array<int, string>
     */
    private const NEUTRAL_AUTOCOMPLETE = ['', 'on', 'off'];

    /**
     * Normalized labels to IvyForms name sub-field types.
     *
     * Matching is exact so labels such as "Company name" or "Product name" are
     * never treated as name parts.
     *
     * @var array<string, string>
     */
    private const LABEL_MAP = [
        'first name'     => 'nameField1',
        'firstname'      => 'nameField1',
        'given name'     => 'nameField1',
        'given names'    => 'nameField1',
        'forename'       => 'nameField1',
        'first'          => 'nameField1',
        'last name'      => 'nameField2',
        'lastname'       => 'nameField2',
        'family name'    => 'nameField2',
        'surname'        => 'nameField2',
        'last'           => 'nameField2',
        'middle name'    => 'nameField3',
        'middlename'     => 'nameField3',
        'middle initial' => 'nameField3',
        'middle'         => 'nameField3',
    ];

    /**
     * Collect the run of adjacent WS Form fields that together form a name.
     *
     * Returns null when the fields starting at $startIndex are not a name group.
     *
     * @param array<int, array<string, mixed>> $wsFields
     * @param int $startIndex
     * @return array<int, array<string, mixed>>|null
     */
    public static function collectRun(array $wsFields, int $startIndex): ?array
    {
        $total = count($wsFields);
        $run = [];
        $usedTypes = [];

        for ($index = $startIndex; $index < $total; $index++) {
            $nameFieldType = self::resolveNameFieldType($wsFields[$index]);
            if ($nameFieldType === null || isset($usedTypes[$nameFieldType])) {
                break;
            }

            $usedTypes[$nameFieldType] = true;
            $run[] = [
                'nameFieldType' => $nameFieldType,
                'wsField'       => $wsFields[$index],
            ];
        }

        if (!isset($usedTypes['nameField1'], $usedTypes['nameField2'])) {
            return null;
        }

        return $run;
    }

    /**
     * Append a composite name field (parent plus sub-fields) for a detected run.
     *
     * @param array<int, array<string, mixed>> $run
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @return void
     */
    public static function append(array $run, array &$fields, array &$context): void
    {
        $parentIndex = $context['fieldIndex']++;
        $fields[] = [
            'id'           => $context['id']++,
            'fieldIndex'   => $parentIndex,
            'type'         => 'name',
            'label'        => self::parentLabel(),
            'placeholder'  => '',
            'required'     => self::runHasRequiredPart($run),
            'defaultValue' => '',
            'parentId'     => null,
            'position'     => $context['position']++,
            'rowIndex'     => $context['rowIndex']++,
            'columnIndex'  => 0,
            'width'        => 100,
        ];

        $subPosition = 1;
        foreach ($run as $part) {
            $fields[] = self::buildSubField($part, $parentIndex, $subPosition, $context);
            $subPosition++;
        }
    }

    /**
     * Whether any part of the run is required.
     *
     * @param array<int, array<string, mixed>> $run
     * @return bool
     */
    private static function runHasRequiredPart(array $run): bool
    {
        foreach ($run as $part) {
            $wsField = is_array($part['wsField'] ?? null) ? $part['wsField'] : [];
            if (self::isRequired($wsField)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build a single name sub-field from a WS Form text field.
     *
     * @param array<string, mixed> $part
     * @param int $parentIndex
     * @param int $subPosition
     * @param array<string, int> $context
     * @return array<string, mixed>
     */
    private static function buildSubField(
        array $part,
        int $parentIndex,
        int $subPosition,
        array &$context
    ): array {
        $wsField = is_array($part['wsField'] ?? null) ? $part['wsField'] : [];
        $meta = is_array($wsField['meta'] ?? null) ? $wsField['meta'] : [];
        $nameFieldType = (string) ($part['nameFieldType'] ?? '');

        return [
            'id'              => $context['id']++,
            'fieldIndex'      => $parentIndex,
            'type'            => 'text',
            'label'           => sanitize_text_field((string) ($wsField['label'] ?? '')),
            'placeholder'     => sanitize_text_field((string) ($meta['placeholder'] ?? '')),
            'description'     => sanitize_text_field((string) ($meta['help'] ?? '')),
            'required'        => self::isRequired($wsField),
            'requiredMessage' => sanitize_text_field((string) ($meta['invalid_feedback'] ?? '')),
            'defaultValue'    => sanitize_text_field((string) ($meta['default_value'] ?? '')),
            'parentId'        => $parentIndex,
            'position'        => $subPosition,
            'settings'        => wp_json_encode(['nameFieldType' => $nameFieldType]) ?: '',
        ];
    }

    /**
     * Resolve the IvyForms name sub-field type of a WS Form field.
     *
     * @param array<string, mixed> $wsField
     * @return string|null
     */
    private static function resolveNameFieldType(array $wsField): ?string
    {
        if ((string) ($wsField['type'] ?? '') !== 'text') {
            return null;
        }

        $meta = is_array($wsField['meta'] ?? null) ? $wsField['meta'] : [];
        $autocomplete = strtolower(trim((string) ($meta['autocomplete'] ?? '')));

        if (isset(self::AUTOCOMPLETE_MAP[$autocomplete])) {
            return self::AUTOCOMPLETE_MAP[$autocomplete];
        }

        // An autocomplete token for a different purpose (organization, email, …)
        // rules the field out even when its label looks like a name part.
        if (!in_array($autocomplete, self::NEUTRAL_AUTOCOMPLETE, true)) {
            return null;
        }

        $label = self::normalizeLabel((string) ($wsField['label'] ?? ''));

        return self::LABEL_MAP[$label] ?? null;
    }

    /**
     * Normalize a WS Form label for exact matching.
     *
     * @param string $label
     * @return string
     */
    private static function normalizeLabel(string $label): string
    {
        $label = strtolower(wp_strip_all_tags($label));
        $label = str_replace(['*', ':'], ' ', $label);
        $label = preg_replace('/\s+/', ' ', $label);

        return trim((string) $label);
    }

    /**
     * Whether a WS Form field is marked required.
     *
     * @param array<string, mixed> $wsField
     * @return bool
     */
    private static function isRequired(array $wsField): bool
    {
        $meta = is_array($wsField['meta'] ?? null) ? $wsField['meta'] : [];

        return ($meta['required'] ?? '') === 'on';
    }

    /**
     * Resolve the label for the generated parent name field.
     *
     * @return string
     */
    private static function parentLabel(): string
    {
        $strings = BackendStrings::getNewFormStrings();

        return sanitize_text_field((string) ($strings['name'] ?? __('Name', 'ivyforms')));
    }
}
