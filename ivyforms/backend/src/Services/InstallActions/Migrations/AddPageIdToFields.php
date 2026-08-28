<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\InstallActions\Migrations;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;
use IvyForms\Services\Page\PageIdNormalizer;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Migration: Add pageId column to fields table and normalise page IDs.
 *
 * On every plugin activation this migration:
 *  1. Adds the `pageId` column to `wp_ivyforms_fields` if it is missing.
 *  2. Iterates every form and converts the random UUID page IDs stored in
 *     `settings.pages[*].id` and in `fields.pageId` to the deterministic
 *     `page_1`, `page_2`, … format (unique per form via the existing
 *     `formId` FK – no separate unique-key needed).
 *
 * Safe to re-run: rows that already carry a normalised ID are detected by
 * `PageIdNormalizer::isDeterministicPageIdForForm()` and skipped.
 *
 * @package IvyForms\Services\InstallActions\Migrations
 */
class AddPageIdToFields
{
    /**
     * Run the migration.
     *
     * @return bool True if successful, false otherwise
     * @throws InvalidArgumentException
     */
    public static function run(): bool
    {
        if (!self::addPageIdColumn()) {
            return false;
        }

        global $wpdb;
        $formsTable = FormsTable::getTableName();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $forms = $wpdb->get_results("SELECT `id`, `settings` FROM `{$formsTable}`", ARRAY_A);

        if (!is_array($forms)) {
            return true;
        }

        return self::processForms($forms);
    }

    // Private helpers

    /**
     * Add the pageId column to the fields table if it does not yet exist.
     */
    private static function addPageIdColumn(): bool
    {
        global $wpdb;
        $fieldsTable = FieldsTable::getTableName();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $columnExists = $wpdb->get_results("SHOW COLUMNS FROM `{$fieldsTable}` LIKE 'pageId'");

        if (!empty($columnExists)) {
            return true;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $result = $wpdb->query(
            "ALTER TABLE `{$fieldsTable}`
                ADD COLUMN `pageId` VARCHAR(255) NOT NULL DEFAULT 'page_1' AFTER `parentId`"
        );

        if ($result === false) {
            error_log(sprintf(
                BackendStrings::getExceptionStrings()['migration_error_add_column'],
                'pageId',
                $fieldsTable
            ));
            return false;
        }

        return true;
    }

    /**
     * Iterate every form row and run all normalisation steps.
     *
     * @param array<int, array<string, mixed>> $forms
     */
    private static function processForms(array $forms): bool
    {
        foreach ($forms as $formRow) {
            $formId = isset($formRow['id']) ? (int) $formRow['id'] : 0;
            if ($formId <= 0) {
                continue;
            }

            $settings = json_decode((string) ($formRow['settings'] ?? '{}'), true);
            if (!is_array($settings)) {
                $settings = [];
            }

            $pagesData = self::normalizeFormSettings($settings);
            self::persistFormSettings($formId, $settings);
            self::normalizeFormFields($formId, $pagesData);
        }

        return true;
    }

    /**
     * Normalise the pages array and pageNavigationSettings inside $settings.
     * Returns the pagesData array produced by PageIdNormalizer.
     *
     * @param array<string, mixed> $settings  Modified in place.
     * @return array{pages: array<int, array<string, mixed>>, pageIdMap: array<string, string>, firstPageId: string}
     */
    private static function normalizeFormSettings(array &$settings): array
    {
        $pages     = isset($settings['pages']) && is_array($settings['pages']) ? $settings['pages'] : [];
        $pagesData = PageIdNormalizer::normalizePagesForForm($pages);

        $settings['pages'] = $pagesData['pages'];

        self::normalizePageNavSettings($settings, $pagesData);

        return $pagesData;
    }

    /**
     * Re-key pageNavigationSettings using the normalised page IDs.
     *
     * @param array<string, mixed> $settings   Modified in place.
     * @param array{pages: array<int, array<string, mixed>>, pageIdMap: array<string, string>,
     *              firstPageId: string} $pagesData
     */
    private static function normalizePageNavSettings(array &$settings, array $pagesData): void
    {
        if (
            !isset($settings['formActionButtons']['pageNavigationSettings'])
            || !is_array($settings['formActionButtons']['pageNavigationSettings'])
        ) {
            return;
        }

        $normalized = [];
        foreach ($settings['formActionButtons']['pageNavigationSettings'] as $oldId => $pageSettings) {
            $targetId              = self::resolveTargetPageId((string) $oldId, $pagesData);
            $normalized[$targetId] = is_array($pageSettings) ? $pageSettings : [];
        }

        $settings['formActionButtons']['pageNavigationSettings'] = $normalized;
    }

    /**
     * Map an old/unknown page ID to its normalised deterministic equivalent.
     *
     * @param array{pageIdMap: array<string, string>, firstPageId: string} $pagesData
     */
    private static function resolveTargetPageId(string $oldId, array $pagesData): string
    {
        if (isset($pagesData['pageIdMap'][$oldId])) {
            return $pagesData['pageIdMap'][$oldId];
        }

        if (PageIdNormalizer::isDeterministicPageIdForForm($oldId)) {
            return $oldId;
        }

        return $pagesData['firstPageId'];
    }

    /**
     * Persist the updated settings JSON back to the database.
     *
     * @param array<string, mixed> $settings
     */
    private static function persistFormSettings(int $formId, array $settings): void
    {
        global $wpdb;

        $result = $wpdb->update(
            FormsTable::getTableName(),
            ['settings' => wp_json_encode($settings)],
            ['id'       => $formId],
            ['%s'],
            ['%d']
        );

        if ($result === false) {
            error_log(
                sprintf(
                    'IvyForms migration error: failed to update settings for form ID %d. DB error: %s',
                    $formId,
                    (string) $wpdb->last_error
                )
            );
        }
    }

    /**
     * Normalise every fields.pageId value for a single form.
     *
     * @param array{pageIdMap: array<string, string>, firstPageId: string} $pagesData
     */
    private static function normalizeFormFields(int $formId, array $pagesData): void
    {
        global $wpdb;
        $fieldsTable = FieldsTable::getTableName();

        $fieldRows = $wpdb->get_results(
            $wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "SELECT `id`, `pageId` FROM `{$fieldsTable}` WHERE `formId` = %d",
                $formId
            ),
            ARRAY_A
        );

        if (!is_array($fieldRows)) {
            return;
        }

        foreach ($fieldRows as $fieldRow) {
            self::normalizeFieldRow($fieldRow, $fieldsTable, $pagesData);
        }
    }

