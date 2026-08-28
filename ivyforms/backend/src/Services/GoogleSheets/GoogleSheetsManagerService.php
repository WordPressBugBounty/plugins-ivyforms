<?php

namespace IvyForms\Services\GoogleSheets;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Entity\GoogleSheets\GoogleSheetIntegration;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\Repository\GoogleSheets\GoogleSheetsRepositoryInterface;
use IvyForms\ValueObjects\GoogleSheets\GoogleSheetIntegration as GoogleSheetIntegrationValueObject;

/**
 * Google Sheets integration CRUD manager.
 */
class GoogleSheetsManagerService
{
    private GoogleSheetsRepositoryInterface $repository;

    public function __construct(GoogleSheetsRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param list<array{column: string, value: string}> $fieldMapping
     * @param array<string, mixed> $smartLogic
     *
     * @SuppressWarnings("BooleanArgumentFlag")
     */
    public function createIntegration(
        int $formId,
        string $spreadsheetId,
        string $spreadsheetName,
        string $worksheetName,
        array $fieldMapping = [],
        array $smartLogic = [],
        bool $enabled = true
    ): GoogleSheetIntegration {
        $this->validateIntegrationLimit($formId);

        $valueObject = new GoogleSheetIntegrationValueObject(
            null,
            $formId,
            $spreadsheetId,
            $spreadsheetName,
            $worksheetName,
            $fieldMapping,
            $smartLogic,
            $enabled,
            null,
            null
        );

        $integration = new GoogleSheetIntegration($valueObject);
        $integrationId = $this->repository->add($integration);
        $integration->setId($integrationId);

        return $integration;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateIntegration(int $id, array $data): GoogleSheetIntegration
    {
        $integration = $this->repository->findById($id);

        if (!$integration) {
            throw new InvalidArgumentException('Google Sheets integration not found');
        }

        if (isset($data['spreadsheet_id'])) {
            $integration->setSpreadsheetId((string) $data['spreadsheet_id']);
        }

        if (isset($data['spreadsheet_name'])) {
            $integration->setSpreadsheetName((string) $data['spreadsheet_name']);
        }

        if (isset($data['worksheet_name'])) {
            $integration->setWorksheetName((string) $data['worksheet_name']);
        }

        if (isset($data['field_mapping'])) {
            $integration->setFieldMapping((array) $data['field_mapping']);
        }

        if (isset($data['smart_logic'])) {
            $integration->setSmartLogic((array) $data['smart_logic']);
        }

        if (isset($data['enabled'])) {
            $integration->setEnabled((bool) $data['enabled']);
        }

        $this->repository->update($integration->getId(), $integration);

        return $integration;
    }

    public function deleteIntegration(int $id): bool
    {
        return $this->repository->deleteById($id);
    }

    public function updateStatus(int $id): GoogleSheetIntegration
    {
        $integration = $this->repository->findById($id);

        if (!$integration) {
            throw new InvalidArgumentException('Google Sheets integration not found');
        }

        $integration->setEnabled(!$integration->isEnabled());
        $this->repository->update($integration->getId(), $integration);

        return $integration;
    }

    public function getIntegration(int $id): ?GoogleSheetIntegration
    {
        return $this->repository->findById($id);
    }

    /**
     * @return array<GoogleSheetIntegration>
     */
    public function getIntegrationsByFormId(int $formId): array
    {
        return $this->repository->findByFormId($formId);
    }

    public function getMaxIntegrationsPerForm(): int
    {
        return (int) apply_filters('ivyforms/google_sheets/max_per_form', 1);
    }

    protected function validateIntegrationLimit(int $formId, ?int $excludeId = null): void
    {
        $maxPerForm = $this->getMaxIntegrationsPerForm();
        $count = $this->repository->countByFormId($formId);

        if ($excludeId !== null) {
            $count--;
        }

        if ($count >= $maxPerForm) {
            throw new InvalidArgumentException(
                str_replace(
                    '{limit}',
                    (string) $maxPerForm,
                    BackendStrings::getIntegrationsStrings()['google_sheets_limit_reached']
                )
            );
        }
    }
}
