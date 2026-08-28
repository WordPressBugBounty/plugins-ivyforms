<?php

namespace IvyForms\Common\Helpers;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\FieldOptions\FieldOptions;
use IvyForms\Factory\FieldOptions\FieldOptionsFactory;
use IvyForms\Repository\FieldOptions\FieldOptionsRepositoryInterface;
use IvyForms\Services\Media\ImageService;

/**
 * General helper utilities related to Field processing.
 */
class FieldHelper
{
    /**
     * Resolve parentId using fieldIndex -> parentId map if needed.
     *
     * @param array<string,mixed> $data Passed by reference; parentId modified in place.
     * @param array<int,int> $parentMap Map of fieldIndex => newly created parent field id.
     */
    public static function resolveParentId(array &$data, array $parentMap): void
    {
        if (!array_key_exists('parentId', $data)) {
            return;
        }

        $parentId = $data['parentId'];

        //parentId is 0 or null → use current field's fieldIndex
        if (($parentId === 0 || $parentId === '0' || $parentId === null) && isset($data['fieldIndex'])) {
            $parentIndex = $data['fieldIndex'];
            if (isset($parentMap[$parentIndex])) {
                $data['parentId'] = $parentMap[$parentIndex];
            }
            return;
        }

        //parentId is numeric → use it as parent's fieldIndex
        if (is_numeric($parentId)) {
            if (isset($parentMap[$parentId])) {
                $data['parentId'] = $parentMap[$parentId];
            }
        }
    }

    /**
     * Remap parentId for duplicated field using old-to-new field ID map.
     *
     * @param array<string,mixed> $fieldData Passed by reference; parentId modified in place.
     * @param array<int,int> $fieldIdMap Map of old field ID => new field ID.
     */
    public static function remapParentIdForDuplication(array &$fieldData, array $fieldIdMap): void
    {
        if ($fieldData['parentId'] !== null && isset($fieldIdMap[$fieldData['parentId']])) {
            $fieldData['parentId'] = $fieldIdMap[$fieldData['parentId']];
        }
    }

    /**
     * Partition field options into new, update, and submitted IDs.
     *
     * @param array<string, mixed> $fieldData
     * @param array<int> $existingOptionIds
     * @return array{
     *   newOptions: array<int, FieldOptions>,
     *   updateOptions: array<int, FieldOptions>,
     *   submittedOptionIds: array<int, int>
     * }
     * @throws ValidationException
     */
    public static function partitionFieldOptions(array $fieldData, array $existingOptionIds): array
    {
        $newOptions = [];
        $updateOptions = [];
        $submittedOptionIds = [];
        foreach ($fieldData['fieldOptions'] as $optionData) {
            $option = FieldOptionsFactory::create($optionData);
            if (
                isset($optionData['id']) && $optionData['id'] > 0
                && in_array($optionData['id'], $existingOptionIds, true)
            ) {
                $updateOptions[] = $option;
                $submittedOptionIds[] = $optionData['id'];
                continue;
            }
            $newOptions[] = $option;
        }
        return [
            'newOptions' => $newOptions,
            'updateOptions' => $updateOptions,
            'submittedOptionIds' => $submittedOptionIds,
        ];
    }

    /**
     * Check if a field type has options (radio, checkbox, select, multi-select).
     *
     * @param string $fieldType
     * @return bool
     */
    public static function fieldTypeHasOptions(string $fieldType): bool
    {
        return in_array($fieldType, ['radio', 'checkbox', 'select', 'multi-select', 'product', 'rating'], true);
    }

    /**
     * Check if a field type is a security/CAPTCHA field.
     *
     * Security fields are used for bot/spam protection and should be excluded
     * from stored entries, integration payloads, placeholder lists, and any
     * other processing that targets user-submitted data.
     *
     * @param string $fieldType
     * @return bool
     */
    public static function isSecurityField(string $fieldType): bool
    {
        return in_array($fieldType, ['recaptcha', 'turnstile', 'hcaptcha'], true);
    }

    /**
     * Duplicate field options from one field to another.
     *
     * @param int $oldFieldId
     * @param int $newFieldId
     * @param FieldOptionsRepositoryInterface $repository
     * @return void
     * @throws ValidationException
     */
    public static function duplicateFieldOptions(
        int $oldFieldId,
        int $newFieldId,
        FieldOptionsRepositoryInterface $repository
    ): void {
        $options = $repository->getByFieldId($oldFieldId);
        foreach ($options as $option) {
            $optionData = $option->toArray();
            unset($optionData['id']);
            $optionData['fieldId'] = $newFieldId;
            $repository->add(FieldOptionsFactory::create($optionData));
        }
    }

    /**
     * Process htmlContent to upload base64 images
     *
     * @param array<string, mixed> $fieldData Passed by reference; htmlContent modified in place
     * @param ImageService $imageService
     * @return void
     */
    public static function processFieldHtmlContent(array &$fieldData, ImageService $imageService): void
    {
        if (!empty($fieldData['htmlContent'])) {
            $processedContent = $imageService->processImagesInContent(
                $fieldData['htmlContent'],
                'field'
            );
            $fieldData['htmlContent'] = $processedContent;
        }
    }

    /**
     * Upload base64 images in rich text default values before form save.
     *
     * @param array<string, mixed> $fieldData Passed by reference; defaultValue modified in place
     * @param ImageService $imageService
     * @return void
     */
    public static function processRichTextDefaultValue(array &$fieldData, ImageService $imageService): void
    {
        if (($fieldData['type'] ?? '') !== 'rich_text' || empty($fieldData['defaultValue'])) {
            return;
        }

        $fieldData['defaultValue'] = $imageService->processImagesInContent(
            (string) $fieldData['defaultValue'],
            'field'
        );
    }
}
