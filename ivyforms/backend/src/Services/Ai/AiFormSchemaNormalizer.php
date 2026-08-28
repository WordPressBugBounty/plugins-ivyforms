<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Ai;

use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Services\Field\FieldType;
use IvyForms\Services\Template\TemplateDefaults;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Normalizes AI-generated form JSON into a create-ready IvyForms payload.
 */
class AiFormSchemaNormalizer
{
    /**
     * Default AI-friendly field types (lite). Pro can extend via
     * `ivyforms/ai/allowed_field_types`. Final list is intersected with
     * FieldType::getAllowedTypes() so license/plan gates still apply.
     */
    private const BASE_AI_FIELD_TYPES = [
        'text',
        'textarea',
        'email',
        'phone',
        'number',
        'website',
        'password',
        'select',
        'multi-select',
        'radio',
        'checkbox',
        'date',
        'time',
        'html',
        'gdpr',
        'rating',
        'slider',
        'name',
        'address',
        'file-upload',
    ];

    private const OPTION_FIELD_TYPES = [
        'select',
        'multi-select',
        'radio',
        'checkbox',
    ];

    private const COMPOUND_FIELD_TYPES = [
        'name',
        'address',
        'likert',
    ];

    private AiFormCompoundFieldExpander $compoundExpander;
    private AiFormMultipageNormalizer $multipageNormalizer;

    public function __construct(
        ?AiFormCompoundFieldExpander $compoundExpander = null,
        ?AiFormMultipageNormalizer $multipageNormalizer = null
    ) {
        $this->compoundExpander = $compoundExpander ?? new AiFormCompoundFieldExpander();
        $this->multipageNormalizer = $multipageNormalizer ?? new AiFormMultipageNormalizer();
    }

    /**
     * Field types the AI may emit for the current site (after Pro/plan filters).
     *
     * @return list<string>
     */
    public function getAllowedFieldTypes(): array
    {
        $types = self::BASE_AI_FIELD_TYPES;

        /**
         * Extend or restrict field types the AI may generate.
         *
         * @since 0.1.0
         *
         * @param string[] $types Candidate AI field type strings.
         * @return string[]
         */
        $filtered = apply_filters('ivyforms/ai/allowed_field_types', $types);

        if (!is_array($filtered)) {
            $filtered = $types;
        }

        $normalized = [];
        foreach ($filtered as $type) {
            if (is_string($type) && $type !== '') {
                $normalized[sanitize_key($type)] = true;
            }
        }

        $allowedToSave = array_fill_keys(FieldType::getAllowedTypes(), true);

        return array_values(array_filter(
            array_keys($normalized),
            static function (string $type) use ($allowedToSave): bool {
                return isset($allowedToSave[$type]);
            }
        ));
    }

    /**
     * @param array<string, mixed> $raw
     *
     * @return array<string, mixed>
     */
    public function normalize(array $raw): array
    {
        $name = sanitize_text_field((string) ($raw['name'] ?? ''));
        if ($name === '') {
            $name = BackendStrings::getSettingsFormBuilderStrings()['blank_form'];
        }

        $description = sanitize_textarea_field((string) ($raw['description'] ?? ''));
        $fieldsInput = $raw['fields'] ?? [];
        if (!is_array($fieldsInput)) {
            $fieldsInput = [];
        }

        $fields = $this->normalizeFields($fieldsInput);
        if ($fields === []) {
            $fields = [$this->fallbackTextField()];
        }

        $formType = Sanitizer::sanitizeFormType($raw['formType'] ?? 'classic');
        $multipage = $this->multipageNormalizer->normalizePages($raw, $formType);
        $pages = $multipage !== null ? $multipage['pages'] : null;
        $fields = $this->applyMultipageToFields($fields, $multipage);

        $formData = [
            'name' => $name,
            'description' => $description,
            'published' => 1,
            'showTitle' => 1,
            'showDescription' => $description !== '' ? 1 : 0,
            'storeEntries' => 1,
            'formType' => $formType,
            'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
            'fields' => $fields,
        ];

        if ($pages !== null) {
            $formData['pages'] = $pages;
            $formData['progressIndicator'] = [
                'type' => 'progress-bar',
                'showPageTitles' => true,
                'hidePageNumbers' => false,
                'hideConnectingLines' => false,
            ];
        }

        /**
         * Mutate the normalized AI form payload before sanitize/create.
         *
         * @since 0.1.0
         *
         * @param array<string, mixed> $formData Normalized form data.
         * @param array<string, mixed> $raw      Raw AI JSON.
         * @return array<string, mixed>
         */
        $filtered = apply_filters('ivyforms/ai/normalized_form_data', $formData, $raw);
        if (!is_array($filtered)) {
            return $formData;
        }

        return $filtered;
    }

    /**
     * @param array<int, mixed> $fieldsInput
     *
     * @return list<array<string, mixed>>
     */
    private function normalizeFields(array $fieldsInput): array
    {
        $allowedTypes = $this->getAllowedFieldTypes();
        $fields = [];
        $nextId = 1;
        $position = 1;

        foreach ($fieldsInput as $fieldInput) {
            if (!is_array($fieldInput)) {
                continue;
            }

            $type = sanitize_key((string) ($fieldInput['type'] ?? 'text'));
            if (!in_array($type, $allowedTypes, true)) {
                continue;
            }

            $built = $this->buildNormalizedField($fieldInput, $type, $position, $nextId);
            $fields[] = $built['field'];
            $nextId = $built['nextId'];

            if (in_array($type, self::COMPOUND_FIELD_TYPES, true)) {
                $expanded = $this->compoundExpander->expand(
                    $type,
                    $position,
                    !empty($built['field']['required']),
                    $nextId,
                    isset($built['field']['pageId']) ? (string) $built['field']['pageId'] : null
                );
                foreach ($expanded['children'] as $child) {
                    $fields[] = $child;
                }
                $nextId = $expanded['nextId'];
            }

            $position++;
        }

        return $fields;
    }