    /**
     * Update a single field row if its pageId needs to change.
     *
     * @param array<string, mixed>                                         $fieldRow
     * @param array{pageIdMap: array<string, string>, firstPageId: string} $pagesData
     */
    private static function normalizeFieldRow(array $fieldRow, string $fieldsTable, array $pagesData): void
    {
        global $wpdb;

        $fieldId       = isset($fieldRow['id']) ? (int) $fieldRow['id'] : 0;
        $currentPageId = isset($fieldRow['pageId']) ? (string) $fieldRow['pageId'] : '';

        if ($fieldId <= 0) {
            return;
        }

        $targetPageId = self::resolveTargetPageId($currentPageId, $pagesData);

        if ($currentPageId === $targetPageId) {
            return; // Nothing to update
        }

        $result = $wpdb->update(
            $fieldsTable,
            ['pageId' => $targetPageId],
            ['id'     => $fieldId],
            ['%s'],
            ['%d']
        );

        if ($result === false) {
            error_log(
                sprintf(
                    'IvyForms migration error: failed to update pageId for field ID %d in table %s. DB error: %s',
                    $fieldId,
                    $fieldsTable,
                    (string) $wpdb->last_error
                )
            );
        }
    }

    /**
     * Rollback the migration (development only).
     *
     * @return bool True if successful, false otherwise
     */
    public static function rollback(): bool
    {
        global $wpdb;

        $fieldsTable = FieldsTable::getTableName();

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $columnExists = $wpdb->get_results("SHOW COLUMNS FROM `{$fieldsTable}` LIKE 'pageId'");

        if (empty($columnExists)) {
            return true;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return $wpdb->query("ALTER TABLE `{$fieldsTable}` DROP COLUMN `pageId`") !== false;
    }
}
