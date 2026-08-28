<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

use IvyForms\Common\Sanitizer\HtmlSanitizer;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Extracted payload logic for IvyForms MCP field operations.
 *
 * Keeping the heavy branching outside `McpFieldAbilitiesHelper` reduces
 * cyclomatic/overall complexity for that class.
 */
class McpFieldPayloadHelper
{
    /**
     * @param array<string, mixed> $input
     * @return array{label: string, fields: array<int, array<string, mixed>>}
     */
    public static function buildFieldPayload(
        array $input,
        int $formId,
        int $position,
        int $rowIndex,
        int $fieldIndex
    ): array {
        $type = sanitize_text_field($input['type'] ?? 'text');
        $label = sanitize_text_field($input['label'] ?? self::defaultLabelForType($type));
        $widthInput = (int) ($input['width'] ?? 100);
        $width = in_array($widthInput, [25, 50, 75, 100], true) ? $widthInput : 100;
        $required = array_key_exists('required', $input)
            ? !empty($input['required'])
            : self::defaultRequiredForType($type);

        $newField = array_merge(
            self::baseField($type, $formId, $fieldIndex, $position, $rowIndex, $width, $label, $required, $input),
            McpFieldTypeDefaultsHelper::defaultsForType($type, $input)
        );

        if (!empty($input['pageId']) && is_string($input['pageId'])) {
            McpMultiPageHelper::applyPageIdToField($newField, sanitize_text_field($input['pageId']));
        }

        $newFields = self::buildRelatedFields($type, $input, $newField, $formId, $fieldIndex, $position);
        self::propagatePageIdToCompoundFields($newFields, $newField, $fieldIndex);

        return [
            'label'  => $label,
            'fields' => $newFields,
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $newField
     * @return array<int, array<string, mixed>>
     */
    private static function buildRelatedFields(
        string $type,
        array $input,
        array $newField,
        int $formId,
        int $fieldIndex,
        int $position
    ): array {
        $newFields = [$newField];

        if (in_array($type, ['name', 'address'], true)) {
            return array_merge(
                $newFields,
                self::compoundChildFields($type, $formId, $fieldIndex, $position)
            );
        }

        if ($type !== 'likert') {
            return $newFields;
        }

        $likertRows = self::resolveLikertRows($input);

        return array_merge(
            $newFields,
            self::likertRowChildFields($likertRows, $formId, $fieldIndex, $position)
        );
    }

    /**
     * @param array<int, array<string, mixed>> $newFields
     * @param array<string, mixed> $newField
     */
    private static function propagatePageIdToCompoundFields(
        array &$newFields,
        array $newField,
        int $fieldIndex
    ): void {
        if (empty($newField['pageId']) || count($newFields) <= 1) {
            return;
        }

        foreach ($newFields as &$compoundField) {
            if ($compoundField === $newField) {
                continue;
            }
            if ((int) ($compoundField['fieldIndex'] ?? 0) === $fieldIndex) {
                McpMultiPageHelper::applyPageIdToField($compoundField, (string) $newField['pageId']);
            }
        }
        unset($compoundField);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    private static function baseField(
        string $type,
        int $formId,
        int $fieldIndex,
        int $position,
        int $rowIndex,
        int $width,
        string $label,
        bool $required,
        array $input
    ): array {
        return [
            'id'                      => 0,
            'formId'                  => $formId,
            'fieldIndex'              => $fieldIndex,
            'type'                    => $type,
            'position'                => $position,
            'rowIndex'                => $rowIndex,
            'columnIndex'             => 0,
            'width'                   => $width,
            'parentId'                => null,
            'rows'                    => 0,
            'label'                   => $label,
            'required'                => $required,
            'placeholder'             => sanitize_text_field($input['placeholder'] ?? ''),
            'hideLabel'               => false,
            'readOnly'                => false,
            'description'             => HtmlSanitizer::sanitizeFieldDescription($input['description'] ?? ''),
            'requiredMessage'         => '',
            'cssClasses'              => '',
            'shuffleOptions'          => false,
            'enableSearch'            => false,
            'phoneFormat'             => 'international',
            'phoneAutoDetect'         => $type === 'phone',
            'minValue'                => null,
            'maxValue'                => null,
            'step'                    => 1,
            'numberFormat'            => '',
            'visible'                 => true,
            'showRatingText'          => false,
            'htmlContent'             => '',
            'limitRange'              => false,
            'confirmFieldEnabled'     => false,
            'confirmFieldLabel'       => '',
            'confirmFieldPlaceholder' => '',
            'confirmFieldHideLabel'   => false,
            'timeFieldType'           => '',
            'timeFormat'              => '',
            'minTimeValue'            => null,
            'maxTimeValue'            => null,
            'limitTimeRange'          => false,
            'dateFieldType'           => '',
            'dateFormat'              => '',
            'minDateValue'            => null,
            'maxDateValue'            => null,
            'limitDateRange'          => false,
            'defaultValue'            => '',
            'limitMaxLength'          => false,
            'maxLength'               => 255,
            'labelPosition'           => 'default',
            'noDuplicates'            => false,
            'inputPrefix'             => '',
            'inputSuffix'             => '',
            'ratingIcon'              => 'star',
            'fieldOptions'            => self::defaultFieldOptions($type),
            'showValues'              => in_array(
                $type,
                ['radio', 'checkbox', 'select', 'multi-select', 'product'],
                true
            ),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function compoundChildFields(
        string $type,
        int $formId,
        int $fieldIndex,
        int $position
    ): array {
        $strings = self::builderStrings();

        if ($type === 'name') {
            return [
                [
                    'id'              => 0,
                    'formId'          => $formId,
                    'fieldIndex'      => $fieldIndex,
                    'type'            => 'text',
                    'label'           => $strings['first_name'] ?? __('First name', 'ivyforms'),
                    'required'        => false,
                    'defaultValue'    => '',
                    'placeholder'     => '',
                    'position'        => $position,
                    'parentId'        => 0,
                    'hideLabel'       => false,
                    'description'     => '',
                    'requiredMessage' => '',
                    'settings'        => wp_json_encode(['nameFieldType' => 'nameField1']) ?: '',
                ],
                [
                    'id'              => 0,
                    'formId'          => $formId,
                    'fieldIndex'      => $fieldIndex,
                    'type'            => 'text',
                    'label'           => $strings['last_name'] ?? __('Last name', 'ivyforms'),
                    'required'        => false,
                    'defaultValue'    => '',
                    'placeholder'     => '',
                    'position'        => $position,
                    'parentId'        => 0,
                    'hideLabel'       => false,
                    'description'     => '',
                    'requiredMessage' => '',
                    'settings'        => wp_json_encode(['nameFieldType' => 'nameField2']) ?: '',
                ],
            ];
        }

        if ($type !== 'address') {
            return [];
        }

        $addressParts = [
            ['type' => 'streetAddress', 'label' => $strings['street_address'] ?? __('Street address', 'ivyforms')],
            ['type' => 'addressLine2', 'label' => $strings['address_line_2'] ?? __('Address line 2', 'ivyforms')],
            ['type' => 'city', 'label' => $strings['city'] ?? __('City', 'ivyforms')],
            ['type' => 'state', 'label' => $strings['state'] ?? __('State / Province', 'ivyforms')],
            ['type' => 'zip', 'label' => $strings['zip'] ?? __('Zip / Postal code', 'ivyforms')],
            ['type' => 'country', 'label' => $strings['country'] ?? __('Country', 'ivyforms')],
        ];

        $childFields = [];
        foreach ($addressParts as $addressPart) {
            $childFields[] = [
                'id'              => 0,
                'formId'          => $formId,
                'fieldIndex'      => $fieldIndex,
                'type'            => 'text',
                'label'           => $addressPart['label'],
                'required'        => false,
                'defaultValue'    => '',
                'placeholder'     => '',
                'position'        => $position,
                'parentId'        => 0,
                'hideLabel'       => false,
                'description'     => '',
                'requiredMessage' => '',
                'visible'         => true,
                'settings'        => wp_json_encode(['addressFieldType' => $addressPart['type']]) ?: '',
            ];
        }

        return $childFields;
    }

    /**
     * @param array<string, mixed> $input
     * @return array<int, array{label: string, value: string}>
     */
    private static function resolveLikertRows(array $input): array
    {
        if (isset($input['likertRows']) && is_array($input['likertRows'])) {
            return self::sanitizeLikertOptions($input['likertRows']);
        }

        if (!empty($input['likertSingleRow'])) {
            return [
                ['label' => '', 'value' => '_single'],
            ];
        }

        return self::defaultLikertRows();
    }

    /**
     * @param array<int, mixed> $options
     * @return array<int, array{label: string, value: string}>
     */
    private static function sanitizeLikertOptions(array $options): array
    {
        $sanitized = [];

        foreach ($options as $option) {
            if (!is_array($option)) {
                continue;
            }

            $label = sanitize_text_field((string) ($option['label'] ?? ''));
            $value = sanitize_text_field((string) ($option['value'] ?? ''));

            if ($label === '' && $value === '') {
                continue;
            }

            $sanitized[] = [
                'label' => $label,
                'value' => $value !== '' ? $value : sanitize_title($label),
            ];
        }

        return $sanitized;
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    private static function defaultLikertRows(): array
    {
        $strings = self::growthFieldOptionsStrings();

        return [
            [
                'label' => $strings['statement_1'] ?? __('Statement 1', 'ivyforms'),
                'value' => 'statement_1',
            ],
            [
                'label' => $strings['statement_2'] ?? __('Statement 2', 'ivyforms'),
                'value' => 'statement_2',
            ],
            [
                'label' => $strings['statement_3'] ?? __('Statement 3', 'ivyforms'),
                'value' => 'statement_3',
            ],
        ];
    }

    /**
     * Likert rows are persisted as hidden child text fields (same pattern as the builder save).
     *
     * @param array<int, array{label: string, value: string}> $likertRows
     * @return array<int, array<string, mixed>>
     */
    public static function likertRowChildFields(
        array $likertRows,
        int $formId,
        int $fieldIndex,
        int $position
    ): array {
        $childFields = [];

        foreach ($likertRows as $rowIndex => $row) {
            $childFields[] = [
                'id'              => 0,
                'formId'          => $formId,
                'fieldIndex'      => $fieldIndex,
                'type'            => 'text',
                'label'           => $row['label'],
                'required'        => false,
                'defaultValue'    => $row['value'],
                'placeholder'     => '',
                'position'        => $position,
                'rowIndex'        => $position,
                'columnIndex'     => 0,
                'width'           => 100,
                'parentId'        => 0,
                'hideLabel'       => false,
                'readOnly'        => false,
                'description'     => '',
                'requiredMessage' => '',
                'visible'         => true,
                'subFieldIndex'   => $rowIndex,
            ];
        }

        return $childFields;
    }

    /**
     * @return array<string, string>
     */
    private static function growthFieldOptionsStrings(): array
    {
        if (!class_exists(\IvyFormsPro\Plans\Growth\Services\Translations\BackendStrings::class)) {
            return [];
        }

        /** @var array<string, string> */
        return \IvyFormsPro\Plans\Growth\Services\Translations\BackendStrings::getFieldOptionsStrings();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function defaultFieldOptions(string $type): array
    {
        $optionsByType = [
            'radio' => [
                [
                    'id' => 0,
                    'fieldId' => 0,
                    'label' => __('Option 1', 'ivyforms'),
                    'value' => 'option1',
                    'isDefault' => true,
                    'position' => 1,
                ],
                [
                    'id' => 0,
                    'fieldId' => 0,
                    'label' => __('Option 2', 'ivyforms'),
                    'value' => 'option2',
                    'isDefault' => false,
                    'position' => 2,
                ],
            ],
            'checkbox' => [
                [
                    'id' => 0,
                    'fieldId' => 0,
                    'label' => __('Choice 1', 'ivyforms'),
                    'value' => 'choice1',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 0,
                    'fieldId' => 0,
                    'label' => __('Choice 2', 'ivyforms'),
                    'value' => 'choice2',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 0,
                    'fieldId' => 0,
                    'label' => __('Choice 3', 'ivyforms'),
                    'value' => 'choice3',
                    'isDefault' => false,
                    'position' => 3,
                ],
            ],
            'select' => [
                [
                    'id' => 0,
                    'fieldId' => 0,
                    'label' => __('Option 1', 'ivyforms'),
                    'value' => 'option1',
                    'isDefault' => true,
                    'position' => 1,
                ],
                [
                    'id' => 0,
                    'fieldId' => 0,
                    'label' => __('Option 2', 'ivyforms'),
                    'value' => 'option2',
                    'isDefault' => false,
                    'position' => 2,
                ],
            ],
            'multi-select' => [
                [
                    'id' => 0,
                    'fieldId' => 0,
                    'label' => __('Option 1', 'ivyforms'),
                    'value' => 'option1',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 0,
                    'fieldId' => 0,
                    'label' => __('Option 2', 'ivyforms'),
                    'value' => 'option2',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 0,
                    'fieldId' => 0,
                    'label' => __('Option 3', 'ivyforms'),
                    'value' => 'option3',
                    'isDefault' => false,
                    'position' => 3,
                ],
            ],
        ];

        return $optionsByType[$type] ?? [];
    }

    /**
     * Duplicate options for copied fields.
     *
     * @param array<int, array<string, mixed>> $fieldOptions
     * @return array<int, array<string, mixed>>
     */
    public static function duplicateFieldOptions(array $fieldOptions): array
    {
        $duplicatedOptions = [];
        $position = 1;

        foreach ($fieldOptions as $fieldOption) {
            $fieldOption['id'] = 0;
            $fieldOption['fieldId'] = 0;
            $fieldOption['position'] = $position++;
            $duplicatedOptions[] = $fieldOption;
        }

        return $duplicatedOptions;
    }

    private static function defaultLabelForType(string $type): string
    {
        $builderStrings = self::builderStrings();
        $commonStrings = self::commonStrings();
        $componentStrings = self::componentStrings();

        $labels = [
            'text'         => $builderStrings['text'] ?? __('Text', 'ivyforms'),
            'textarea'     => $builderStrings['paragraph'] ?? __('Paragraph', 'ivyforms'),
            'email'        => $builderStrings['email'] ?? __('Email', 'ivyforms'),
            'number'       => $builderStrings['number'] ?? __('Number', 'ivyforms'),
            'phone'        => $builderStrings['phone'] ?? __('Phone', 'ivyforms'),
            'website'      => $builderStrings['website'] ?? __('Website/URL', 'ivyforms'),
            'name'         => $builderStrings['name'] ?? __('Name', 'ivyforms'),
            'address'      => $builderStrings['address'] ?? __('Address', 'ivyforms'),
            'radio'        => $builderStrings['radio_button'] ?? __('Radio button', 'ivyforms'),
            'checkbox'     => $builderStrings['checkbox'] ?? __('Checkbox', 'ivyforms'),
            'select'       => $builderStrings['dropdown'] ?? __('Dropdown', 'ivyforms'),
            'multi-select' => $builderStrings['dropdown'] ?? __('Dropdown', 'ivyforms'),
            'date'         => $builderStrings['date_picker'] ?? __('Date picker', 'ivyforms'),
            'time'         => $builderStrings['time_picker'] ?? __('Time picker', 'ivyforms'),
            'date_time'    => $builderStrings['date_time_picker'] ?? __('Date & time (picker)', 'ivyforms'),
            'rating'       => $builderStrings['rating'] ?? __('Rating', 'ivyforms'),
            'html'         => $builderStrings['html'] ?? __('HTML', 'ivyforms'),
            'slider'       => $builderStrings['slider'] ?? __('Slider', 'ivyforms'),
            'file-upload'  => $commonStrings['file_upload'] ?? __('File upload', 'ivyforms'),
            'gdpr'         => $builderStrings['gdpr'] ?? __('GDPR', 'ivyforms'),
            'product'      => $componentStrings['product'] ?? __('Product', 'ivyforms'),
            'quantity'     => $componentStrings['quantity'] ?? __('Quantity', 'ivyforms'),
            'total'        => $componentStrings['total'] ?? __('Total', 'ivyforms'),
            'nps'          => $builderStrings['net_promoter'] ?? __('Net promoter', 'ivyforms'),
            'likert'       => $builderStrings['likert'] ?? __('Likert', 'ivyforms'),
            'rich_text'    => $builderStrings['rich_text'] ?? __('Rich Text', 'ivyforms'),
            'password'     => $builderStrings['password'] ?? __('Password', 'ivyforms'),
            'signature'    => $builderStrings['signature'] ?? __('Signature', 'ivyforms'),
        ];

        return $labels[$type] ?? ucfirst(str_replace(['_', '-'], ' ', $type));
    }

    private static function defaultRequiredForType(string $type): bool
    {
        return in_array($type, ['gdpr', 'total'], true);
    }

    /**
     * @return array<string, string>
     */
    private static function builderStrings(): array
    {
        /** @var array<string, string> */
        return BackendStrings::getNewFormStrings();
    }

    /**
     * @return array<string, string>
     */
    private static function commonStrings(): array
    {
        /** @var array<string, string> */
        return BackendStrings::getCommonStrings();
    }

    /**
     * @return array<string, string>
     */
    private static function componentStrings(): array
    {
        /** @var array<string, string> */
        return BackendStrings::getComponentsStrings();
    }
}
