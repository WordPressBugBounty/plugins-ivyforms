<?php

namespace IvyForms\Repository\EntryField;

use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Repository\AbstractRepository;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\Translations\BackendStrings;

class EntryFieldNameEmailRepository extends AbstractRepository implements EntryFieldNameEmailRepositoryInterface
{
    /**
     * @param int[] $entryIds
     * @return array<int, array<string, string>>
     * @throws QueryExecutionException
     */
    public function findNameEmailValuesByEntryIds(array $entryIds): array
    {
        if ($entryIds === []) {
            return [];
        }

        $fieldsTable = FieldsTable::getTableName();
        $placeholders = implode(',', array_fill(0, count($entryIds), '%d'));
        $sql = "SELECT ef.entryId, ef.fieldValue, CONCAT(fld.type, '_', fld.fieldIndex) AS fieldKey
                FROM {$this->table} ef
                INNER JOIN {$fieldsTable} fld ON fld.id = ef.fieldId
                WHERE ef.entryId IN ({$placeholders})
                  AND (fld.parentId IS NULL OR fld.parentId = 0)
                  AND fld.type IN ('name', 'email')
                  AND ef.fieldValue <> ''";
        $results = $this->wpdb->get_results($this->wpdb->prepare($sql, ...$entryIds), ARRAY_A);
        if ($results === false) {
            throw new QueryExecutionException(
                BackendStrings::getExceptionStrings()['unable_find_by_id'] . __CLASS__
            );
        }

        $values = [];
        foreach ($results as $row) {
            $entryId = (int) ($row['entryId'] ?? 0);
            $fieldKey = (string) ($row['fieldKey'] ?? '');
            if ($entryId > 0 && $fieldKey !== '') {
                $values[$entryId][$fieldKey] = (string) ($row['fieldValue'] ?? '');
            }
        }

        return $values;
    }
}
