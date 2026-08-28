<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP\Abilities;

use IvyForms\Common\Helpers\McpHelpers\McpFieldAbilitiesHelper;
use IvyForms\Common\Helpers\McpHelpers\McpFieldAddHelper;
use IvyForms\Common\Helpers\McpHelpers\McpFieldDuplicateHelper;
use IvyForms\Infrastructure\WP\MCP\McpAbilityPermissions;

/**
 * Registers IvyForms MCP abilities for this domain.
 */
class IvyFormsFieldsAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerAddFormField();
        self::registerAddFormFields();
        self::registerDuplicateFormField();
        self::registerReorderFormFields();
    }

    private static function registerAddFormField(): void
    {
        wp_register_ability('ivyforms/add-form-field', [
            'label'       => __('Add Form Field', 'ivyforms'),
            'description' => __(
                'Add a new field to an existing IvyForms form. ' .
                'Supported types: text, textarea, email, number, phone, website, name, radio, checkbox, ' .
                'select, multi-select, address, date, time, rating, html, slider, file-upload, gdpr, ' .
                'product, quantity, total. ' .
                'When IvyForms Pro is active, password, signature, date_time, likert, nps, and rich_text ' .
                'are also supported. ' .
                'After adding a field, use open-form-builder to let the user see and configure. ' .
                'For multipage forms (Pro), pass pageId (e.g. page_1) to place the field on a specific page.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId', 'type'],
                'properties' => [
                    'formId'      => ['type' => 'integer', 'description' => 'Form ID to add the field to'],
                    'type'        => [
                        'type' => 'string',
                        'description' => 'Field type: text, textarea, email, number, phone, website, name, ' .
                            'radio, checkbox, select, multi-select, address, date, time, rating, html, ' .
                            'slider, file-upload, gdpr, product, quantity, total. ' .
                            'With Pro active: password, signature, date_time, likert, nps, rich_text',
                    ],
                    'label'       => [
                        'type' => 'string',
                        'description' => 'Field label (default: capitalised type name)',
                    ],
                    'required'    => [
                        'type' => 'boolean',
                        'description' => 'Whether the field is required (default: false)',
                    ],
                    'placeholder' => [
                        'type' => 'string',
                        'description' => 'Placeholder text',
                    ],
                    'description' => ['type' => 'string',  'description' => 'Help text shown below the field'],
                    'width'       => [
                        'type' => 'integer',
                        'description' => 'Column width: 25, 50, 75 or 100 (default: 100)',
                    ],
                    'pageId'      => [
                        'type' => 'string',
                        'description' => 'Optional multipage target (e.g. page_1, page_2). Requires IvyForms Pro.',
                    ],
                    'rows'        => [
                        'type' => 'integer',
                        'description' => 'Visible rows for textarea (default: 3) and rich_text (default: 4)',
                    ],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'formId'    => ['type' => 'integer'],
                    'success'   => ['type' => 'boolean'],
                    'message'   => ['type' => 'string'],
                    'adminLink' => ['type' => 'string'],
                ],
            ],
            'execute_callback' => static function (array $input) {
                return McpFieldAddHelper::addFormField($input);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/add-form-field');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. To add a field to an existing form, call '
                        . '`ivyforms/add-form-field` with formId and type (e.g. text, email, textarea). '
                        . 'If formId is unknown, get it from `ivyforms/get-forms` or `ivyforms/create-form` first. '
                        . 'After adding, call `ivyforms/open-form-builder` so the user can review the field.',
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ],
            ],
        ]);
    }

    private static function registerAddFormFields(): void
    {
        wp_register_ability('ivyforms/add-form-fields', [
            'label'       => __('Add Form Fields', 'ivyforms'),
            'description' => __(
                'Add one or more new fields to an existing IvyForms form. ' .
                'This ability modifies stored form configuration 
                (writes the whole form payload like the builder save). ' .

                'REQUIRED INPUTS: formId and fields (each item requires type). 
                Optional per-field keys mirror add-form-field. ' .

                'SUPPORTED FIELD TYPES: text, textarea, email, number, phone, website, name, radio, checkbox, ' .
                'select, multi-select, address, date, time, rating, html, slider, file-upload, gdpr, ' .
                'product, quantity, total. ' .
                'If IvyForms Pro is active, password, signature, date_time, likert, nps, and rich_text ' .
                'fields are also supported. ' .

                'IMPORTANT WORKFLOW RULES: ' .
                '1. This ability ONLY adds fields to an existing form (it does NOT create a form). ' .
                '2. If formId is missing or unknown, the agent MUST first use get-forms or 
                create-form before calling this. ' .
                '3. After successfully adding fields, the next step should usually be open-form-builder
                 so the user can review layout and options. ' .
                '4. Do not assume field ordering unless explicitly returned by the API response. ' .

                'FIELD BEHAVIOR NOTES: ' .
                '- label defaults to a capitalized version of type if not provided. ' .
                '- required defaults to false. ' .
                '- width defaults to 100 if not specified. ' .
                '- description is helper text shown under the field in UI. ' .

                'COMMON AGENT MISTAKES TO AVOID: ' .
                '- Do NOT call this if the user wants to create a form (use create-form instead). ' .
                '- Do NOT assume the field already exists after calling this ability until API confirms success. ' .
                '- Do NOT duplicate fields manually; use duplicate-form-field for that purpose.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId', 'fields'],
                'properties' => [
                    'formId' => ['type' => 'integer', 'description' => 'Form ID to add the fields to'],
                    'fields' => [
                        'type'        => 'array',
                        'description' => 'Field definitions to add in order',
                        'items'       => [
                            'type'       => 'object',
                            'required'   => ['type'],
                            'properties' => [
                                'type'        => ['type' => 'string'],
                                'label'       => ['type' => 'string'],
                                'required'    => ['type' => 'boolean'],
                                'placeholder' => ['type' => 'string'],
                                'description' => ['type' => 'string'],
                                'width'       => ['type' => 'integer'],
                                'pageId'      => ['type' => 'string'],
                                'rows'        => [
                                    'type' => 'integer',
                                    'description' => 'Visible rows for textarea (default: 3) '
                                        . 'and rich_text (default: 4)',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'formId'      => ['type' => 'integer'],
                    'success'     => ['type' => 'boolean'],
                    'message'     => ['type' => 'string'],
                    'addedFields' => [
                        'type'  => 'array',
                        'items' => ['type' => 'string'],
                    ],
                    'adminLink'   => ['type' => 'string'],
                ],
            ],
            'execute_callback' => static function (array $input) {
                return McpFieldAddHelper::addFormFields($input);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/add-form-fields');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'instructions' => 'MCP execution is required. Prefer `ivyforms/add-form-fields` when adding '
                        . 'multiple fields at once. Requires formId and a fields array with type on each item.',
                    'readonly'    => false,
                    'destructive' => false,
                    'idempotent'  => false,
                ],
            ],
        ]);
    }

    private static function registerDuplicateFormField(): void
    {
        wp_register_ability('ivyforms/duplicate-form-field', [
            'label'       => __('Duplicate Form Field', 'ivyforms'),
            'description' => __('Duplicate an existing field in an IvyForms form by its ID.', 'ivyforms'),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId', 'fieldId'],
                'properties' => [
                    'formId'  => ['type' => 'integer', 'description' => 'Form ID the field belongs to'],
                    'fieldId' => ['type' => 'integer', 'description' => 'ID of the field to duplicate'],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'formId'     => ['type' => 'integer'],
                    'oldFieldId' => ['type' => 'integer'],
                    'newFieldId' => ['type' => 'integer'],
                    'success'    => ['type' => 'boolean'],
                    'message'    => ['type' => 'string'],
                    'adminLink'  => ['type' => 'string'],
                ],
            ],
            'execute_callback' => static function (array $input) {
                return McpFieldDuplicateHelper::duplicateFormField($input);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/duplicate-form-field');
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

    private static function registerReorderFormFields(): void
    {
        wp_register_ability('ivyforms/reorder-form-fields', [
            'label'       => __('Reorder Form Fields', 'ivyforms'),
            'description' => __(
                'Reorder and adjust layout metadata for IvyForms form fields without changing field 
                types or deleting them. ' .
                'Send formId plus a fields array listing each affected field id with its new position, 
                and optionally rowIndex, columnIndex, width, and pageId (multipage / Pro). ' .

                'Unlike duplicate-form-field, this updates existing records in place via a form save. ' .

                'REQUIRED INPUTS: formId, and for each element in fields: id and position (per input schema). ' .

                'After reordering, use open-form-builder if the user should verify layout in the UI.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId', 'fields'],
                'properties' => [
                    'formId' => ['type' => 'integer', 'description' => 'Form ID to reorder fields in'],
                    'fields' => [
                        'type'        => 'array',
                        'description' => 'List of field objects with updated order and layout properties',
                        'items'       => [
                            'type'       => 'object',
                            'required'   => ['id', 'position'],
                            'properties' => [
                                'id'          => ['type' => 'integer', 'description' => 'ID of the field'],
                                'position'    => ['type' => 'integer', 'description' => 'New position of the field'],
                                'rowIndex'    => [
                                    'type' => 'integer',
                                    'description' => 'Optional: New row index of the field',
                                ],
                                'columnIndex' => [
                                    'type' => 'integer',
                                    'description' => 'Optional: New column index of the field',
                                ],
                                'width'       => [
                                    'type' => 'integer',
                                    'description' => 'Optional: New width of the field (25, 50, 75, or 100)',
                                ],
                                'pageId'      => [
                                    'type' => 'string',
                                    'description' => 'Optional: Move field to this page id (e.g. page_2). ' .
                                        'Pro multipage.',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'formId'            => ['type' => 'integer'],
                    'success'           => ['type' => 'boolean'],
                    'message'           => ['type' => 'string'],
                    'updatedFieldsCount' => ['type' => 'integer'],
                    'adminLink'         => ['type' => 'string'],
                ],
            ],
            'execute_callback' => static function (array $input) {
                return McpFieldAbilitiesHelper::reorderFormFields($input);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/reorder-form-fields');
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
}
