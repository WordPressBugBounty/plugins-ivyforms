<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Common\Helpers\McpHelpers;

use IvyForms\Services\API\IvyFormsAPI;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Multipage form helpers for IvyForms MCP abilities.
 */
class McpMultiPageHelper
{
    /**
     * @return array<string, mixed>|null Error payload when Pro is inactive.
     */
    public static function proRequiredError(int $formId): ?array
    {
        if (IvyFormsAPI::isProPluginActive()) {
            return null;
        }

        return McpMultiPagePersistenceHelper::errorResponse(
            $formId,
            self::multipageStrings()['multipage_pro_required']
        );
    }

    /**
     * Configure multipage structure (pages + optional progress indicator) on an existing form.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function setupMultipageForm(array $input): array
    {
        $formId = (int) ($input['formId'] ?? 0);
        $validationError = McpMultiPagePersistenceHelper::validateFormIdAndPro($formId);
        if ($validationError !== null) {
            return $validationError;
        }

        $pageInputs = $input['pages'] ?? [];
        if (!is_array($pageInputs) || $pageInputs === []) {
            return McpMultiPagePersistenceHelper::errorResponse(
                $formId,
                self::multipageStrings()['at_least_one_page_required']
            );
        }

        $form = McpMultiPagePersistenceHelper::loadFormOrNull($formId);
        if ($form === null) {
            return McpMultiPagePersistenceHelper::errorResponse(
                $formId,
                McpMultiPagePersistenceHelper::formNotFoundMessage($formId)
            );
        }

        $builtPages = McpMultiPagePagesHelper::buildPagesFromInput($pageInputs);
        if ($builtPages === []) {
            return McpMultiPagePersistenceHelper::errorResponse(
                $formId,
                self::multipageStrings()['no_valid_page_definitions']
            );
        }

        $form['pages'] = $builtPages;
        $form = McpMultiPagePagesHelper::mergeProgressIndicator($form, $input);

        $validPageIds = McpMultiPagePagesHelper::collectPageIds($builtPages);
        $form['fields'] = McpMultiPagePagesHelper::applyFieldPageAssignments(
            $form['fields'] ?? [],
            $input['fieldAssignments'] ?? [],
            $validPageIds,
            (string) $builtPages[0]['id']
        );

        return McpMultiPagePersistenceHelper::persistForm(
            $formId,
            $form,
            self::multipageStrings()['multipage_configured_successfully']
        );
    }

    /**
     * Append a page to an existing multipage form.
     *
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function addFormPage(array $input): array
    {
        $formId = (int) ($input['formId'] ?? 0);
        $validationError = McpMultiPagePersistenceHelper::validateFormIdAndPro($formId);
        if ($validationError !== null) {
            return $validationError;
        }

        $label = sanitize_text_field((string) ($input['label'] ?? ''));
        if ($label === '') {
            return McpMultiPagePersistenceHelper::errorResponse(
                $formId,
                self::multipageStrings()['page_label_required']
            );
        }

        $form = McpMultiPagePersistenceHelper::loadFormOrNull($formId);
        if ($form === null) {
            return McpMultiPagePersistenceHelper::errorResponse(
                $formId,
                McpMultiPagePersistenceHelper::formNotFoundMessage($formId)
            );
        }

        $existingPages = McpMultiPagePagesHelper::stripFieldsFromPages($form['pages'] ?? []);
        if ($existingPages === []) {
            $existingPages = McpMultiPagePagesHelper::defaultFirstPage();
        }

        $newPage = [
            'id'       => 'mcp_page_' . wp_generate_password(8, false, false),
            'label'    => $label,
            'closable' => true,
        ];

        $insertAfterPageId = sanitize_text_field((string) ($input['insertAfterPageId'] ?? ''));
        $form['pages'] = McpMultiPagePagesHelper::insertPage($existingPages, $newPage, $insertAfterPageId);
        $form = McpMultiPagePagesHelper::mergeProgressIndicator($form, []);

        $result = McpMultiPagePersistenceHelper::persistForm(
            $formId,
            $form,
            self::multipageStrings()['form_page_added_successfully']
        );

        return McpMultiPagePagesHelper::enrichResultWithAddedPage($result, $label);
    }

    /**
     * @param array<string, mixed> $field
     */
    public static function applyPageIdToField(array &$field, string $pageId): void
    {
        $field['pageId'] = $pageId;

        if (!isset($field['settings']) || !is_array($field['settings'])) {
            $field['settings'] = [];
        }

        $field['settings']['pageId'] = $pageId;
    }

    /**
     * @param array<string, mixed> $field
     */
    public static function extractFieldPageId(array $field): ?string
    {
        $pageId = isset($field['pageId']) ? sanitize_text_field((string) $field['pageId']) : '';
        if ($pageId !== '') {
            return $pageId;
        }

        if (isset($field['settings']) && is_array($field['settings'])) {
            $settingsPageId = sanitize_text_field((string) ($field['settings']['pageId'] ?? ''));
            if ($settingsPageId !== '') {
                return $settingsPageId;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $form
     */
    public static function resolveDefaultPageIdForForm(array $form): ?string
    {
        $pages = $form['pages'] ?? [];
        if (!is_array($pages) || $pages === []) {
            return null;
        }

        $firstPage = $pages[0] ?? null;
        if (!is_array($firstPage)) {
            return 'page_1';
        }

        $pageId = sanitize_text_field((string) ($firstPage['id'] ?? ''));
        return $pageId !== '' ? $pageId : 'page_1';
    }

    /**
     * @param array<string, mixed> $form
     */
    public static function normalizePageIdForForm(string $rawPageId, array $form): ?string
    {
        $pageId = sanitize_text_field($rawPageId);
        if ($pageId === '') {
            return self::resolveDefaultPageIdForForm($form);
        }

        $pages = $form['pages'] ?? [];
        if (!is_array($pages) || $pages === []) {
            return $pageId;
        }

        $validPageIds = McpMultiPagePagesHelper::collectPageIds($pages);
        if (in_array($pageId, $validPageIds, true)) {
            return $pageId;
        }

        if (preg_match('/^page_(\d+)$/', $pageId, $matches) === 1) {
            $index = (int) $matches[1] - 1;
            if (isset($validPageIds[$index])) {
                return $validPageIds[$index];
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultProgressIndicator(): array
    {
        return [
            'type'                => 'progress-bar',
            'showPageTitles'      => true,
            'hidePageNumbers'     => false,
            'hideConnectingLines' => false,
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function multipageStrings(): array
    {
        /** @var array<string, string> */
        return BackendStrings::getMcpMultipageStrings();
    }
}
