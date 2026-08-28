<?php

namespace IvyForms\Services\GoogleSheets;

use IvyForms\Entity\GoogleSheets\GoogleSheetIntegration;
use IvyForms\Services\GoogleSheets\Helpers\GoogleSheetsFieldKeyHelper;
use IvyForms\Repository\GoogleSheets\GoogleSheetsRepositoryInterface;
use IvyForms\Services\Placeholder\PlaceholderService;
use IvyForms\Services\Submission\SubmissionDataFormatterService;

/**
 * Dispatches form submissions to Google Sheets with retry logic.
 */
class GoogleSheetsDispatcherService
{
    private GoogleSheetsRepositoryInterface $repository;
    private GoogleSheetsService $googleSheetsService;
    private SubmissionDataFormatterService $submissionFormatter;
    private int $maxRetries = 2;

    public function __construct(
        GoogleSheetsRepositoryInterface $repository,
        GoogleSheetsService $googleSheetsService,
        SubmissionDataFormatterService $submissionFormatter
    ) {
        $this->repository = $repository;
        $this->googleSheetsService = $googleSheetsService;
        $this->submissionFormatter = $submissionFormatter;
    }

    /**
     * @param array<string, mixed> $submissionData
     * @param array<mixed> $formFields
     */
    public function dispatchForSubmission(
        int $formId,
        array $submissionData,
        array $formFields
    ): void {
        if (!$this->googleSheetsService->isConnected()) {
            return;
        }

        $integrations = $this->repository->findEnabledByFormId($formId);

        if ($integrations === []) {
            return;
        }

        $formatted = $this->submissionFormatter->formatForIntegrations($submissionData, $formFields);
        $integrationFieldData = array_merge($formatted['fields'], $formatted['user_inputs']);
        // UI placeholders use type_fieldIndex (e.g. {{text_1}}); buildFieldData uses the same keys.
        $fieldData = array_merge(
            PlaceholderService::buildFieldData($formFields, $submissionData),
            $integrationFieldData,
            GoogleSheetsFieldKeyHelper::buildFieldIndexAliases($formFields, $integrationFieldData)
        );
        $fieldLabels = [];

        foreach ($formatted['field_metadata'] as $key => $metadata) {
            $fieldLabels[$key] = $metadata['label'];
        }

        $generalData = PlaceholderService::buildGeneralData(
            (int) ($submissionData['id'] ?? 0),
            $submissionData
        );

        foreach ($integrations as $integration) {
            if (!$this->shouldProcess($integration->getSmartLogic(), $submissionData, $formFields)) {
                continue;
            }

            $this->syncIntegration(
                $integration,
                $fieldData,
                $generalData,
                $fieldLabels
            );
        }
    }

    /**
     * @param array<string, mixed> $smartLogic
     * @param array<string, mixed> $submissionData
     * @param array<mixed> $formFields
     */
    private function shouldProcess(array $smartLogic, array $submissionData, array $formFields): bool
    {
        /**
         * @param bool $shouldProcess
         * @param array<string, mixed> $smartLogic
         * @param array<string, mixed> $submissionData
         * @param array<mixed> $formFields
         */
        return (bool) apply_filters(
            'ivyforms/google_sheets/should_process',
            true,
            $smartLogic,
            $submissionData,
            $formFields
        );
    }

    /**
     * @param array<string, mixed> $fieldData
     * @param array<string, string|int> $generalData
     * @param array<string, string> $fieldLabels
     */
    private function syncIntegration(
        GoogleSheetIntegration $integration,
        array $fieldData,
        array $generalData,
        array $fieldLabels
    ): void {
        $attempt = 0;
        $lastError = null;

        while ($attempt <= $this->maxRetries) {
            $result = $this->appendIntegrationRow($integration, $fieldData, $generalData, $fieldLabels);

            if ($result['success']) {
                $this->updateIntegrationStatus($integration, 'success');
                return;
            }

            $lastError = $result;
            $attempt++;
        }

        $this->updateIntegrationStatus(
            $integration,
            'failed: ' . ($lastError['message'] ?? 'Unknown error')
        );
    }

    /**
     * @param array<string, mixed> $fieldData
     * @param array<string, string|int> $generalData
     * @param array<string, string> $fieldLabels
     * @return array{success: bool, message: string}
     */
    private function appendIntegrationRow(
        GoogleSheetIntegration $integration,
        array $fieldData,
        array $generalData,
        array $fieldLabels
    ): array {
        $headers = $this->googleSheetsService->listColumns(
            $integration->getSpreadsheetId(),
            $integration->getWorksheetName()
        );

        if ($headers === []) {
            $headers = array_map(static function ($mapping) {
                return $mapping['column'];
            }, $integration->getFieldMapping());
        }

        $mappingByColumn = [];
        foreach ($integration->getFieldMapping() as $mapping) {
            $mappingByColumn[$mapping['column']] = $mapping['value'];
        }

        $rowValues = [];
        foreach ($headers as $header) {
            $template = $mappingByColumn[$header] ?? '';
            $rowValues[] = $template !== ''
                ? PlaceholderService::replacePlaceholders($template, $fieldData, $generalData, $fieldLabels)
                : '';
        }

        return $this->googleSheetsService->appendRow(
            $integration->getSpreadsheetId(),
            $integration->getWorksheetName(),
            $rowValues
        );
    }

    private function updateIntegrationStatus(GoogleSheetIntegration $integration, string $status): void
    {
        $this->repository->updateSyncStatus($integration->getId(), $status);
    }
}
