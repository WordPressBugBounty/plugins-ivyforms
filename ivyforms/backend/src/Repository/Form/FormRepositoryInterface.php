<?php

namespace IvyForms\Repository\Form;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Repository\BaseRepositoryInterface;

interface FormRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Check if a form exists in the database.
     *
     * @param int $formId
     * @return bool
     */
    public function exists(int $formId): bool;

    /**
     * @param list<int> $formIds
     * @return list<int> Subset of $formIds that exist in the database.
     */
    public function filterExistingIds(array $formIds): array;

    /**
     * @return list<int>
     */
    public function getAllIds(): array;

    /**
     * @return list<array{id:int, name:string}>
     */
    public function getIdNamePairs(): array;

    /**
     * @param list<int> $formIds
     * @return array<int, string> Map of form id => name.
     */
    public function getNamesByIds(array $formIds): array;

    /**
     * @param int $formId
     * @param string $value
     *
     * @return bool
     */
    public function updateFormStarred(int $formId, string $value): bool;

    /**
     * @param int $formId
     * @param string $value
     *
     * @return bool
     */
    public function updateFormStatus(int $formId, string $value): bool;

    /**
     * Get the count of form filters.
     *
     * @return array<string, int>
     */
    public function getFilterCount(): array;

    /**
     * Get the earliest form creation timestamp.
     *
     * @return string|null DATETIME as stored in the database
     */
    public function getEarliestCreatedAt(): ?string;
}
