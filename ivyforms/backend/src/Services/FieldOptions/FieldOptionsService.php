<?php

namespace IvyForms\Services\FieldOptions;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Repository\FieldOptions\FieldOptionsRepositoryInterface;
use IvyForms\Entity\FieldOptions\FieldOptions;

class FieldOptionsService
{
    /**
     * @var FieldOptionsRepositoryInterface
     */
    private FieldOptionsRepositoryInterface $fieldOptionsRepository;

    /**
     * @param FieldOptionsRepositoryInterface $fieldOptionsRepository
     */
    public function __construct(FieldOptionsRepositoryInterface $fieldOptionsRepository)
    {
        $this->fieldOptionsRepository = $fieldOptionsRepository;
    }

    /**
     * @param FieldOptions $option
     * @return int
     */
    public function addOption(FieldOptions $option): int
    {
        return $this->fieldOptionsRepository->add($option);
    }

    /**
     * Batch add FieldOptions entities.
     * @param FieldOptions[] $options
     * @return array<int> Inserted IDs
     */
    public function addOptions(array $options): array
    {
        return $this->fieldOptionsRepository->addMany($options);
    }

    /**
     * @param FieldOptions $option
     * @return bool
     */
    public function updateOption(FieldOptions $option): bool
    {
        return $this->fieldOptionsRepository->update($option->getId(), $option);
    }

    /**
     * Batch update FieldOptions entities.
     * @param FieldOptions[] $options
     * @return int Number of updated rows
     */
    public function updateOptions(array $options): int
    {
        return $this->fieldOptionsRepository->updateMany($options);
    }

    /**
     * @param int $fieldId
     * @return bool
     */
    public function deleteOptionsByFieldId(int $fieldId): bool
    {
        return $this->fieldOptionsRepository->deleteByFieldId($fieldId);
    }

    /**
     * Delete all options for multiple field IDs in one query.
     * @param int[] $fieldIds
     * @return int Number of deleted options
     * @throws InvalidArgumentException
     */
    public function deleteOptionsByFieldIds(array $fieldIds): int
    {
        if (empty($fieldIds)) {
            return 0;
        }
        return $this->fieldOptionsRepository->deleteByFieldIds($fieldIds);
    }

    /**
     * @param int $fieldId
     * @return array<FieldOptions>
     */
    public function getByFieldId(int $fieldId): array
    {
        return $this->fieldOptionsRepository->getByFieldId($fieldId);
    }
}