    /**
     * @param array<string, mixed> $fieldInput
     *
     * @return array{field: array<string, mixed>, nextId: int}
     */
    private function buildNormalizedField(
        array $fieldInput,
        string $type,
        int $position,
        int $nextId
    ): array {
        $label = sanitize_text_field((string) ($fieldInput['label'] ?? ''));
        if ($label === '') {
            $label = ucfirst(str_replace('-', ' ', $type));
        }

        $field = [
            'id' => $nextId++,
            'fieldIndex' => $position,
            'type' => $type,
            'label' => $label,
            'required' => !empty($fieldInput['required']),
            'parentId' => null,
            'defaultValue' => sanitize_text_field((string) ($fieldInput['defaultValue'] ?? '')),
            'placeholder' => sanitize_text_field((string) ($fieldInput['placeholder'] ?? '')),
            'position' => $position,
            'rowIndex' => $position - 1,
            'columnIndex' => 0,
            'width' => 100,
            'description' => sanitize_textarea_field((string) ($fieldInput['description'] ?? '')),
            'hideLabel' => !empty($fieldInput['hideLabel']),
        ];

        $pageId = sanitize_text_field((string) ($fieldInput['pageId'] ?? ''));
        if ($pageId !== '' && AiAvailability::canUseMultipage()) {
            $field['pageId'] = $pageId;
            $field['settings'] = ['pageId' => $pageId];
        }

        if ($type === 'textarea') {
            $rows = isset($fieldInput['rows']) ? (int) $fieldInput['rows'] : 3;
            $field['rows'] = max(2, min($rows, 12));
        }

        if (in_array($type, self::OPTION_FIELD_TYPES, true)) {
            $optionsResult = $this->normalizeChoices(
                $fieldInput['fieldOptions'] ?? [],
                $nextId
            );
            $field['fieldOptions'] = $optionsResult['choices'];
            $nextId = $optionsResult['nextId'];
        }

        return [
            'field' => $field,
            'nextId' => $nextId,
        ];
    }

    /**
     * Attach validated pageIds when multipage applies; otherwise strip any leaked ones.
     *
     * @param list<array<string, mixed>> $fields
     * @param array{pages: list<array<string, mixed>>, idMap: array<string, string>}|null $multipage
     *
     * @return list<array<string, mixed>>
     */
    private function applyMultipageToFields(array $fields, ?array $multipage): array
    {
        if ($multipage === null) {
            return $this->stripFieldPageIds($fields);
        }

        return $this->multipageNormalizer->ensureFieldPageIds(
            $fields,
            $multipage['pages'],
            $multipage['idMap']
        );
    }

    /**
     * Drop pageId from fields when multipage structure was not applied
     * (disabled entitlement, conversational, or invalid pages payload).
     *
     * @param list<array<string, mixed>> $fields
     *
     * @return list<array<string, mixed>>
     */
    private function stripFieldPageIds(array $fields): array
    {
        foreach ($fields as &$field) {
            unset($field['pageId']);
            if (!isset($field['settings']) || !is_array($field['settings'])) {
                continue;
            }

            unset($field['settings']['pageId']);
            if ($field['settings'] === []) {
                unset($field['settings']);
            }
        }
        unset($field);

        return $fields;
    }

    /**
     * @return array<string, mixed>
     */
    private function fallbackTextField(): array
    {
        return [
            'id' => 1,
            'fieldIndex' => 1,
            'type' => 'text',
            'label' => BackendStrings::getCommonStrings()['text'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 1,
            'rowIndex' => 0,
            'columnIndex' => 0,
            'width' => 100,
            'description' => '',
            'hideLabel' => false,
        ];
    }

    /**
     * @param mixed $optionsInput
     *
     * @return array{choices: list<array<string, mixed>>, nextId: int}
     */
    private function normalizeChoices($optionsInput, int $nextId): array
    {
        if (!is_array($optionsInput)) {
            $optionsInput = [];
        }

        $choices = [];
        $position = 1;

        foreach ($optionsInput as $optionInput) {
            if (!is_array($optionInput)) {
                continue;
            }

            $label = sanitize_text_field((string) ($optionInput['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $value = sanitize_text_field((string) ($optionInput['value'] ?? ''));
            if ($value === '') {
                $value = sanitize_title($label);
            }

            $choices[] = [
                'id' => $nextId++,
                'label' => $label,
                'value' => $value,
                'isDefault' => !empty($optionInput['isDefault']),
                'position' => $position++,
            ];
        }

        if ($choices === []) {
            $choices = $this->defaultChoices($nextId);
            $nextId += 2;
        }

        return [
            'choices' => $choices,
            'nextId' => $nextId,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function defaultChoices(int $nextId): array
    {
        return [
            [
                'id' => $nextId,
                'label' => 'Option 1',
                'value' => 'option_1',
                'isDefault' => false,
                'position' => 1,
            ],
            [
                'id' => $nextId + 1,
                'label' => 'Option 2',
                'value' => 'option_2',
                'isDefault' => false,
                'position' => 2,
            ],
        ];
    }
}
