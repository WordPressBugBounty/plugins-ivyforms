<?php

namespace IvyForms\Entity\GoogleSheets;

use DateTime;
use IvyForms\ValueObjects\GoogleSheets\GoogleSheetIntegration as GoogleSheetIntegrationValueObject;

/**
 * Google Sheet integration entity.
 */
class GoogleSheetIntegration
{
    private GoogleSheetIntegrationValueObject $integration;

    public function __construct(GoogleSheetIntegrationValueObject $integration)
    {
        $this->integration = $integration;
    }

    public function getId(): ?int
    {
        return $this->integration->getId();
    }

    public function getFormId(): int
    {
        return $this->integration->getFormId();
    }

    public function getSpreadsheetId(): string
    {
        return $this->integration->getSpreadsheetId();
    }

    public function getSpreadsheetName(): string
    {
        return $this->integration->getSpreadsheetName();
    }

    public function getWorksheetName(): string
    {
        return $this->integration->getWorksheetName();
    }

    /**
     * @return list<array{column: string, value: string}>
     */
    public function getFieldMapping(): array
    {
        return $this->integration->getFieldMapping();
    }

    /**
     * @return array<string, mixed>
     */
    public function getSmartLogic(): array
    {
        return $this->integration->getSmartLogic();
    }

    public function isEnabled(): bool
    {
        return $this->integration->isEnabled();
    }

    public function getLastStatus(): ?string
    {
        return $this->integration->getLastStatus();
    }

    public function getLastRunAt(): ?DateTime
    {
        return $this->integration->getLastRunAt();
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->integration->getCreatedAt();
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->integration->getUpdatedAt();
    }

    public function setId(int $id): void
    {
        $this->integration->id = $id;
    }

    public function setSpreadsheetId(string $spreadsheetId): void
    {
        $this->integration->spreadsheetId = $spreadsheetId;
    }

    public function setSpreadsheetName(string $spreadsheetName): void
    {
        $this->integration->spreadsheetName = $spreadsheetName;
    }

    public function setWorksheetName(string $worksheetName): void
    {
        $this->integration->worksheetName = $worksheetName;
    }

    /**
     * @param list<array{column: string, value: string}> $fieldMapping
     */
    public function setFieldMapping(array $fieldMapping): void
    {
        $this->integration->fieldMapping = $fieldMapping;
    }

    /**
     * @param array<string, mixed> $smartLogic
     */
    public function setSmartLogic(array $smartLogic): void
    {
        $this->integration->smartLogic = $smartLogic;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->integration->enabled = $enabled;
    }

    public function setLastStatus(?string $lastStatus): void
    {
        $this->integration->lastStatus = $lastStatus;
    }

    public function setLastRunAt(?DateTime $lastRunAt): void
    {
        $this->integration->lastRunAt = $lastRunAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->integration->toArray();
    }
}
