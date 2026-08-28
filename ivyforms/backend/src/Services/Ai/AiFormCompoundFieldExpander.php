<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Ai;

use IvyForms\Common\Helpers\McpHelpers\McpFieldPayloadHelper;

/**
 * Expands compound AI field parents (name, address, likert) into child rows.
 */
class AiFormCompoundFieldExpander
{
    /**
     * @return array{children: list<array<string, mixed>>, nextId: int}
     */
    public function expand(
        string $type,
        int $parentFieldIndex,
        bool $parentRequired,
        int $nextId,
        ?string $pageId = null
    ): array {
        if ($type === 'likert') {
            return $this->expandLikert($parentFieldIndex, $nextId, $pageId);
        }

        if ($type === 'name') {
            return $this->expandName($parentFieldIndex, $parentRequired, $nextId, $pageId);
        }

        if ($type === 'address') {
            return $this->expandAddress($parentFieldIndex, $parentRequired, $nextId, $pageId);
        }

        return [
            'children' => [],
            'nextId' => $nextId,
        ];
    }

    /**
     * @return array{children: list<array<string, mixed>>, nextId: int}
     */
    private function expandName(
        int $parentFieldIndex,
        bool $parentRequired,
        int $nextId,
        ?string $pageId
    ): array {
        $rawChildren = McpFieldPayloadHelper::compoundChildFields(
            'name',
            0,
            $parentFieldIndex,
            $parentFieldIndex
        );

        $children = [];
        foreach ($rawChildren as $index => $child) {
            if (!is_array($child)) {
                continue;
            }

            $settings = $this->decodeSettings($child['settings'] ?? null);
            $nameFieldType = (string) ($settings['nameFieldType'] ?? ('nameField' . ($index + 1)));
            $child = $this->baseChild($child, $nextId++, $parentFieldIndex, $index, $pageId);
            $child['nameFieldType'] = $nameFieldType;
            $child['required'] = $parentRequired;
            $child['settings'] = array_merge(
                is_array($child['settings'] ?? null) ? $child['settings'] : [],
                [
                    'nameFieldType' => $nameFieldType,
                    'subFieldIndex' => $index,
                ]
            );
            $children[] = $child;
        }

        return [
            'children' => $children,
            'nextId' => $nextId,
        ];
    }

    /**
     * @return array{children: list<array<string, mixed>>, nextId: int}
     */
    private function expandAddress(
        int $parentFieldIndex,
        bool $parentRequired,
        int $nextId,
        ?string $pageId
    ): array {
        $rawChildren = McpFieldPayloadHelper::compoundChildFields(
            'address',
            0,
            $parentFieldIndex,
            $parentFieldIndex
        );
        $defaults = ['streetAddress', 'addressLine2', 'city', 'state', 'zip', 'country'];

        $children = [];
        foreach ($rawChildren as $index => $child) {
            if (!is_array($child)) {
                continue;
            }

            $settings = $this->decodeSettings($child['settings'] ?? null);
            $addressType = (string) (
                $settings['addressFieldType']
                ?? $settings['type']
                ?? $child['addressType']
                ?? ($defaults[$index] ?? ('addressPart' . ($index + 1)))
            );

            $child = $this->baseChild($child, $nextId++, $parentFieldIndex, $index, $pageId);
            $child['addressType'] = $addressType;
            $child['required'] = $addressType === 'addressLine2' ? false : $parentRequired;
            $child['visible'] = true;
            $child['settings'] = array_merge(
                is_array($child['settings'] ?? null) ? $child['settings'] : [],
                [
                    'type' => $addressType,
                    'hideLabel' => false,
                    'description' => '',
                    'placeholder' => (string) ($child['placeholder'] ?? ''),
                    'requiredMessage' => '',
                    'visible' => true,
                ]
            );
            $children[] = $child;
        }

        return [
            'children' => $children,
            'nextId' => $nextId,
        ];
    }

    /**
     * @return array{children: list<array<string, mixed>>, nextId: int}
     */
    private function expandLikert(int $parentFieldIndex, int $nextId, ?string $pageId): array
    {
        $defaultRows = [
            ['label' => __('Statement 1', 'ivyforms'), 'value' => 'statement_1'],
            ['label' => __('Statement 2', 'ivyforms'), 'value' => 'statement_2'],
            ['label' => __('Statement 3', 'ivyforms'), 'value' => 'statement_3'],
        ];

        $rawChildren = McpFieldPayloadHelper::likertRowChildFields(
            $defaultRows,
            0,
            $parentFieldIndex,
            $parentFieldIndex
        );

        $children = [];
        foreach ($rawChildren as $index => $child) {
            if (!is_array($child)) {
                continue;
            }

            $child = $this->baseChild($child, $nextId++, $parentFieldIndex, $index, $pageId);
            $children[] = $child;
        }

        return [
            'children' => $children,
            'nextId' => $nextId,
        ];
    }

    /**
     * @param array<string, mixed> $child
     *
     * @return array<string, mixed>
     */
    private function baseChild(
        array $child,
        int $id,
        int $parentFieldIndex,
        int $index,
        ?string $pageId
    ): array {
        $child['id'] = $id;
        $child['parentId'] = 0;
        $child['fieldIndex'] = $parentFieldIndex;
        $child['position'] = $parentFieldIndex;
        $child['subFieldIndex'] = $index;
        $child['rowIndex'] = 0;
        $child['columnIndex'] = 0;
        $child['width'] = 100;

        if ($pageId !== null && $pageId !== '') {
            $child['pageId'] = $pageId;
            $child['settings'] = array_merge(
                is_array($child['settings'] ?? null) ? $child['settings'] : [],
                ['pageId' => $pageId]
            );
        }

        return $child;
    }

    /**
     * @param mixed $settings
     *
     * @return array<string, mixed>
     */
    private function decodeSettings($settings): array
    {
        if (is_array($settings)) {
            return $settings;
        }

        if (!is_string($settings) || $settings === '') {
            return [];
        }

        $decoded = json_decode($settings, true);

        return is_array($decoded) ? $decoded : [];
    }
}
