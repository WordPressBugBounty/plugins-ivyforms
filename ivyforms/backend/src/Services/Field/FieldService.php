<?php

namespace IvyForms\Services\Field;

use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\Field\Field;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Factory\Field\FieldFactory;
use IvyForms\Factory\FieldOptions\FieldOptionsFactory;
use IvyForms\Repository\Field\FieldRepositoryInterface;
use IvyForms\Repository\FieldOptions\FieldOptionsRepositoryInterface;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\Common\Helpers\FieldHelper;
use IvyForms\Services\Media\ImageService;

/**
 * @SuppressWarnings("TooManyPublicMethods")
 */
class FieldService
{
    private FieldRepositoryInterface $fieldRepository;
    private FieldOptionsRepositoryInterface $fieldOptionsRepository;
    private ImageService $imageService;

    // Constructor injection for repositories only via PHP-DI
    public function __construct(
        FieldRepositoryInterface $fieldRepository,
        FieldOptionsRepositoryInterface $fieldOptionsRepository,
        ImageService $imageService
    ) {
        $this->fieldRepository = $fieldRepository;
        $this->fieldOptionsRepository = $fieldOptionsRepository;
        $this->imageService = $imageService;
    }

    /**
     * Create a new field.
     *
     * @param Field $fieldData
     * @return int Field ID
     * @throws InvalidArgumentException If the field data is invalid
     */
    public function addField(Field $fieldData): int
    {
        if (empty($fieldData->getFormId())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['form_id_required']
            );
        }
        return $this->fieldRepository->add($fieldData);
    }

    /**
     * Update an existing field.
     *
     * @param int $fieldId
     * @param Field $fieldData
     * @return bool
     * @throws InvalidArgumentException If the field data is invalid
     */
    public function updateField(int $fieldId, Field $fieldData): bool
    {
        if (empty($fieldData->getId())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['field_id_required']
            );
        }
        return $this->fieldRepository->update($fieldId, $fieldData);
    }

    /**
     * Delete a field by its ID (removes options first).
     *
     * @param int $fieldId
     * @return int
     * @throws QueryExecutionException
     */
    public function deleteField(int $fieldId): int
    {
        $this->fieldOptionsRepository->deleteByFieldIds([$fieldId]);
        return $this->fieldRepository->delete($fieldId);
    }

    /**
     * Batch delete fields by IDs.
     *
     * @param int[] $fieldIds
     * @return int Number of deleted rows
     * @throws QueryExecutionException
     */
    public function deleteFields(array $fieldIds): int
    {
        $ids = array_values(array_filter(array_map('intval', $fieldIds), static fn($val) => $val > 0));
        if (empty($ids)) {
            return 0;
        }
        $this->fieldOptionsRepository->deleteByFieldIds($ids);
        return $this->fieldRepository->deleteMany($ids);
    }

    /**
     * Get all fields by form ID.
     * @param int $id
     * @return Field[]|null
     */
    public function getAllFields(int $id): ?array
    {
        return $this->fieldRepository->getFieldsById($id);
    }

    /**
     * Get multiple fields by their IDs in a single query.
     * @param int[] $fieldIds
     * @return array<int, object> Indexed by field ID
     */
    public function getFieldsByIds(array $fieldIds): array
    {
        if (empty($fieldIds)) {
            return [];
        }
        return $this->fieldRepository->getFieldsByIds($fieldIds);
    }

    /**
     * Delete all fields for multiple form IDs.
     * @param int[] $formIds
     * @return int
     * @throws InvalidArgumentException|QueryExecutionException
     */
    public function deleteFieldsByFormIds(array $formIds): int
    {
        if (empty($formIds)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['no_form_ids_provided']
            );
        }
        $this->fieldOptionsRepository->deleteByFieldIdsByColumnValues('formId', $formIds);
        return $this->fieldRepository->deleteManyByForeignKeyValues($formIds, 'formId');
    }

    /**
     * Delete fields by single form ID.
     * @param int $formId
     * @return int
     * @throws QueryExecutionException
     */
    public function deleteFieldsByFormId(int $formId): int
    {
        $this->fieldOptionsRepository->deleteByFieldIdsByColumnValues('formId', [$formId]);
        return $this->fieldRepository->deleteOneByForeignKeyValue($formId, 'formId');
    }

    /**
     * Duplicate all fields from one form to another.
     * @param int $originalFormId
     * @param int $newFormId
     * @return void
     * @throws InvalidArgumentException|ValidationException
     */
    public function duplicateFields(int $originalFormId, int $newFormId): void
    {
        $fields = $this->getAllFields($originalFormId);
        $fieldIdMap = [];
        foreach ($fields as $field) {
            $fieldData = $field->toArray();
            $oldId = $fieldData['id'];
            unset($fieldData['id']);
            $fieldData['formId'] = $newFormId;
            FieldHelper::remapParentIdForDuplication($fieldData, $fieldIdMap);
            $newFieldId = $this->addField(FieldFactory::create($fieldData));
            $fieldIdMap[$oldId] = $newFieldId;
            // Duplicate field options using helper
            if (FieldHelper::fieldTypeHasOptions($field->getType())) {
                FieldHelper::duplicateFieldOptions($oldId, $newFieldId, $this->fieldOptionsRepository);
            }
        }
    }

    /**
     * Save fields with their options (radio/checkbox/select) for a form.
     * @param array<int, array<string,mixed>> $fields
     * @param int $formId
     * @return void
     * @throws ValidationException|InvalidArgumentException
     */
    public function saveFieldsWithOptions(array $fields, int $formId): void
    {
        $parentMap = [];
        foreach ($fields as $fieldData) {
            $fieldData['formId'] = $fieldData['formId'] ?? $formId;
            // Resolve parentId using fieldIndex if available
            FieldHelper::resolveParentId($fieldData, $parentMap);
            // Process htmlContent / rich text default values to upload base64 images
            FieldHelper::processFieldHtmlContent($fieldData, $this->imageService);
            FieldHelper::processRichTextDefaultValue($fieldData, $this->imageService);
            $field = FieldFactory::create($fieldData);
            $fieldId = $this->addField($field);
            // If this is a parent field (no parentId), store its DB id in the map
            if ($field->getParentId() === null) {
                $parentMap[$field->getIndex()] = $fieldId;
            }
            if (FieldHelper::fieldTypeHasOptions($field->getType()) && !empty($fieldData['fieldOptions'])) {
                foreach ($fieldData['fieldOptions'] as $optionData) {
                    // Always overwrite fieldId with the actual DB id of the field
                    $optionData['fieldId'] = $fieldId;
                    $option = FieldOptionsFactory::create($optionData);
                    $this->fieldOptionsRepository->add($option);
                }
            }
        }
    }

    /**
     * Update (and create/delete) fields and their options for a form.
     * @param array<int, array<string,mixed>> $fields
     * @param int $formId
     * @return int[] Submitted field IDs
     * @throws ValidationException|InvalidArgumentException
     */
    public function updateFieldsWithOptions(array $fields, int $formId): array
    {
        $submittedFieldIds = [];
        $parentMap = [];
        foreach ($fields as $fieldData) {
            $fieldData['formId'] = $fieldData['formId'] ?? $formId;
            FieldHelper::resolveParentId($fieldData, $parentMap);
            // Process htmlContent / rich text default values to upload base64 images
            FieldHelper::processFieldHtmlContent($fieldData, $this->imageService);
            FieldHelper::processRichTextDefaultValue($fieldData, $this->imageService);
            $field = FieldFactory::create($fieldData);
            $fieldId = $field->getId();

            if ($fieldId === 0) { // new field
                $newFieldId = $this->addField($field);
                if ($field->getParentId() === null) {
                    $parentMap[$field->getIndex()] = $newFieldId;
                }
                $submittedFieldIds[] = $newFieldId;
                if (empty($fieldData['fieldOptions'])) {
                    continue;
                }
                $newOptions = [];
                foreach ($fieldData['fieldOptions'] as $optionData) {
                    $optionData['fieldId'] = $newFieldId;
                    $newOptions[] = FieldOptionsFactory::create($optionData);
                }
                $this->fieldOptionsRepository->addMany($newOptions);
                continue;
            }

            // existing field
            $this->updateField($fieldId, $field);
            $submittedFieldIds[] = $fieldId;
            if (empty($fieldData['fieldOptions'])) {
                continue;
            }
            foreach ($fieldData['fieldOptions'] as &$optionData) {
                $optionData['fieldId'] = $fieldId;
            }
            unset($optionData);
            $this->batchProcessFieldOptions($fieldId, $fieldData);
        }
        return $submittedFieldIds;
    }

    /**
     * Fetch all fields (with options for radio/checkbox/select types).
     * @param int $formId
     * @return array<int, array<string,mixed>>
     * @throws ValidationException|QueryExecutionException
     */
    public function getAllFieldsWithOptions(int $formId): array
    {
        $fields = $this->fieldRepository->getFieldsById($formId);
        $fieldsWithOptions = [];
        foreach ($fields as $field) {
            $fieldArr = $field->toArray();
            if (FieldHelper::fieldTypeHasOptions($field->getType())) {
                $options = $this->fieldOptionsRepository->getByFieldId($field->getId());
                $fieldArr['fieldOptions'] = array_map(static fn($option) => $option->toArray(), $options);
            }
            $fieldsWithOptions[] = $fieldArr;
        }
        return $fieldsWithOptions;
    }

    /**
     * Collect existing field IDs for a form.
     * @param int $formId
     * @return int[]
     */
    public function collectExistingFieldIds(int $formId): array
    {
        $existingFields = $this->getAllFields($formId);
        return array_map(static fn($field) => $field->getId(), $existingFields ?? []);
    }

    /**
     * Batch process field options: add, update, and delete as needed.
     * @param int $fieldId
     * @param array<string, mixed> $fieldData
     * @throws ValidationException
     */
    public function batchProcessFieldOptions(
        int $fieldId,
        array $fieldData
    ): void {
        $existingOptions = $this->fieldOptionsRepository->getByFieldId($fieldId);
        $existingOptionIds = array_map(static fn($opt) => $opt->getId(), $existingOptions);
        $partition = FieldHelper::partitionFieldOptions($fieldData, $existingOptionIds);
        // Add new options
        $newOptionIds = $this->fieldOptionsRepository->addMany($partition['newOptions']);
        // Update existing options
        $this->fieldOptionsRepository->updateMany($partition['updateOptions']);
        // Determine submitted option IDs (existing updated + newly created)
        $submittedOptionIds = array_merge($partition['submittedOptionIds'], $newOptionIds);
        // Delete orphaned (those that existed but were not resubmitted)
        $orphanedOptionIds = array_diff($existingOptionIds, $submittedOptionIds);
        if (!empty($orphanedOptionIds)) {
            $this->fieldOptionsRepository->deleteByOptionIds($orphanedOptionIds);
        }
    }

    /**
     * Validate that all fields have allowed types.
     * @param Field[]|mixed[] $formFields
     * @throws InvalidArgumentException
     */
    public function validateFieldsType(array $formFields): void
    {
        FieldType::validateFields($formFields);
    }
}
