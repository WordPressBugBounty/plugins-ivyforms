<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Controllers\Permissions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Repository\Permissions\AccessRulesRepository;
use IvyForms\Repository\Form\FormRepositoryInterface;
use IvyForms\Services\Permissions\PermissionCatalog;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class GetAccessRulesController extends Controller
{
    private AccessRulesRepository $accessRulesRepository;
    private FormRepositoryInterface $formRepository;

    public function __construct(
        AccessRulesRepository $accessRulesRepository,
        FormRepositoryInterface $formRepository
    ) {
        $this->accessRulesRepository = $accessRulesRepository;
        $this->formRepository        = $formRepository;
    }

    /**
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $payload              = $this->accessRulesRepository->getPayload();
        $rules                = $this->stripDeletedFormIdsFromRules($payload['rules']);
        $roleNames            = wp_roles()->role_names;
        $permissionLabelMap   = PermissionCatalog::labelMap();
        $permissionStrings    = BackendStrings::getPermissionsStrings();
        $allFormsLabel        = (string) ($permissionStrings['all_forms'] ?? '');
        $allEmailsForRole     = (string) ($permissionStrings['all_emails_for_role'] ?? '');
        $formNameMap          = $this->loadFormNameMap($rules);

        $rows = [];
        foreach ($rules as $rule) {
            $rows[] = $this->hydrateRow(
                $rule,
                $roleNames,
                $permissionLabelMap,
                $formNameMap,
                $allFormsLabel,
                $allEmailsForRole
            );
        }

        return new WP_REST_Response(
            [
                'message' => BackendStrings::getCommonStrings()['ok'],
                'rules'   => $rows,
            ],
            200
        );
    }

    /**
     * Drop form IDs that no longer exist so clients do not re-submit stale scopes.
     *
     * @param list<array<string,mixed>> $rules
     * @return list<array<string,mixed>>
     */
    private function stripDeletedFormIdsFromRules(array $rules): array
    {
        $allFormIds = $this->collectPositiveFormIdsFromRules($rules);
        if ($allFormIds === []) {
            return $rules;
        }

        $existingSet = array_flip($this->formRepository->filterExistingIds($allFormIds));
        $sanitized   = [];
        foreach ($rules as $rule) {
            $sanitized[] = $this->sanitizeRuleFormIds($rule, $existingSet);
        }

        return $sanitized;
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return list<int>
     */
    private function collectPositiveFormIdsFromRules(array $rules): array
    {
        $allFormIds = [];
        foreach ($rules as $rule) {
            foreach ($this->positiveFormIdsFromRule($rule) as $formId) {
                $allFormIds[] = $formId;
            }
        }

        return $allFormIds;
    }

    /**
     * @param array<string,mixed> $rule
     * @return list<int>
     */
    private function positiveFormIdsFromRule(array $rule): array
    {
        $ruleFormIds = $rule['form_ids'] ?? null;
        if (!is_array($ruleFormIds)) {
            return [];
        }

        $ids = [];
        foreach ($ruleFormIds as $formId) {
            $normalizedId = (int) $formId;
            if ($normalizedId > 0) {
                $ids[] = $normalizedId;
            }
        }

        return $ids;
    }

    /**
     * @param array<string,mixed> $rule
     * @param array<int, int> $existingSet
     * @return array<string,mixed>
     */
    private function sanitizeRuleFormIds(array $rule, array $existingSet): array
    {
        $ruleFormIds = $rule['form_ids'] ?? null;
        if (!is_array($ruleFormIds)) {
            return $rule;
        }

        $validIds = [];
        foreach ($ruleFormIds as $formId) {
            $normalizedId = (int) $formId;
            if ($normalizedId > 0 && isset($existingSet[ $normalizedId ])) {
                $validIds[] = $normalizedId;
            }
        }

        $rule['form_ids'] = array_values(array_unique($validIds));

        return $rule;
    }

    /**
     * @param list<array<string,mixed>> $rules
     * @return array<int, string>
     */
    private function loadFormNameMap(array $rules): array
    {
        $formIds = array_values(array_unique($this->collectPositiveFormIdsFromRules($rules)));
        if ($formIds === []) {
            return [];
        }

        return $this->formRepository->getNamesByIds($formIds);
    }

    /**
     * @param array<string,mixed> $rule
     * @param array<string, string> $roleNames
     * @param array<string, string> $permissionLabelMap
     * @param array<int, string> $formNameMap
     * @return array<string, mixed>
     */
    private function hydrateRow(
        array $rule,
        array $roleNames,
        array $permissionLabelMap,
        array $formNameMap,
        string $allFormsLabel,
        string $allEmailsForRole
    ): array {
        $base = [
            'id'          => $rule['id'] ?? '',
            'type'        => $rule['type'] ?? '',
            'permissions' => $rule['permissions'] ?? [],
            'form_ids'    => $rule['form_ids'] ?? null,
            'created_at'  => isset($rule['created_at']) ? (int) $rule['created_at'] : 0,
            'updated_at'  => isset($rule['updated_at']) ? (int) $rule['updated_at'] : 0,
            'user_id'     => null,
            'role_slug'   => '',
            'role_name'   => '',
            'email_display' => '',
        ];

        $permissions = is_array($rule['permissions'] ?? null) ? $rule['permissions'] : [];
        $base['forms_display'] = $this->formatFormsDisplay(
            $rule['form_ids'] ?? null,
            $formNameMap,
            $allFormsLabel
        );
        $base['permissions_display'] = $this->formatPermissionsDisplay($permissions, $permissionLabelMap);

        if (($rule['type'] ?? '') === 'role') {
            $slug = (string) ($rule['role_slug'] ?? '');
            $base['role_slug']       = $slug;
            $base['role_name']       = $roleNames[ $slug ] ?? $slug;
            $base['email_display']   = $allEmailsForRole;

            return $base;
        }

        $uid = isset($rule['user_id']) ? (int) $rule['user_id'] : 0;
        $base['user_id']       = $uid;
        $user                  = get_userdata($uid);
        $base['email_display'] = $user && $user->user_email ? (string) $user->user_email : '';

        return $base;
    }

    /**
     * @param mixed $formIds
     * @param array<int, string> $formNameMap
     */
    private function formatFormsDisplay($formIds, array $formNameMap, string $allFormsLabel): string
    {
        if ($formIds === null) {
            return $allFormsLabel;
        }
        if (!is_array($formIds) || $formIds === []) {
            return '';
        }

        $titles = [];
        foreach ($formIds as $formId) {
            $normalizedId = (int) $formId;
            $titles[]     = $formNameMap[ $normalizedId ] ?? (string) $normalizedId;
        }

        return implode(', ', $titles);
    }

    /**
     * @param mixed[] $permissions
     * @param array<string, string> $permissionLabelMap
     */
    private function formatPermissionsDisplay(array $permissions, array $permissionLabelMap): string
    {
        $labels = [];
        foreach (PermissionCatalog::sanitize($permissions) as $key) {
            $labels[] = $permissionLabelMap[ $key ] ?? $key;
        }

        return implode(', ', $labels);
    }
}
