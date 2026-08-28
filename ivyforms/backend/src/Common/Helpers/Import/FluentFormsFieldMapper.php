<?php

namespace IvyForms\Common\Helpers\Import;

/**
 * Map individual Fluent Forms elements into IvyForms field arrays.
 *
 * Splitting field mapping out of FluentFormsImportConverter keeps each class
 * focused and within complexity limits.
 */
class FluentFormsFieldMapper
{
    /**
     * Map Fluent Forms elements to IvyForms field types. Elements absent from
     * this map are skipped during import.
     *
     * @var array<string, string>
     */
    private const ELEMENT_MAP = [
        'input_text'     => 'text',
        'input_mask'     => 'text',
        'input_email'    => 'email',
        'input_number'   => 'number',
        'textarea'       => 'textarea',
        'select'         => 'select',
        'select_country' => 'select',
        'input_radio'    => 'radio',
        'input_checkbox' => 'checkbox',
        'input_url'      => 'website',
        'input_password' => 'password',
        'phone'          => 'phone',
        'input_phone'    => 'phone',
        'input_date'     => 'date',
        'ratings'        => 'rating',
        'gdpr_agreement' => 'gdpr',
        'input_file'     => 'file-upload',
        'input_image'    => 'file-upload',
        'custom_html'    => 'html',
    ];

    /**
     * Field types that carry selectable options.
     *
     * @var array<int, string>
     */
    private const OPTION_TYPES = ['select', 'radio', 'checkbox', 'multi-select'];

    /**
     * Sub-field key to IvyForms name field type mapping.
     *
     * @var array<string, string>
     */
    private const NAME_FIELD_MAP = [
        'first_name'  => 'nameField1',
        'last_name'   => 'nameField2',
        'middle_name' => 'nameField3',
    ];

    /**
     * Map a single Fluent Forms element and append the resulting field(s).
     *
     * @param array<string, mixed> $rawField
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @return void
     */
    public static function append(array $rawField, array &$fields, array &$context): void
    {
        $element = (string) ($rawField['element'] ?? '');

        if ($element === 'container') {
            self::appendContainerFields($rawField, $fields, $context);
            return;
        }

        if ($element === 'input_name') {
            self::appendNameField($rawField, $fields, $context);
            return;
        }

        $ivyType = self::resolveType($element, $rawField);
        if ($ivyType === null) {
            return;
        }

        if ($ivyType === 'html' && self::extractHtmlContent($rawField) === '') {
            // Skip empty HTML blocks — nothing useful to import.
            return;
        }

        $fields[] = self::buildSimpleField($ivyType, $rawField, $context);
    }

    /**
     * Build a single IvyForms field from a mapped element.
     *
     * @param string $ivyType
     * @param array<string, mixed> $rawField
     * @param array<string, int> $context
     * @return array<string, mixed>
     */
    private static function buildSimpleField(string $ivyType, array $rawField, array &$context): array
    {
        $attributes = self::child($rawField, 'attributes');
        $settings = self::child($rawField, 'settings');
        $validation = self::child($settings, 'validation_rules');
        $required = self::child($validation, 'required');

        $field = [
            'id'              => $context['id']++,
            'fieldIndex'      => $context['fieldIndex']++,
            'type'            => $ivyType,
            'label'           => self::resolveLabel($settings),
            'placeholder'     => self::text($attributes, 'placeholder'),
            'description'     => self::text($settings, 'help_message'),
            'required'        => !empty($required['value']),
            'requiredMessage' => self::text($required, 'message'),
            'defaultValue'    => self::text($attributes, 'value'),
            'parentId'        => null,
            'position'        => $context['position']++,
            'rowIndex'        => $context['rowIndex']++,
            'columnIndex'     => 0,
            'width'           => 100,
        ];

        if ($ivyType === 'html') {
            $field['htmlContent'] = self::extractHtmlContent($rawField);
        }

        if ($ivyType === 'password') {
            // Match Pro password field defaults; Fluent Forms has no confirm field.
            $field['confirmFieldEnabled'] = false;
            $field['showPasswordIcon'] = true;
            $field['inputPrefix'] = self::text($settings, 'prefix_label');
            $field['inputSuffix'] = self::text($settings, 'suffix_label');
        }

        if (in_array($ivyType, self::OPTION_TYPES, true)) {
            $options = FluentFormsOptionsExtractor::extract($settings, $attributes);
            if (!empty($options)) {
                $field['fieldOptions'] = $options;
            }
        }

        return $field;
    }

    /**
     * Extract sanitized HTML from a Fluent Forms custom_html element.
     *
     * @param array<string, mixed> $rawField
     * @return string
     */
    private static function extractHtmlContent(array $rawField): string
    {
        $settings = self::child($rawField, 'settings');

        return wp_kses_post((string) ($settings['html_codes'] ?? ''));
    }

