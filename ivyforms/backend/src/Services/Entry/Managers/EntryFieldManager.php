<?php

namespace IvyForms\Services\Entry\Managers;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Helpers\EntryHelper;
use IvyForms\Common\Helpers\FieldHelper;
use IvyForms\Entity\EntryField\EntryField;
use IvyForms\Factory\EntryField\EntryFieldFactory;
use IvyForms\Repository\Entry\EntryRepositoryInterface;
use IvyForms\Repository\EntryField\EntryFieldRepositoryInterface;

/**
 * Manages EntryField repository operations.
 */
class EntryFieldManager
{
    private EntryFieldRepositoryInterface $entryFieldRepository;
    private EntryRepositoryInterface $entryRepository;
    private EntryFieldValueManager $valueManager;

    public function __construct(
        EntryFieldRepositoryInterface $entryFieldRepository,
        EntryRepositoryInterface $entryRepository,
        EntryFieldValueManager $valueManager
    ) {
        $this->entryFieldRepository = $entryFieldRepository;
        $this->entryRepository = $entryRepository;
        $this->valueManager = $valueManager;
    }

    /**
     * Fetch entries along with their associated fields.
     * Automatically formats field values for display.
     *
     * @param array<int, array<string, mixed>> $entries
     * @param bool $skipFormatting Whether to skip formatting and return raw values (used for result tables).
     * @return array<mixed>
     * @SuppressWarnings(PHPMD)
     */
    public function getEntryFields(array $entries, bool $skipFormatting = false): array
    {
        $entryFields = $this->entryRepository->getEntryFields($entries);
        return $this->valueManager->formatForFrontend($entryFields, $skipFormatting);
    }

    /**
     * Check if a value already exists in entry fields for a specific field.
     *
     * @param int $formId
     * @param int $fieldId
     * @param string $value
     * @return bool
     */
    public function checkDuplicateValue(
        int $formId,
        int $fieldId,
        string $value,
        ?int $excludeEntryId = null
    ): bool {
        return $this->entryFieldRepository->checkDuplicateValue(
            $formId,
            $fieldId,
            $value,
            $excludeEntryId
        );
    }

    /**
     * Add entry fields to the repository.
     *
     * Resolves all field values in PHP, then persists with a single multi-row INSERT.
     *
     * @param array<int, object> $formFields
     * @param int $entryId
     * @param array<mixed> $submissionData
     * @throws ValidationException
     */
    public function addEntryFields(array $formFields, int $entryId, array $submissionData): void
    {
        $parentChildrenMap = EntryHelper::buildParentChildrenMap($formFields);
        $parentSubfieldKeys = EntryHelper::buildParentSubfieldKeys(
            $parentChildrenMap,
            $submissionData,
            $formFields
        );

        /** @var list<array{entryField: EntryField, field: object}> $created */
        $created = [];

        foreach ($formFields as $field) {
            // Skip security/CAPTCHA fields - their tokens are only needed for verification, not storage
            $fieldType = $field->getType();
            if (FieldHelper::isSecurityField($fieldType)) {
                continue;
            }

            /** @var bool $shouldSkip */
            $shouldSkip = apply_filters('ivyforms/entry/field/should_skip', false, $field);
            if ($shouldSkip) {
                continue;
            }

            $value = $this->valueManager->resolveFieldValue(
                $field,
                $submissionData,
                $parentChildrenMap,
                $parentSubfieldKeys
            );

            $entryFieldData = [
                'entryId'   => $entryId,
                'fieldId'    => $field->getId(),
                'fieldValue' => $value,
            ];

            /**
             * Filters the entry field data before creating the entry field object.
             * Allows modification of entry data before persistence.
             *
             * @since 0.1.0
             *
             * @param mixed[] $entryFieldData The entry field data array.
             * @param object $field The field object.
             * @param mixed[] $submissionData The submission data array.
             * @return mixed[] The modified entry field data array.
             */
            $entryFieldData = apply_filters(
                'ivyforms/entry/field/before_create',
                $entryFieldData,
                $field,
                $submissionData
            );

            /**
             * Filters the entry field data for a specific field type before creating the entry field object.
             * Allows dynamic hook registration per field type
             *
             * @since 0.1.0
             *
             * @param mixed[] $entryFieldData The entry field data array.
             * @param object $field The field object.
             * @param mixed[] $submissionData The submission data array.
             * @return mixed[] The modified entry field data array.
             */
            $entryFieldData = apply_filters(
                "ivyforms/entry/field/before_create/{$field->getType()}",
                $entryFieldData,
                $field,
                $submissionData
            );

            $created[] = [
                'entryField' => EntryFieldFactory::create($entryFieldData),
                'field' => $field,
            ];
        }

        if ($created === []) {
            return;
        }

        $this->entryFieldRepository->batchInsert(
            array_column($created, 'entryField')
        );

        foreach ($created as $item) {
            $entryField = $item['entryField'];
            $field = $item['field'];

            /**
             * Action fired after an entry field has been saved to the database.
             * Allows post-processing or logging of entry field creation.
             *
             * @since 0.1.0
             *
             * @param object $entryField The created entry field object.
             * @param object $field The field object.
             * @param mixed[] $submissionData The submission data array.
             */
            do_action(
                'ivyforms/entry/field/after_create',
                $entryField,
                $field,
                $submissionData
            );

            /**
             * Action fired after an entry field of a specific type has been saved to the database.
             * Allows dynamic hook registration per field type
             *
             * @since 0.1.0
             *
             * @param object $entryField The created entry field object.
             * @param object $field The field object.
             * @param mixed[] $submissionData The submission data array.
             */
            do_action(
                "ivyforms/entry/field/after_create/{$field->getType()}",
                $entryField,
                $field,
                $submissionData
            );
        }
    }
}
