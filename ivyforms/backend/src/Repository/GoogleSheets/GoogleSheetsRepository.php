<?php

namespace IvyForms\Repository\GoogleSheets;

use IvyForms\Entity\GoogleSheets\GoogleSheetIntegration;
use IvyForms\Factory\GoogleSheets\GoogleSheetIntegrationFactory;
use IvyForms\Repository\AbstractRepository;

/**
 * Google Sheets repository.
 */
class GoogleSheetsRepository extends AbstractRepository implements GoogleSheetsRepositoryInterface
{
    public const FACTORY = GoogleSheetIntegrationFactory::class;

    public function __construct(string $tableName)
    {
        parent::__construct($tableName);
    }

    public function findById(int $id): ?GoogleSheetIntegration
    {
        $entity = $this->getById($id);

        return $entity instanceof GoogleSheetIntegration ? $entity : null;
    }

    /**
     * @return array<GoogleSheetIntegration>
     */
    public function findByFormId(int $formId): array
    {
        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE form_id = %d ORDER BY id ASC",
            $formId
        );

        $rows = $this->wpdb->get_results($query, ARRAY_A);
        $result = [];

        foreach ($rows as $row) {
            $result[] = call_user_func([static::FACTORY, 'create'], $row);
        }

        return $result;
    }

    /**
     * @return array<GoogleSheetIntegration>
     */
    public function findEnabledByFormId(int $formId): array
    {
        $query = $this->wpdb->prepare(
            "SELECT * FROM {$this->table} WHERE form_id = %d AND enabled = 1 ORDER BY id ASC",
            $formId
        );

        $rows = $this->wpdb->get_results($query, ARRAY_A);
        $result = [];

        foreach ($rows as $row) {
            $result[] = call_user_func([static::FACTORY, 'create'], $row);
        }

        return $result;
    }

    public function countByFormId(int $formId): int
    {
        $query = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE form_id = %d",
            $formId
        );

        return (int) $this->wpdb->get_var($query);
    }

    public function deleteById(int $id): bool
    {
        $result = $this->wpdb->delete(
            $this->table,
            ['id' => $id],
            ['%d']
        );

        return $result !== false;
    }

    public function deleteByFormId(int $formId): bool
    {
        $result = $this->wpdb->delete(
            $this->table,
            ['form_id' => $formId],
            ['%d']
        );

        return $result !== false;
    }

    public function updateSyncStatus(int $id, string $status, ?string $lastRunAt = null): bool
    {
        $result = $this->wpdb->update(
            $this->table,
            [
                'last_status' => $status,
                'last_run_at' => $lastRunAt ?? current_time('mysql'),
            ],
            ['id' => $id],
            ['%s', '%s'],
            ['%d']
        );

        return $result !== false;
    }

    public function add(object $entity): int
    {
        if (!$entity instanceof GoogleSheetIntegration) {
            return 0;
        }

        if ($entity->getId()) {
            error_log('GoogleSheetsRepository: Cannot add integration that already has an ID.');
            return 0;
        }

        $saved = $this->save($entity);

        return $saved->getId() ?? 0;
    }

    public function update(int $id, object $entity): bool
    {
        if (!$entity instanceof GoogleSheetIntegration) {
            return false;
        }

        $entity->setId($id);
        $this->save($entity);

        return true;
    }

    private function save(GoogleSheetIntegration $integration): GoogleSheetIntegration
    {
        $data = $integration->toArray();

        $dbData = [
            'form_id'          => $data['form_id'],
            'spreadsheet_id'   => $data['spreadsheet_id'],
            'spreadsheet_name' => $data['spreadsheet_name'],
            'worksheet_name'   => $data['worksheet_name'],
            'field_mapping'    => wp_json_encode($data['field_mapping']),
            'smart_logic'      => wp_json_encode($data['smart_logic'] ?? []),
            'enabled'          => $data['enabled'] ? 1 : 0,
            'last_status'      => $data['last_status'],
            'last_run_at'      => $data['last_run_at'],
        ];

        $format = ['%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s'];

        if ($integration->getId()) {
            $this->wpdb->update(
                $this->table,
                $dbData,
                ['id' => $integration->getId()],
                $format,
                ['%d']
            );

            return $integration;
        }

        $this->wpdb->insert($this->table, $dbData, $format);
        $integration->setId((int) $this->wpdb->insert_id);

        return $integration;
    }

    /**
     * @return string[]
     */
    protected function getAllowedForeignColumns(): array
    {
        return ['form_id'];
    }
}
