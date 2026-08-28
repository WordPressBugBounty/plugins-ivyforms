<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Infrastructure\WP\MCP\Abilities;

use IvyForms\Common\Helpers\McpHelpers\McpAbilitiesHelper;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Infrastructure\WP\MCP\McpAbilityPermissions;

/**
 * Registers IvyForms MCP abilities for this domain.
 */
class IvyFormsNotificationsAbilitiesRegistrar
{
    public static function registerAbilities(): void
    {
        self::registerListNotifications();
        self::registerCreateNotification();
        self::registerGetNotification();
        self::registerUpdateNotification();
        self::registerUpdateNotificationSettings();
        self::registerDuplicateNotification();
        self::registerSearchNotifications();
    }

    private static function registerListNotifications(): void
    {
        wp_register_ability('ivyforms/list-notifications', [
            'label'       => __('List Notifications', 'ivyforms'),
            'description' => __(
                'Get all email notifications configured for a specific IvyForms form. ' .
                'This ability returns a complete list of notifications attached to a form, 
                including their configuration and metadata. ' .

                'WHEN TO USE: ' .
                '- Use when the user asks to see all notifications for a form. ' .
                '- Use when the user wants an overview of email automations or messaging rules. ' .
                '- Use when checking what emails are sent after form submission. ' .

                'WHAT IT RETURNS: ' .
                '- Notification ID ' .
                '- Notification name ' .
                '- Sender / receiver configuration (if available) ' .
                '- Enabled/disabled status ' .
                '- Basic metadata for rendering or selection ' .

                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY listing operation. No changes are made. ' .
                '2. Do NOT use this to fetch full notification configuration for editing;
                 use get-notification instead. ' .
                '3. Do NOT create or modify notifications; use create-notification or update-notification. ' .
                '4. Always treat results as a list view, not a detailed editor view. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Use this as the entry point for notification discovery. ' .
                '- If the user wants to edit a notification, first call this, then proceed to get-notification. ' .
                '- Use notificationId from results to navigate to deeper actions. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT assume full email template content is included. ' .
                '- Do NOT modify notifications based on this response. ' .
                '- Do NOT confuse with search-notifications (which supports pagination and filtering).',
                'ivyforms'
            ),            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId'],
                'properties' => [
                    'formId' => ['type' => 'integer'],
                ],
            ],
            'output_schema' => ['type' => 'array'],
            'execute_callback' => static function (array $input) {
                $formId = (int) ($input['formId'] ?? 0);
                $data   = McpAbilitiesHelper::restRequest('GET', '/notifications/' . $formId);
                return $data['data']['data'] ?? $data['data'] ?? $data;
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/list-notifications');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerCreateNotification(): void
    {
        wp_register_ability('ivyforms/create-notification', [
            'label'       => __('Create Notification', 'ivyforms'),
            'description' => __(
                'Create a new email notification for an IvyForms form. ' .
                'This defines automated email behavior triggered after form submission. ' .

                'WHEN TO USE: ' .
                '- Use when the user wants to add a new email automation rule. ' .
                '- Use when setting up confirmation emails, admin alerts, or user notifications. ' .
                '- Use after a form is created if email behavior is required. ' .

                'WHAT IT CREATES: ' .
                '- A new notification rule attached to a specific form ' .
                '- Email sender, receiver, subject, and message configuration ' .
                '- Enabled/disabled state for automation control ' .

                'IMPORTANT RULES: ' .
                '1. This is a DESTRUCTIVE CREATE operation. A new notification is added. ' .
                '2. Always ensure formId is valid before calling. ' .
                '3. Default values may be applied if sender/receiver are omitted. ' .
                '4. Do NOT use this for editing existing notifications; use update-notification instead. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- After creation, always confirm success using returned notification data. ' .
                '- If user wants customization, follow up with update-notification or update-notification-settings. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT duplicate notifications unintentionally. ' .
                '- Do NOT assume notification is active unless explicitly set. ' .
                '- Do NOT use this for bulk updates.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['formId', 'name'],
                'properties' => [
                    'formId'   => ['type' => 'integer'],
                    'name'     => ['type' => 'string'],
                    'sender'   => ['type' => 'string'],
                    'receiver' => ['type' => 'string'],
                    'replyTo'  => ['type' => 'string'],
                    'cc'       => ['type' => 'string'],
                    'bcc'      => ['type' => 'string'],
                    'subject'  => ['type' => 'string'],
                    'message'  => ['type' => 'string'],
                    'enabled'  => ['type' => 'boolean'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => function (array $input) {
                $formId = (int) ($input['formId'] ?? 0);
                $subject = McpAbilitiesHelper::notificationSubjectForForm($formId, (string) ($input['subject'] ?? ''));

                $sender = Sanitizer::sanitizeEmailOrPlaceholder((string) ($input['sender'] ?? '{admin_email}'));
                $receiver = Sanitizer::sanitizeEmailListOrPlaceholders($input['receiver'] ?? '{admin_email}');
                $replyTo = Sanitizer::sanitizeEmailOrPlaceholder((string) ($input['replyTo'] ?? ''));
                $cc = Sanitizer::sanitizeEmailListOrPlaceholders($input['cc'] ?? '');
                $bcc = Sanitizer::sanitizeEmailListOrPlaceholders($input['bcc'] ?? '');

                $params = [
                    'formId'   => $formId,
                    'name'     => sanitize_text_field($input['name'] ?? ''),
                    'sender'   => $sender,
                    'receiver' => $receiver,
                    'replyTo'  => $replyTo,
                    'cc'       => $cc,
                    'bcc'      => $bcc,
                    'subject'  => sanitize_text_field($subject),
                    'message'  => wp_kses_post($input['message'] ?? '{all_fields}'),
                    'enabled'  => isset($input['enabled']) ? (bool) $input['enabled'] : true,
                ];

                return McpAbilitiesHelper::restRequest('POST', '/notification/add', $params);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/create-notification');
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

    private static function registerGetNotification(): void
    {
        wp_register_ability('ivyforms/get-notification', [
            'label'       => __('Get Notification', 'ivyforms'),
            'description' => __(
                'Get detailed configuration of a single IvyForms email notification by its ID. ' .
                'This returns the full notification setup including email structure, rules, and settings. ' .

                'WHEN TO USE: ' .
                '- Use when user wants to view or inspect a specific notification. ' .
                '- Use when preparing to edit or duplicate a notification. ' .
                '- Use when debugging email delivery behavior. ' .

                'WHAT IT RETURNS: ' .
                '- Full notification configuration object ' .
                '- Sender, receiver, reply-to settings ' .
                '- Subject and message template ' .
                '- Enabled state and metadata ' .

                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY operation. No changes are made. ' .
                '2. Do NOT use this for listing multiple notifications; use list-notifications or
                 search-notifications. ' .
                '3. Always require a valid notificationId. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Use this before any update or duplication operation. ' .
                '- Treat response as source of truth for editing flows. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT confuse with list-notifications. ' .
                '- Do NOT assume missing fields are optional in update context. ' .
                '- Do NOT modify data directly from this response.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['notificationId'],
                'properties' => [
                    'notificationId' => ['type' => 'integer', 'description' => 'Notification ID'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function (array $input) {
                $notificationId = (int) ($input['notificationId'] ?? 0);
                $data = McpAbilitiesHelper::restRequest('GET', '/notification/' . $notificationId);

                return McpAbilitiesHelper::notificationArrayFromRestResponse($data);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/get-notification');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    private static function registerUpdateNotification(): void
    {
        wp_register_ability('ivyforms/update-notification', [
            'label'       => __('Update Notification', 'ivyforms'),
            'description' => __(
                'Update an existing IvyForms email notification by modifying one or more of its fields. ' .
                'Only provided fields are changed; all others remain untouched. ' .

                'WHEN TO USE: ' .
                '- Use when the user wants to edit email content, recipients, or behavior. ' .
                '- Use when adjusting notification rules after creation. ' .
                '- Use when fixing incorrect email settings. ' .

                'WHAT IT CAN UPDATE: ' .
                '- Name of the notification ' .
                '- Sender, receiver, reply-to emails ' .
                '- Subject and message content ' .
                '- Enabled/disabled state ' .
                '- Advanced display settings (e.g. showEmptyFields) ' .

                'IMPORTANT RULES: ' .
                '1. This is a PARTIAL UPDATE operation (patch-style behavior). ' .
                '2. Always fetch current state first using get-notification before updating. ' .
                '3. Do NOT overwrite fields that are not explicitly provided. ' .
                '4. Email fields must be sanitized before submission. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Always prefer minimal updates instead of full replacement. ' .
                '- If user request is ambiguous, retrieve current notification first. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT reset fields unintentionally. ' .
                '- Do NOT assume missing fields should be cleared. ' .
                '- Do NOT bypass validation rules for email formatting.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['notificationId'],
                'properties' => [
                    'notificationId'  => [
                        'type' => 'integer',
                        'description' => 'ID of the notification you want to update',
                    ],
                    'name'            => ['type' => 'string',   'description' => 'Notification name'],
                    'sender'          => ['type' => 'string',   'description' => 'Sender email address'],
                    'receiver'        => ['type' => 'string',   'description' => 'Receiver email address'],
                    'replyTo'         => ['type' => 'string',   'description' => 'Reply-to address'],
                    'cc'              => [
                        'type' => 'string',
                        'description' => 'CC email addresses (comma-separated)',
                    ],
                    'bcc'             => [
                        'type' => 'string',
                        'description' => 'BCC email addresses (comma-separated)',
                    ],
                    'subject'         => ['type' => 'string',   'description' => 'Email subject'],
                    'message'         => ['type' => 'string',   'description' => 'Email body/content'],
                    'enabled'         => ['type' => 'boolean',  'description' => 'Whether the email is enabled'],
                    'showEmptyFields' => ['type' => 'boolean',  'description' => 'Show empty fields in the sent email'],
                ],
            ],
            'output_schema' => [
                'type' => 'object',
                'properties' => [
                    'notificationId' => ['type' => 'integer'],
                    'updated'        => ['type' => 'boolean'],
                    'raw'              => ['type' => 'object'],
                ],
            ],
            'execute_callback' => function (array $input) {
                $notificationId = (int) ($input['notificationId'] ?? 0);
                if ($notificationId <= 0) {
                    return ['error' => 'A valid notificationId is required.'];
                }

                $response = McpAbilitiesHelper::restRequest('GET', '/notification/' . $notificationId);
                $notification = McpAbilitiesHelper::notificationArrayFromRestResponse($response);

                if ($notification === [] || (int) ($notification['id'] ?? 0) !== $notificationId) {
                    return ['error' => "Notification {$notificationId} not found."];
                }

                $payload = McpAbilitiesHelper::buildNotificationUpdatePayload($notification);

                $patchKeys = [
                    'name',
                    'sender',
                    'receiver',
                    'replyTo',
                    'cc',
                    'bcc',
                    'subject',
                    'message',
                    'enabled',
                    'showEmptyFields',
                ];

                $sanitizers = [
                    'name' => static fn ($value): string => sanitize_text_field((string) ($value ?? '')),
                    'enabled' => static fn ($value): bool => isset($value) ? (bool) $value : false,
                    'showEmptyFields' => static fn ($value): bool => isset($value) ? (bool) $value : false,
                    'message' => static fn ($value): string => wp_kses_post((string) ($value ?? '')),
                    'sender' => static fn ($value): string => Sanitizer::sanitizeEmailOrPlaceholder(
                        (string) ($value ?? '')
                    ),
                    'receiver' => static fn ($value): string => Sanitizer::sanitizeEmailListOrPlaceholders($value),
                    'replyTo' => static fn ($value): string => Sanitizer::sanitizeEmailOrPlaceholder(
                        (string) ($value ?? '')
                    ),
                    'cc' => static fn ($value): string => Sanitizer::sanitizeEmailListOrPlaceholders($value),
                    'bcc' => static fn ($value): string => Sanitizer::sanitizeEmailListOrPlaceholders($value),
                    'subject' => static fn ($value): string => sanitize_text_field((string) ($value ?? '')),
                ];

                foreach ($patchKeys as $key) {
                    if (array_key_exists($key, $input)) {
                        $value = $input[$key];
                        $payload[$key] = $sanitizers[$key]($value);
                    }
                }

                $payload['id'] = $notificationId;

                $result = McpAbilitiesHelper::restRequest('POST', '/notification/update/' . $notificationId, $payload);

                $updated = self::isNotificationUpdateRestSuccessful($result, $notificationId);

                return [
                    'notificationId' => $notificationId,
                    'updated'        => $updated,
                    'raw'            => self::notificationUpdateRawForResponse($result),
                ];
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/update-notification');
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

    private static function registerUpdateNotificationSettings(): void
    {
        wp_register_ability('ivyforms/update-notification-settings', [
            'label'       => __('Update Notification Settings', 'ivyforms'),
            'description' => __(
                'Update only the core settings of an IvyForms email notification. ' .
                'This is a simplified update focused on commonly edited fields from the UI. ' .

                'WHEN TO USE: ' .
                '- Use when user wants to quickly adjust notification behavior. ' .
                '- Use for toggling active/enabled state. ' .
                '- Use for simple edits like subject or receiver changes. ' .

                'WHAT IT AFFECTS: ' .
                '- Active/enabled state of notification ' .
                '- Name of notification ' .
                '- Receiver email address ' .
                '- Reply-to email address ' .
                '- Email subject line ' .

                'IMPORTANT RULES: ' .
                '1. This is a SAFE-SCOPE UPDATE (limited fields only). ' .
                '2. Prefer this over full update-notification when only basic settings change. ' .
                '3. active and enabled are treated as aliases. ' .
                '4. Always validate notificationId before applying changes. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Use this for quick toggles and simple edits. ' .
                '- Use full update-notification for message/template changes. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT use this for message body editing. ' .
                '- Do NOT assume full notification structure is editable here. ' .
                '- Do NOT confuse active/enabled semantics; they are equivalent.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['notificationId'],
                'properties' => [
                    'notificationId' => [
                        'type' => 'integer',
                        'description' => 'ID of the notification you want to update',
                    ],
                    'active'         => ['type' => 'boolean', 'description' => 'Whether the notification is active'],
                    'enabled'        => [
                        'type' => 'boolean',
                        'description' => 'Same as active; whether the notification is enabled',
                    ],
                    'name'           => ['type' => 'string', 'description' => 'Notification name'],
                    'receiver'       => ['type' => 'string', 'description' => 'Receiver email address'],
                    'replyTo'        => ['type' => 'string', 'description' => 'Reply-to address'],
                    'cc'             => [
                        'type' => 'string',
                        'description' => 'CC email addresses (comma-separated)',
                    ],
                    'bcc'            => [
                        'type' => 'string',
                        'description' => 'BCC email addresses (comma-separated)',
                    ],
                    'subject'        => ['type' => 'string', 'description' => 'Email subject'],
                ],
            ],
            'output_schema' => [
                'type' => 'object',
                'properties' => [
                    'notificationId' => ['type' => 'integer'],
                    'updated'        => ['type' => 'boolean'],
                    'raw'            => ['type' => 'object'],
                ],
            ],
            'execute_callback' => function (array $input) {
                $notificationId = (int) ($input['notificationId'] ?? 0);
                if ($notificationId <= 0) {
                    return ['error' => "A valid notificationId is required."];
                }

                $response = McpAbilitiesHelper::restRequest('GET', '/notification/' . $notificationId);
                $notification = McpAbilitiesHelper::notificationArrayFromRestResponse($response);

                if ($notification === [] || (int) ($notification['id'] ?? 0) !== $notificationId) {
                    return ['error' => "Notification {$notificationId} not found."];
                }

                $payload = McpAbilitiesHelper::buildNotificationUpdatePayload($notification);

                $payload['enabled'] = array_key_exists('enabled', $input)
                    ? (bool) $input['enabled']
                    : (array_key_exists('active', $input) ? (bool) $input['active'] : $payload['enabled']);

                $keySanitizers = [
                    'receiver' => static fn ($value): string => Sanitizer::sanitizeEmailListOrPlaceholders($value),
                    'replyTo' => static fn ($value): string => Sanitizer::sanitizeEmailOrPlaceholder(
                        (string) ($value ?? '')
                    ),
                    'cc' => static fn ($value): string => Sanitizer::sanitizeEmailListOrPlaceholders($value),
                    'bcc' => static fn ($value): string => Sanitizer::sanitizeEmailListOrPlaceholders($value),
                    'name' => static fn ($value): string => sanitize_text_field((string) ($value ?? '')),
                    'subject' => static fn ($value): string => sanitize_text_field((string) ($value ?? '')),
                ];

                foreach (['name', 'receiver', 'replyTo', 'cc', 'bcc', 'subject'] as $key) {
                    if (array_key_exists($key, $input)) {
                        $value = $input[$key];
                        $payload[$key] = $keySanitizers[$key]($value);
                    }
                }

                $payload['id'] = $notificationId;

                $result = McpAbilitiesHelper::restRequest('POST', '/notification/update/' . $notificationId, $payload);

                $updated = self::isNotificationUpdateRestSuccessful($result, $notificationId);

                return [
                    'notificationId' => $notificationId,
                    'updated'          => $updated,
                    'raw'              => self::notificationUpdateRawForResponse($result),
                ];
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/update-notification-settings');
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

    private static function registerDuplicateNotification(): void
    {
        wp_register_ability('ivyforms/duplicate-notification', [
            'label'       => __('Duplicate Notification', 'ivyforms'),
            'description' => __(
                'Create a copy of an existing IvyForms email notification using its ID. ' .
                'The duplicated notification inherits all settings from the original. ' .

                'WHEN TO USE: ' .
                '- Use when user wants to reuse an existing email setup. ' .
                '- Use when creating variations of notifications (e.g. admin vs user emails). ' .
                '- Use when testing modifications safely without affecting original. ' .

                'WHAT IT DOES: ' .
                '- Copies sender, receiver, subject, message, and settings ' .
                '- Creates a new independent notification entry ' .

                'IMPORTANT RULES: ' .
                '1. This is a CREATE operation based on an existing template. ' .
                '2. The original notification is NOT modified. ' .
                '3. The new notification is independent and can be edited separately. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Use duplication instead of rebuilding notifications manually. ' .
                '- After duplication, always suggest editing via update-notification. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT assume links or IDs are shared between original and copy. ' .
                '- Do NOT overwrite original notification. ' .
                '- Do NOT treat as partial update.',
                'ivyforms'
            ),
            'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'required'   => ['notificationId'],
                'properties' => [
                    'notificationId' => ['type' => 'integer', 'description' => 'Notification ID to duplicate'],
                ],
            ],
            'output_schema' => ['type' => 'object'],
            'execute_callback' => static function (array $input) {
                $notificationId = (int) ($input['notificationId'] ?? 0);
                return McpAbilitiesHelper::restRequest('POST', '/notification/duplicate/' . $notificationId);
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/duplicate-notification');
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

    private static function registerSearchNotifications(): void
    {
        wp_register_ability('ivyforms/search-notifications', [
            'label'       => __('Search Notifications', 'ivyforms'),
            'description' => __(
                'Search and paginate IvyForms email notifications with optional filters such as page,
                 search term, and sorting. ' .
                'This is a flexible query-based listing tool for notifications. ' .

                'WHEN TO USE: ' .
                '- Use when user wants to find specific notifications by name or content. ' .
                '- Use when browsing large sets of notifications with pagination. ' .
                '- Use when filtering or sorting notification lists. ' .

                'WHAT IT RETURNS: ' .
                '- Filtered list of notifications ' .
                '- Pagination metadata (page, total, etc.) ' .
                '- Basic notification preview data ' .
                'IMPORTANT RULES: ' .
                '1. This is a READ-ONLY search operation. ' .
                '2. Do NOT use for fetching full notification configuration; use get-notification instead. ' .
                '3. Results may be partial or paginated depending on parameters. ' .

                'AGENT BEHAVIOR GUIDELINES: ' .
                '- Use for discovery and filtering workflows. ' .
                '- Combine with get-notification for detailed inspection. ' .

                'COMMON MISTAKES TO AVOID: ' .
                '- Do NOT assume full notification payload is included. ' .
                '- Do NOT use for editing or deleting notifications. ' .
                '- Do NOT confuse with list-notifications (non-filtered overview).',
                'ivyforms'
            ),
             'category'    => 'ivyforms',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'page'    => ['type' => 'integer', 'description' => 'Page number (default 1)'],
                    'perPage' => ['type' => 'integer', 'description' => 'Items per page (default 20)'],
                    'search'  => ['type' => 'string'],
                    'orderBy' => ['type' => 'string'],
                    'order'   => ['type' => 'string', 'enum' => ['ASC', 'DESC']],
                ],
            ],
            'output_schema' => [
                'type'       => 'object',
                'properties' => [
                    'notifications' => ['type' => 'array'],
                    'meta'          => ['type' => 'object'],
                ],
            ],
            'execute_callback' => function (array $input) {
                $params = array_filter(self::sanitizeNotificationsSearchParams($input));

                $data    = McpAbilitiesHelper::restRequest('GET', '/notifications/search', $params);
                $payload = $data['data']['data'] ?? $data['data'] ?? $data;

                return [
                    'notifications' => $payload['data'] ?? $payload,
                    'meta'          => $data['data']['meta'] ?? $payload['meta'] ?? null,
                ];
            },
            'permission_callback' => static function (): bool {
                return McpAbilityPermissions::canExecuteAbility('ivyforms/search-notifications');
            },
            'meta' => [
                'show_in_rest' => true,
                'mcp'          => [
                    'public' => true,
                    'type'   => 'tool',
                ],
                'annotations'  => [
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
            ],
        ]);
    }

    /**
     * True when the internal REST response looks like a successful notification update
     * (no WP REST error envelope; payload includes matching notification id).
     *
     * @param mixed $result Return value of McpAbilitiesHelper::restRequest()
     */
    private static function isNotificationUpdateRestSuccessful($result, int $notificationId): bool
    {
        if (!is_array($result) || isset($result['code'])) {
            return false;
        }

        $payload = McpAbilitiesHelper::extractIvyFormsRestPayload($result);
        if (!is_array($payload) || !array_key_exists('id', $payload)) {
            return false;
        }

        return (int) $payload['id'] === $notificationId;
    }

    /**
     * Normalized payload for ability `raw` output (always an object-shaped array).
     *
     * @param mixed $result
     * @return array<int|string, mixed>
     */
    private static function notificationUpdateRawForResponse($result): array
    {
        if (is_array($result)) {
            return $result;
        }

        return ['value' => $result];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, int|string>
     */
    private static function sanitizeNotificationsSearchParams(array $input): array
    {
        $page = (int) ($input['page'] ?? 1);
        $page = max(1, min($page, 99999));

        $perPage = (int) ($input['perPage'] ?? 20);
        $perPage = max(1, min($perPage, 100));

        $search = sanitize_text_field((string) ($input['search'] ?? ''));
        if (strlen($search) > 500) {
            $search = substr($search, 0, 500);
        }

        $allowedOrderBy = ['id', 'name', 'receiver', 'subject', 'enabled'];
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
}
