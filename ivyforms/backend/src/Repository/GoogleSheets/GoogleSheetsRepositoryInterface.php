<?php

namespace IvyForms\Repository\GoogleSheets;

use IvyForms\Entity\GoogleSheets\GoogleSheetIntegration;
use IvyForms\Repository\BaseRepositoryInterface;

/**
 * Google Sheets repository interface.
 */
interface GoogleSheetsRepositoryInterface extends BaseRepositoryInterface
{
    public function findById(int $id): ?GoogleSheetIntegration;

    /**
     * @return array<GoogleSheetIntegration>
     */
    public function findByFormId(int $formId): array;

    /**
     * @return array<GoogleSheetIntegration>
     */
    public function findEnabledByFormId(int $formId): array;

    public function countByFormId(int $formId): int;

    public function deleteById(int $id): bool;

    public function deleteByFormId(int $formId): bool;

    public function updateSyncStatus(int $id, string $status, ?string $lastRunAt = null): bool;
}
