<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP\Abilities;

use IvyForms\Common\Helpers\McpHelpers\McpAbilitiesHelper;
use IvyForms\Common\Helpers\McpHelpers\McpResponseRedactor;
use IvyForms\Infrastructure\WP\MCP\McpAbilityPermissions;

/**
 * Registers IvyForms MCP abilities for this domain.
 */
class IvyFormsEntriesAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerListEntries();
        self::registerGetEntry();
        self::registerGetEntryCount();
        self::registerUpdateEntryStarred();
    }

    private static function registerListEntries(): void
    {
        wp_register_ability('ivyforms/list-entries', [
            'label'       => __('List Entries', 'ivyforms'),
            'description' => __(
                'List IvyForms form entries (submissions) with pagination, optional text search, form filter, ' .
                'and sorting. Through this MCP ability, orderBy is limited to id or formName 
                (matching the plugin list UI).',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'formId'  => ['type' => 'integer', 'description' => 'Filter by form ID'],
                    'page'    => ['type' => 'integer', 'description' => 'Page number (default 1)'],
                    'perPage' => ['type' => 'integer', 'description' => 'Entries per page (default 20)'],
                    'search'  => ['type' => 'string'],
                    'orderBy' => ['type' => 'string'],
                    'order'   => ['type' => 'string', 'enum' => ['ASC', 'DESC']],
                ],
            ],
            'output_schema' => [
                'type' => 'object',
                'properties' => [
                    'entries' => ['type' => 'array'],
                    'meta'    => ['type' => 'object'],
                ],
            ],
            'execute_callback' => function (array $input) {
                $params = array_filter(self::sanitizeEntriesSearchParams($input));

                if (!empty($input['formId'])) {
                    $params['filters[formId]'] = (int) $input['formId'];
                }

                $data = McpAbilitiesHelper::restRequest('GET', '/entries/search', $params);

                $payload = $data['data']['data'] ?? $data['data'] ?? $data;
                $entries = $payload['data'] ?? $payload;
                $meta    = $data['data']['meta'] ?? $payload['meta'] ?? null;

                if (is_array($entries)) {
                    $entries = array_map(static function ($entry) {
                        $id = isset($entry['id']) ? (int) $entry['id'] : null;
                        if ($id) {
                            $entry['adminLink'] = McpAbilitiesHelper::adminEntryUrl($id);
                        }
                        return $entry;
                    }, $entries);

                    $entries = McpResponseRedactor::redactEntryList($entries);
                }

                return ['entries' => $entries, 'meta' => $meta];
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/list-entries');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. For listing entries, execute `ivyforms/list-entries`
                     and when describing entries render each returned entry as a clickable markdown 
                     link using its `adminLink` (if present). For viewing a specific entry,
                      use `ivyforms/get-entry` (and/or `ivyforms/open-entry` for navigation).',
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerGetEntry(): void
    {
        wp_register_ability('ivyforms/get-entry', [
            'label'       => __('Get Entry', 'ivyforms'),
            'description' => __(
                'Retrieve a single IvyForms form entry (submission) by entryId.

                Returns the entry record plus field values suitable for inspecting one submission.

                Use this ability when the user asks to:
                - open or view details for one entry when its ID is known
                - inspect field values submitted in a single response

                DO NOT use this ability when the user wants:
                - multiple entries or a list/browse flow → use ivyforms/list-entries
                - form structure or builder fields → use ivyforms/get-form
                - notifications or emails → use notification abilities

                Entry content/status editing is not exposed as an MCP ability; only starring is available
                 via ivyforms/update-entry-starred.

                This is READ-ONLY. The response includes adminLink when the entry resolves successfully.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['entryId'],
                'properties' => [
                    'entryId' => ['type' => 'integer'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => function (array $input) {
                $entryId = (int) ($input['entryId'] ?? 0);
                if ($entryId <= 0) {
                    return ['error' => __('Invalid entry ID.', 'ivyforms')];
                }
                $data    = McpAbilitiesHelper::restRequest('GET', '/entry/' . $entryId);

                $payload = $data['data'] ?? $data;
                $entry   = $payload['entry'] ?? $payload;
                if (is_array($entry)) {
                    $entry['fields']    = $payload['fields'] ?? [];
                    $entry['adminLink'] = McpAbilitiesHelper::adminEntryUrl($entryId);
                    $entry              = McpResponseRedactor::redactEntry($entry);
                }

                return $entry;
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-entry');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. Use `ivyforms/get-entry` only for a known `entryId`.
                     If the user asks to open/view the entry in the UI, use `ivyforms/open-entry` and use the returned
                      `adminLink`.',
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerGetEntryCount(): void
    {
        wp_register_ability('ivyforms/get-entry-count', [
            'label'       => __('Get Entry Count', 'ivyforms'),
            'description' => __(
                'Retrieve the total number of IvyForms entries (submissions) for one or more form IDs.

                This ability returns ONLY counts (aggregated numbers), not entry data.

                Use this ability when the user asks:
                - how many entries a form has
                - total submissions count
                - number of responses for one or more forms
                - entry statistics in the form of counts only

                DO NOT use this ability for:
                - listing actual entries → use ivyforms/list-entries
                - retrieving a single entry → use ivyforms/get-entry
                - searching or filtering entry data → use ivyforms/list-entries
                - analytics or reporting beyond simple totals

                This is a READ-ONLY aggregation endpoint and does not return individual entry records.

                Provide formIds as an array; the response includes counts keyed by each form ID.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formIds'],
                'properties' => [
                    'formIds' => [
                        'type'  => 'array',
                        'items' => ['type' => 'integer'],
                        'description' => 'One or more form IDs',
                    ],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function (array $input) {
                $formIds = array_map('intval', $input['formIds'] ?? []);
                return McpAbilitiesHelper::restRequest('GET', '/entries/count', ['id' => $formIds]);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-entry-count');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. Use `ivyforms/get-entry-count` 
                    when the user asks how many entries exist (counting/overview only).',
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerUpdateEntryStarred(): void
    {
        wp_register_ability('ivyforms/update-entry-starred', [
            'label'       => __('Update Entry Starred', 'ivyforms'),
            'description' => __(
                'Mark an IvyForms entry as starred or unstarred (favorite toggle).
                This ability updates ONLY the "starred" status of an entry and does NOT modify entry content.

                Use this ability when the user wants to:
                - star an entry
                - mark an entry as important or favorite
                - unstar or remove star from an entry
                - toggle entry priority or bookmarking state

                DO NOT use this ability for:
                - editing entry field values or read/unread status (not exposed as an MCP ability)
                - listing or viewing entries → use list-entries or get-entry
                - bulk updates or modifications beyond star status

                This is a SAFE STATE CHANGE operation that only toggles a single boolean flag (starred/unstarred).',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['entryId', 'value'],
                'properties' => [
                    'entryId' => ['type' => 'integer', 'description' => 'Entry ID'],
                    'value'   => ['type' => 'boolean', 'description' => 'true to star, false to unstar'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => function (array $input) {
                $entryId = (int) ($input['entryId'] ?? 0);
                if ($entryId <= 0) {
                    return ['error' => __('Invalid entry ID.', 'ivyforms')];
                }
                return McpAbilitiesHelper::restRequest('POST', '/entry/update/starred/' . $entryId, [
                    'id'    => $entryId,
                    'value' => self::normalizeBooleanPayload($input['value'] ?? false),
                ]);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/update-entry-starred');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, int|string>
     */
    private static function sanitizeEntriesSearchParams(array $input): array
    {
        $page = (int) ($input['page'] ?? 1);
        $page = max(1, min($page, 99999));

        $perPage = (int) ($input['perPage'] ?? 20);
        $perPage = max(1, min($perPage, 100));

        $search = sanitize_text_field((string) ($input['search'] ?? ''));
        if (strlen($search) > 500) {
            $search = substr($search, 0, 500);
        }

        $allowedOrderBy = ['id', 'formName'];
        $orderByRaw     = sanitize_text_field((string) ($input['orderBy'] ?? 'id'));
        $orderBy        = in_array($orderByRaw, $allowedOrderBy, true) ? $orderByRaw : 'id';

        $orderRaw = strtolower((string) ($input['order'] ?? 'desc'));
        $order    = in_array($orderRaw, ['asc', 'desc'], true) ? $orderRaw : 'desc';

        return [
            'page'    => $page,
            'perPage' => $perPage,
            'search'  => $search,
            'orderBy' => $orderBy,
            'order'   => $order,
        ];
    }

    /**
     * @param mixed $raw
     */
    private static function normalizeBooleanPayload($raw): bool
    {
        if (is_bool($raw)) {
            return $raw;
        }
        if (is_int($raw)) {
            return $raw === 1;
        }
        if (is_string($raw)) {
            $normalized = strtolower(trim($raw));

            return in_array($normalized, ['true', '1', 'yes', 'on'], true);
        }

        return (bool) $raw;
    }
}
