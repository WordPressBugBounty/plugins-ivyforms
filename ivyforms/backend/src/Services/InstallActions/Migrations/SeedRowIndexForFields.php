<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;

/**
 * Seed: Initialize rowIndex for fields from AddRowColumnLayoutToFields migration
 *
 * This seed runs after AddRowColumnLayoutToFields migration to properly set rowIndex
 * for existing fields. It assigns each field its own row based on position.
 *
 * @package IvyForms\Services\InstallActions\Migrations
 */
class SeedRowIndexForFields
{
    /**
     * Run the seed
     *
     * @return bool True if successful, false otherwise
     * @throws InvalidArgumentException
     */
    public static function run(): bool
    {
        global $wpdb;

        $tableName = FieldsTable::getTableName();

        // Get all forms that have fields
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $forms = $wpdb->get_col(
            "SELECT DISTINCT formId FROM `{$tableName}` 
            WHERE (`parentId` IS NULL OR `parentId` = 0)"
        );

        if (empty($forms)) {
            return true; // No forms to seed
        }

        $seededForms = 0;

        foreach ($forms as $formId) {
            // For each form, check if all parent fields are in row 0
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $allInRowZero = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM `{$tableName}` 
                    WHERE formId = %d 
                    AND (`parentId` IS NULL OR `parentId` = 0)
                    AND `rowIndex` != 0",
                    $formId
                )
            );

            // If all fields are in row 0, seed them with proper row indexes
            if ($allInRowZero == 0) {
                // Get all parent fields for this form ordered by position
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $fields = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT id, position FROM `{$tableName}` 
                        WHERE formId = %d 
                        AND (`parentId` IS NULL OR `parentId` = 0)
                        ORDER BY position ASC",
                        $formId
                    )
                );

                // Update each field with proper rowIndex (each field in its own row)
                foreach ($fields as $index => $field) {
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $wpdb->update(
                        $tableName,
                        [
                            'rowIndex' => $index,
                            'columnIndex' => 0,
                            'width' => 100,
                        ],
                        ['id' => $field->id],
                        ['%d', '%d', '%d'],
                        ['%d']
                    );
                }

                $seededForms++;
            }
        }

        if ($seededForms > 0) {
            error_log("IvyForms Seed: Initialized rowIndex for {$seededForms} forms");
        }

        return true;
    }

    /**
     * Rollback the seed (for development purposes)
     *
     * @return bool True if successful, false otherwise
     * @throws InvalidArgumentException
     */
    public static function rollback(): bool
    {
        global $wpdb;

        $tableName = FieldsTable::getTableName();

        // Reset all fields back to row 0
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $updateQuery = "UPDATE `{$tableName}` 
            SET `rowIndex` = 0,
                `columnIndex` = 0,
                `width` = 100
            WHERE (`parentId` IS NULL OR `parentId` = 0)";

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $result = $wpdb->query($updateQuery);

        return $result !== false;
    }
}
