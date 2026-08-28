<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP\Abilities;

use IvyForms\Common\Helpers\McpHelpers\McpAbilitiesHelper;
use IvyForms\Common\Helpers\McpHelpers\McpFormCreateHelper;
use IvyForms\Common\Helpers\McpHelpers\McpFormSettingsHelper;
use IvyForms\Infrastructure\WP\MCP\McpAbilityPermissions;

/**
 * Registers IvyForms MCP abilities for this domain.
 */
class IvyFormsFormsAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerGetForms();
        self::registerGetForm();
        self::registerCreateForm();
        self::registerDuplicateForm();
        self::registerUpdateFormSettings();
        self::registerUpdateFormStarred();
        self::registerUpdateFormStatus();
    }

    private static function registerGetForms(): void
    {
        wp_register_ability('ivyforms/get-forms', [
            'label'       => __('List Forms', 'ivyforms'),
            'description' => __(
                'List all existing IvyForms forms. ' .
                'This ability is strictly for retrieving an overview of available forms and does 
                NOT return full form details. ' .

                'WHEN TO USE: ' .
                '- Use when the user asks which forms exist, what forms they have, list/show/display forms,
                 or how many forms exist. ' .
                '- Use for dashboards, overviews, or selection steps before operating on a specific form. ' .

                'WHAT IT RETURNS: ' .
                '- Form ID (if available) ' .
                '- Form name ' .
                '- Publication status (published/unpublished) ' .
                '- Admin link for navigation (use it to turn the name into a clickable markdown link) ' .

                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY listing operation (no modifications happen). ' .
                '2. Do NOT use this to fetch full form configuration; use get-form for that. ' .
                '3. Do NOT use this to create or modify forms; use create-form or update-form-settings. ' .
                '4. If a user refers to a specific form ID, prefer get-form instead. ' .
                '5. This endpoint is safe for repeated calls and listing/overview workflows. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Always treat this as the entry point for form discovery. ' .
                '- Use adminLink for navigation instead of building URLs manually. ' .
                '- If user intent shifts to editing, transition to get-form → update-form-settings flow. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT confuse this with get-form (single form details). ' .
                '- Do NOT assume field-level data is included. ' .
                '- Do NOT attempt updates or mutations from this ability.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [],
            ],
            'output_schema' => [
                'type' => 'array',
                'items' => [
                    'type'       => 'object',
                    'properties' => [
                        'id'        => ['type' => ['integer', 'null']],
                        'name'      => ['type' => 'string'],
                        'published' => ['type' => 'boolean'],
                        'adminLink' => ['type' => ['string', 'null']],
                    ],
                ],
            ],
            'execute_callback' => static function () {
                return self::getForms();
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-forms');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. When listing forms, first call `ivyforms/get-forms`, 
                    then render each returned form name as a clickable Markdown link in the form `[name](adminLink)` 
                    using the returned `adminLink`. When the user asks to open a specific form or its section, use 
                    `ivyforms/open-form-builder` with `section` values: `settings/general`, `settings/notification` 
                    (alias `settings/notifications`), `settings/integrations`, or `results/entries`.',
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerGetForm(): void
    {
        wp_register_ability('ivyforms/get-form', [
            'label'       => __('Get Form', 'ivyforms'),
            'description' => __(
                'Get full details of a single IvyForms form using its formId. ' .
                'This ability returns complete form configuration including structure, fields, and settings. ' .

                'WHEN TO USE: ' .
                '- Use only when a specific formId is known or explicitly provided by the user. ' .
                '- Use when user requests to view, inspect, or edit a specific form. ' .

                'WHAT IT RETURNS: ' .
                '- Full form metadata (id, name, settings) ' .
                '- Complete field structure and layout ' .
                '- Configuration and behavior settings ' .
                '- Admin link for editing/navigation ' .

                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY detailed fetch operation. ' .
                '2. Do NOT use for listing or searching multiple forms; use get-forms instead. ' .
                '3. This returns full structure and may be heavier than list operations. ' .
                '4. Use this before any form editing or field modification workflows. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Always prefer this after form selection from get-forms. ' .
                '- Use adminLink for navigation or UI actions. ' .
                '- This is the canonical entry point for form editing context. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT use for listing forms. ' .
                '- Do NOT assume this modifies data. ' .
                '- Do NOT skip this before edit operations.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId'],
                'properties' => [
                    'formId' => ['type' => 'integer', 'description' => 'Form ID'],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'id'     => ['type' => 'integer'],
                    'name'   => ['type' => 'string'],
                    'fields' => ['type' => 'array'],
                ],
            ],
            'execute_callback' => function (array $input) {
                $formId = (int) ($input['formId'] ?? 0);
                if ($formId <= 0) {
                    return ['error' => __('Invalid form ID.', 'ivyforms')];
                }
                $data   = McpAbilitiesHelper::restRequest('GET', '/form/' . $formId);

                $form = $data['data']['data'] ?? $data['data'] ?? $data;
                if (is_array($form)) {
                    $form['adminLink'] = McpAbilitiesHelper::adminFormUrl($formId);
                }

                return $form;
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-form');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. Use `ivyforms/get-form` 
                    only when a specific `formId` 
                    is known and the user requests details. When providing navigation links,
                     use the returned `adminLink` 
                    (if present) as a clickable markdown link.',
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function getForms(): array
    {
        $perPage = 100;
        $allRawForms = [];
        $page = 1;

        while (true) {
            $data = McpAbilitiesHelper::restRequest(
                'GET',
                '/forms/search',
                ['perPage' => $perPage, 'page' => $page]
            );

            $rawForms = $data['data']['data']['data']
                ?? $data['data']['data']
                ?? $data['data']
                ?? [];

            if (!is_array($rawForms)) {
                break;
            }

            if ($rawForms === []) {
                break;
            }

            $allRawForms = array_merge($allRawForms, $rawForms);

            if (count($rawForms) < $perPage) {
                break;
            }

            $page++;
        }

        return array_map(function ($form) {
            $id = isset($form['id']) ? (int) $form['id'] : null;

            return [
                'id'        => $id,
                'name'      => $form['name'] ?? '',
                'published' => !empty($form['published']),
                'adminLink' => $id ? McpAbilitiesHelper::adminFormUrl($id) : null,
            ];
        }, $allRawForms);
    }

    private static function registerCreateForm(): void
    {
        wp_register_ability('ivyforms/create-form', [
            'label'       => __('Create Form', 'ivyforms'),
            'description' => __(
                'Create a new IvyForms form from a template. ' .
                'This creates a fresh form instance but does NOT fully configure it. ' .

                'WHEN TO USE: ' .
                '- Use when user requests creation of a new form or starting a new form project. ' .
                '- Use when a template-based form is requested (e.g. blank_form, contact_form). ' .

                'WHAT IT RETURNS: ' .
                '- Newly created form ID ' .
                '- Admin link to access the form builder ' .
                '- Creation status message ' .

                'IMPORTANT RULES: ' .
                '1. This is a DESTRUCTIVE WRITE operation (creates new persistent data). ' .
                '2. Do NOT use for updating existing forms; use update-form-settings instead. ' .
                '3. Do NOT use for adding fields; use add-form-field after creation. ' .
                '4. Do NOT assume form is fully configured after creation. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- After creation, immediately suggest next steps: settings → fields → publish. ' .
                '- Always guide user to update-form-settings for customization. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT treat this as full form setup. ' .
                '- Do NOT add fields inside this operation. ' .
                '- Do NOT assume publishing happens automatically.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'templateId' => [
                        'type' => 'string',
                        'description' => 'Template ID (e.g. blank_form, contact_form). '
                            . 'Aliases: blank, contact, empty, start_from_scratch.',
                    ],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'formId'    => ['type' => ['integer', 'null']],
                    'adminLink' => ['type' => ['string', 'null']],
                    'message'   => ['type' => 'string'],
                ],
            ],
            'execute_callback' => static function (array $input) {
                return McpFormCreateHelper::createForm($input);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/create-form');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. To create a form, call `ivyforms/create-form` '
                        . 'with templateId `blank_form` or `contact_form` (aliases: blank, contact). '
                        . 'After success, share the returned adminLink as a clickable markdown link and, '
                        . 'when the user should edit the form, call `ivyforms/open-form-builder` with the new formId.',
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ],
            ],
        ]);
    }

    private static function registerDuplicateForm(): void
    {
        wp_register_ability('ivyforms/duplicate-form', [
            'label'       => __('Duplicate Form', 'ivyforms'),
            'description' => __(
                'Duplicate an existing IvyForms form by formId, creating an independent copy. ' .
                'The new form contains the same structure, fields, and configuration as the original. ' .

                'WHEN TO USE: ' .
                '- Use when user wants to copy, clone, or reuse an existing form. ' .
                '- Use when user wants a template based on an existing form. ' .

                'WHAT IT RETURNS: ' .
                '- New form ID ' .
                '- Admin link for the duplicated form ' .
                '- Copy status message ' .

                'IMPORTANT RULES: ' .
                '1. This is a DESTRUCTIVE WRITE operation (creates new form). ' .
                '2. The original form is never modified. ' .
                '3. Do NOT use for editing or updating forms. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Treat duplication as a starting point for a new workflow. ' .
                '- After duplication, suggest update-form-settings or add-form-field depending on intent. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT modify original form. ' .
                '- Do NOT confuse with create-form (template-based creation).',
                'ivyforms'
            ),            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId'],
                'properties' => [
                    'formId' => ['type' => 'integer', 'description' => 'Form ID to duplicate'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => function (array $input) {
                $formId = (int) ($input['formId'] ?? 0);
                if ($formId <= 0) {
                    return ['error' => __('Invalid form ID.', 'ivyforms')];
                }
                $data   = McpAbilitiesHelper::restRequest('POST', '/form/duplicate/' . $formId);

                $dup = $data['data']['data'] ?? $data['data'] ?? $data;
                if (is_array($dup) && isset($dup['id'])) {
                    $dup['adminLink'] = McpAbilitiesHelper::adminFormUrl((int) $dup['id']);
                }

                return $dup;
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/duplicate-form');
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
                    'idempotent'  => false,
                ],
            ],
        ]);
    }

    private static function registerUpdateFormSettings(): void
    {
        wp_register_ability('ivyforms/update-form-settings', [
            'label'       => __('Update Form Settings', 'ivyforms'),
            'description' => __(
                'Update general settings of an existing IvyForms form. ' .
                'This includes metadata and UI behavior, but NOT fields or entries. ' .

                'WHEN TO USE: ' .
                '- Use when user wants to rename a form, change description, 
                publish/unpublish, or adjust visibility settings. ' .
                '- Use for configuration-level updates only. ' .

                'WHAT IT UPDATES: ' .
                '- Form name (max 255 characters) ' .
                '- Description (max 1000 characters) ' .
                '- Published status (draft/live) ' .
                '- UI settings (showTitle, showDescription, storeEntries) ' .

                'IMPORTANT RULES: ' .
                '1. This is a WRITE operation affecting form metadata only. ' .
                '2. Do NOT use for modifying fields (use field abilities instead). ' .
                '3. Do NOT use for entries or submissions. ' .
                '4. Do NOT use for structural changes. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Always ensure form exists via get-form before updating. ' .
                '- After update, confirm status and optionally suggest open-form-builder. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT modify fields here. ' .
                '- Do NOT assume content structure changes. ' .
                '- Do NOT use for entry-level operations.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId'],
                'properties' => [
                    'formId'          => ['type' => 'integer'],
                    'name'            => ['type' => 'string', 'description' => 'New form name'],
                    'title'           => [
                        'type' => 'string',
                        'description' => 'Alias for name (useful when renaming a form)',
                    ],
                    'description'     => ['type' => 'string'],
                    'published'       => ['type' => 'boolean'],
                    'showTitle'       => ['type' => 'boolean'],
                    'showDescription' => ['type' => 'boolean'],
                    'storeEntries'    => ['type' => 'boolean'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function (array $input) {
                return McpFormSettingsHelper::updateFormSettings($input);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/update-form-settings');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. To rename a form or change general settings, '
                        . 'call `ivyforms/update-form-settings` with formId and the fields to change '
                        . '(e.g. name for rename). Do not use this tool to create forms or add fields.',
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerUpdateFormStarred(): void
    {
        wp_register_ability('ivyforms/update-form-starred', [
            'label'       => __('Update Form Starred', 'ivyforms'),
            'description' => __(
                'Toggle starred (favorite) status of an IvyForms form. ' .
                'This is purely a UI/organizational flag and does NOT affect form behavior. ' .

                'WHEN TO USE: ' .
                '- Use when user wants to mark a form as important or favorite. ' .
                '- Use when user wants to remove a form from favorites. ' .

                'WHAT IT AFFECTS: ' .
                '- Only the starred flag (true/false) ' .
                '- No structural or behavioral changes ' .

                'IMPORTANT RULES: ' .
                '1. This is a SAFE metadata update. ' .
                '2. Does NOT affect publishing or visibility. ' .
                '3. Does NOT affect form data or structure. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Use for quick organization workflows. ' .
                '- Can be safely called multiple times. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT treat as publishing action. ' .
                '- Do NOT confuse with status update.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId', 'value'],
                'properties' => [
                    'formId' => ['type' => 'integer', 'description' => 'Form ID'],
                    'value'  => ['type' => 'boolean', 'description' => 'true to star, false to unstar'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => function (array $input) {
                $formId = (int) ($input['formId'] ?? 0);
                if ($formId <= 0) {
                        return ['error' => __('Invalid form ID.', 'ivyforms')];
                }
                return McpAbilitiesHelper::restRequest('POST', '/form/update/starred/' . $formId, [
                    'id'    => $formId,
                    'value' => self::normalizeBooleanPayload($input['value'] ?? false),
                ]);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/update-form-starred');
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

    private static function registerUpdateFormStatus(): void
    {
        wp_register_ability('ivyforms/update-form-status', [
            'label'       => __('Update Form Status', 'ivyforms'),
            'description' => __(
                'Publish or unpublish an IvyForms form. ' .
                'Controls whether a form is live (public) or in draft (hidden). ' .

                'WHEN TO USE: ' .
                '- Use when user wants to make a form live or disable public access. ' .
                '- Use when toggling availability of a form. ' .

                'WHAT IT AFFECTS: ' .
                '- Form visibility (published vs unpublished) ' .
                '- Public accessibility of the form ' .

                'IMPORTANT RULES: ' .
                '1. This is a SAFE STATUS update operation. ' .
                '2. Does NOT modify structure, fields, or entries. ' .
                '3. Does NOT delete or alter data. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Always confirm intent before publishing/unpublishing. ' .
                '- Prefer this over delete when user says "disable" or "hide". ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT modify settings or fields here.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId', 'value'],
                'properties' => [
                    'formId' => ['type' => 'integer', 'description' => 'Form ID'],
                    'value'  => ['type' => 'boolean', 'description' => 'true to publish, false to unpublish'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => function (array $input) {
                $formId = (int) ($input['formId'] ?? 0);
                if ($formId <= 0) {
                    return ['error' => __('Invalid form ID.', 'ivyforms')];
                }
                return McpAbilitiesHelper::restRequest('POST', '/form/update/status/' . $formId, [
                    'id'    => $formId,
                    'value' => self::normalizeBooleanPayload($input['value'] ?? false),
                ]);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/update-form-status');
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
     * Normalize MCP boolean payloads.
     *
     * Unlike naive `(bool)` casting, this maps strings like "false"/"0"
     * to `false` exactly like the entries registrar does.
     *
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