    /**
     * Flatten a container's columns into top-level fields.
     *
     * @param array<string, mixed> $rawField
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @return void
     */
    private static function appendContainerFields(array $rawField, array &$fields, array &$context): void
    {
        foreach (($rawField['columns'] ?? []) as $column) {
            if (!is_array($column)) {
                continue;
            }
            foreach (($column['fields'] ?? []) as $columnField) {
                if (is_array($columnField)) {
                    self::append($columnField, $fields, $context);
                }
            }
        }
    }

    /**
     * Build an IvyForms composite name field (parent + visible sub-fields).
     *
     * @param array<string, mixed> $rawField
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @return void
     */
    private static function appendNameField(array $rawField, array &$fields, array &$context): void
    {
        $settings = self::child($rawField, 'settings');
        $required = !empty(self::child(self::child($settings, 'validation_rules'), 'required')['value']);

        $parentIndex = $context['fieldIndex']++;
        $fields[] = [
            'id'           => $context['id']++,
            'fieldIndex'   => $parentIndex,
            'type'         => 'name',
            'label'        => self::resolveLabel($settings),
            'placeholder'  => '',
            'required'     => $required,
            'defaultValue' => '',
            'parentId'     => null,
            'position'     => $context['position']++,
            'rowIndex'     => $context['rowIndex']++,
            'columnIndex'  => 0,
            'width'        => 100,
        ];

        self::appendNameSubFields($rawField, $fields, $context, $parentIndex, $required);
    }

    /**
     * Append the visible sub-fields (first/last/middle) of a name field.
     *
     * @param array<string, mixed> $rawField
     * @param array<int, array<string, mixed>> $fields
     * @param array<string, int> $context
     * @param int $parentIndex
     * @param bool $required
     * @return void
     */
    private static function appendNameSubFields(
        array $rawField,
        array &$fields,
        array &$context,
        int $parentIndex,
        bool $required
    ): void {
        $subPosition = 1;
        foreach (self::child($rawField, 'fields') as $key => $subField) {
            if (!is_array($subField) || self::isHiddenSubField($subField)) {
                continue;
            }

            $subSettings = self::child($subField, 'settings');
            $subAttributes = self::child($subField, 'attributes');
            $nameFieldType = self::NAME_FIELD_MAP[(string) $key] ?? ('nameField' . $subPosition);

            $fields[] = [
                'id'           => $context['id']++,
                'fieldIndex'   => $parentIndex,
                'type'         => 'text',
                'label'        => self::text($subSettings, 'label'),
                'placeholder'  => self::text($subAttributes, 'placeholder'),
                'required'     => $required,
                'defaultValue' => '',
                'parentId'     => $parentIndex,
                'position'     => $subPosition,
                'settings'     => wp_json_encode(['nameFieldType' => $nameFieldType]),
            ];
            $subPosition++;
        }
    }

    /**
     * Whether a name sub-field is explicitly hidden.
     *
     * @param array<string, mixed> $subField
     * @return bool
     */
    private static function isHiddenSubField(array $subField): bool
    {
        $settings = self::child($subField, 'settings');

        return ($settings['visible'] ?? true) === false;
    }

    /**
     * Resolve the IvyForms field type for a Fluent Forms element.
     *
     * @param string $element
     * @param array<string, mixed> $rawField
     * @return string|null
     */
    private static function resolveType(string $element, array $rawField): ?string
    {
        $ivyType = self::ELEMENT_MAP[$element] ?? null;
        if ($ivyType === null) {
            return null;
        }

        if ($ivyType === 'select' && !empty(self::child($rawField, 'attributes')['multiple'])) {
            return 'multi-select';
        }

        return $ivyType;
    }

    /**
     * Resolve a field label, falling back to the admin label.
     *
     * @param array<string, mixed> $settings
     * @return string
     */
    private static function resolveLabel(array $settings): string
    {
        $label = self::text($settings, 'label');
        if ($label === '') {
            $label = self::text($settings, 'admin_field_label');
        }

        return $label;
    }

    /**
     * Get a nested array value by key, defaulting to an empty array.
     *
     * @param array<string, mixed> $source
     * @param string $key
     * @return array<string, mixed>
     */
    private static function child(array $source, string $key): array
    {
        $value = $source[$key] ?? null;

        return is_array($value) ? $value : [];
    }

    /**
     * Get a sanitized string value from an array by key.
     *
     * Fluent Forms stores multi-value data (such as checkbox defaults) as arrays,
     * so non-scalars are discarded instead of being cast to string.
     *
     * @param array<string, mixed> $source
     * @param string $key
     * @return string
     */
    private static function text(array $source, string $key): string
    {
        $value = $source[$key] ?? '';
        if (!is_scalar($value)) {
            return '';
        }

        return sanitize_text_field((string) $value);
    }
}
