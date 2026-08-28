<?php

namespace IvyForms\Services\Field;

use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Entity\Field\Field;
use IvyForms\Repository\EntryField\EntryFieldRepositoryInterface;

/**
 * Validates no-duplicates field values against stored submissions.
 */
class FieldDuplicateValidationService
{
    private EntryFieldRepositoryInterface $entryFieldRepository;

    public function __construct(EntryFieldRepositoryInterface $entryFieldRepository)
    {
        $this->entryFieldRepository = $entryFieldRepository;
    }

    /**
     * @param Field[] $formFields
     * @param array<string,mixed> $submissionData
     * @return array{0: bool, 1: array<int|string,bool>}
     */
    public function checkDuplicateFieldValues(
        array $formFields,
        array $submissionData,
        int $formId,
        ?string $pageId = null,
        ?int $excludeEntryId = null
    ): array {
        $duplicateErrors = [];
        $isDuplicate = false;
        $fieldValues = [];

        foreach ($formFields as $field) {
            if (!$this->isFieldOnPage($field, $pageId)) {
                continue;
            }
            $fid = $field->getId();
            $fidStr = (string) $fid;
            if (isset($submissionData[$fidStr])) {
                $fieldValues[$fidStr] = $submissionData[$fidStr];
            }
            if (!$this->shouldCheckDuplicateValue($field, $fieldValues, $fidStr)) {
                continue;
            }
            $rawVal = $fieldValues[$fidStr];
            $baseNormalized = Sanitizer::normalizeFieldValue($rawVal);
            $val = apply_filters(
                'ivyforms/field/normalize_value_for_duplicate_check',
                $baseNormalized,
                $field,
                $rawVal
            );

            $isFieldDuplicate = $val !== $baseNormalized
                ? $this->entryFieldRepository->checkDuplicateValueNormalized(
                    $formId,
                    $fid,
                    (string) $val,
                    static function (string $stored) use ($field): string {
                        return (string) apply_filters(
                            'ivyforms/field/normalize_value_for_duplicate_check',
                            Sanitizer::normalizeFieldValue($stored),
                            $field,
                            $stored
                        );
                    },
                    $excludeEntryId
                )
                : $this->entryFieldRepository->checkDuplicateValue($formId, $fid, (string) $val, $excludeEntryId);

            if ($isFieldDuplicate) {
                $duplicateErrors[$fidStr] = true;
                $isDuplicate = true;
            }
        }

        return [$isDuplicate, $duplicateErrors];
    }

    private function isFieldOnPage(Field $field, ?string $pageId): bool
    {
        return $pageId === null || $field->getPageId() === $pageId;
    }

    /**
     * @param array<string, mixed> $fieldValues
     */
    private function shouldCheckDuplicateValue(Field $field, array $fieldValues, string $fidStr): bool
    {
        if (!$field->getFieldAdvancedSettings()->isNoDuplicates()) {
            return false;
        }
        if (!array_key_exists($fidStr, $fieldValues)) {
            return false;
        }

        $value = $fieldValues[$fidStr];

        return $value !== '' && $value !== null;
    }
}
