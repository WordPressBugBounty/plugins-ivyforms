<?php

namespace IvyForms\ValueObjects\GoogleSheets;

use DateTime;
use IvyForms\Common\Exceptions\ValidationException;

/**
 * Google Sheet integration value object.
 */
final class GoogleSheetIntegration
{
    public ?int $id;
    public int $formId;
    public string $spreadsheetId;
    public string $spreadsheetName;
    public string $worksheetName;
    /** @var list<array{column: string, value: string}> */
    public array $fieldMapping;
    /** @var array<string, mixed> */
    public array $smartLogic;
    public bool $enabled;
    public ?string $lastStatus;
    public ?DateTime $lastRunAt;
    public ?DateTime $createdAt;
    public ?DateTime $updatedAt;

    /**
     * @param list<array{column: string, value: string}> $fieldMapping
     * @param array<string, mixed> $smartLogic
     * @throws ValidationException
     *
     * @SuppressWarnings("ExcessiveParameterList")
     */
    public function __construct(
        ?int $id,
        int $formId,
        string $spreadsheetId,
        string $spreadsheetName,
        string $worksheetName,
        array $fieldMapping,
        array $smartLogic,
        bool $enabled,
        ?string $lastStatus,
        ?DateTime $lastRunAt,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->formId = $this->validateFormId($formId);
        $this->spreadsheetId = $this->validateRequiredString($spreadsheetId, 'spreadsheet_id', 255);
        $this->spreadsheetName = $spreadsheetName;
        $this->worksheetName = $this->validateRequiredString($worksheetName, 'worksheet_name', 255);
        $this->fieldMapping = $fieldMapping;
        $this->smartLogic = $smartLogic;
        $this->enabled = $enabled;
        $this->lastStatus = $lastStatus;
        $this->lastRunAt = $lastRunAt;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    /**
     * @throws ValidationException
     */
    private function validateFormId(int $formId): int
    {
        if ($formId <= 0) {
            throw new ValidationException(__('Form ID must be a positive integer.', 'ivyforms'));
        }

        return $formId;
    }

    /**
     * @throws ValidationException
     */
    private function validateRequiredString(string $value, string $fieldName, int $maxLength): string
    {
        $value = trim($value);

        if ($value === '') {
            throw new ValidationException(
                sprintf(
                    /* translators: %s: field name */
                    __('%s cannot be empty.', 'ivyforms'),
                    $fieldName
                )
            );
        }

        if (strlen($value) > $maxLength) {
            throw new ValidationException(
                sprintf(
                    /* translators: 1: field name, 2: max length */
                    __('%1$s must be at most %2$d characters.', 'ivyforms'),
                    $fieldName,
                    $maxLength
                )
            );
        }

        return $value;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFormId(): int
    {
        return $this->formId;
    }

    public function getSpreadsheetId(): string
    {
        return $this->spreadsheetId;
    }

    public function getSpreadsheetName(): string
    {
        return $this->spreadsheetName;
    }

    public function getWorksheetName(): string
    {
        return $this->worksheetName;
    }

    /**
     * @return list<array{column: string, value: string}>
     */
    public function getFieldMapping(): array
    {
        return $this->fieldMapping;
    }

    /**
     * @return array<string, mixed>
     */
    public function getSmartLogic(): array
    {
        return $this->smartLogic;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getLastStatus(): ?string
    {
        return $this->lastStatus;
    }

    public function getLastRunAt(): ?DateTime
    {
        return $this->lastRunAt;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'               => $this->getId(),
            'form_id'          => $this->getFormId(),
            'spreadsheet_id'   => $this->getSpreadsheetId(),
            'spreadsheet_name' => $this->getSpreadsheetName(),
            'worksheet_name'   => $this->getWorksheetName(),
            'field_mapping'    => $this->getFieldMapping(),
            'smart_logic'      => $this->getSmartLogic(),
            'enabled'          => $this->isEnabled(),
            'last_status'      => $this->getLastStatus(),
            'last_run_at'      => $this->getLastRunAt() ? $this->getLastRunAt()->format('Y-m-d H:i:s') : null,
            'created_at'       => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d H:i:s') : null,
            'updated_at'       => $this->getUpdatedAt() ? $this->getUpdatedAt()->format('Y-m-d H:i:s') : null,
        ];
    }
}
