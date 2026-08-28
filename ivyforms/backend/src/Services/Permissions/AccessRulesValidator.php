<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Services\Permissions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Repository\Form\FormRepositoryInterface;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Validates payloads for /permissions/access-rules updates.
 */
class AccessRulesValidator
{
    /** @var array<string, string> */
    private array $exceptionStrings;

    private FormRepositoryInterface $formRepository;

    public function __construct(
        FormRepositoryInterface $formRepository
    ) {
        $this->formRepository     = $formRepository;
        $this->exceptionStrings   = BackendStrings::getExceptionStrings();
    }

    /**
     * @param mixed[] $rules
     * @return list<array<string,mixed>>
     *
     * @throws ValidationException
     */
    public function validateAndNormalize(array $rules): array
    {
        if (!array_is_list($rules)) {
            throw new ValidationException($this->exceptionStrings['rules_payload_required']);
        }

        $normalized   = [];
        $seenIds      = [];
        $ruleDataList = [];
        $allFormIds   = [];

        foreach ($rules as $index => $rule) {
            $ruleData       = $this->validateRuleBase($rule, (int) $index, $seenIds);
            $ruleDataList[] = $ruleData;

            if (!array_key_exists('form_ids', $ruleData) || $ruleData['form_ids'] === null) {
                continue;
            }
            if (!is_array($ruleData['form_ids'])) {
                continue;
            }
            foreach ($ruleData['form_ids'] as $formId) {
                $allFormIds[] = (int) $formId;
            }
        }

        $existingFormIds = $this->buildExistingFormIdSet($allFormIds);

        foreach ($ruleDataList as $ruleData) {
            $permissions = $this->normalizePermissions($ruleData);
            $formIds     = $this->normalizeFormIds($ruleData, $existingFormIds);
            $timestamps  = $this->resolveTimestamps($ruleData);

            if ($ruleData['type'] === 'role') {
                $normalized[] = $this->buildRoleRule($ruleData, $permissions, $formIds, $timestamps);
                continue;
            }
            $normalized[] = $this->buildUserRule($ruleData, $permissions, $formIds, $timestamps);
        }

        return $normalized;
    }

    /**
     * @param list<int> $formIds
     * @return array<int, true>
     */
    private function buildExistingFormIdSet(array $formIds): array
    {
        $existing = $this->formRepository->filterExistingIds($formIds);
        $set      = [];
        foreach ($existing as $id) {
            $set[ $id ] = true;
        }

        return $set;
    }

    /**
     * @param mixed $rule
     * @param array<string,bool> $seenIds
     * @return array<string,mixed>
     *
     * @throws ValidationException
     */
    private function validateRuleBase($rule, int $index, array &$seenIds): array
    {
        if (!is_array($rule)) {
            throw new ValidationException(sprintf(
                $this->exceptionStrings['permissions_rule_invalid_index'],
                $index
            ));
        }

        $type = isset($rule['type']) ? (string) $rule['type'] : '';
        if (!in_array($type, ['role', 'user'], true)) {
            throw new ValidationException($this->exceptionStrings['permissions_rule_type_required']);
        }

        $id = isset($rule['id']) ? (string) $rule['id'] : '';
        if ($id === '') {
            throw new ValidationException($this->exceptionStrings['permissions_rule_id_required']);
        }
        if (isset($seenIds[$id])) {
            throw new ValidationException($this->exceptionStrings['permissions_rule_duplicate_id']);
        }
        $seenIds[$id] = true;

        $rule['type'] = $type;
        $rule['id']   = $id;

        return $rule;
    }

    /**
     * @param array<string,mixed> $rule
     * @return list<string>
     *
     * @throws ValidationException
     */
    private function normalizePermissions(array $rule): array
    {
        $raw = isset($rule['permissions']) && is_array($rule['permissions'])
            ? $rule['permissions']
            : [];
        $permissions = PermissionCatalog::sanitizeWithDependencies($raw);
        if ($permissions === []) {
            throw new ValidationException($this->exceptionStrings['permissions_rule_grant_required']);
        }

        return $permissions;
    }

    /**
     * @param array<string,mixed> $rule
     * @param array<int, true> $existingFormIds
     * @return list<int>|null
     *
     * @throws ValidationException
     */
    private function normalizeFormIds(array $rule, array $existingFormIds): ?array
    {
        if (!array_key_exists('form_ids', $rule) || $rule['form_ids'] === null) {
            return null;
        }
        if (!is_array($rule['form_ids'])) {
            throw new ValidationException($this->exceptionStrings['permissions_form_ids_invalid']);
        }

        $requestedIds = [];
        $formIds      = [];
        foreach ($rule['form_ids'] as $formId) {
            $normalizedFormId = (int) $formId;
            if ($normalizedFormId <= 0) {
                throw new ValidationException($this->exceptionStrings['permissions_form_id_invalid']);
            }
            $requestedIds[] = $normalizedFormId;
            if (!isset($existingFormIds[ $normalizedFormId ])) {
                continue;
            }
            $formIds[] = $normalizedFormId;
        }

        $formIds = array_values(array_unique($formIds));
        if ($requestedIds !== [] && $formIds === []) {
            $missing = array_values(array_unique($requestedIds));
            throw new ValidationException(
                sprintf(
                    $this->exceptionStrings['permissions_deleted_forms'],
                    implode(', ', array_map('strval', $missing))
                )
            );
        }

        return $formIds;
    }

    /**
     * @param array<string,mixed> $rule
     * @return array{created_at:int,updated_at:int}
     */
    private function resolveTimestamps(array $rule): array
    {
        $createdAt = isset($rule['created_at']) ? (int) $rule['created_at'] : 0;
        $updatedAt = isset($rule['updated_at']) ? (int) $rule['updated_at'] : 0;
        $now       = time();
        if ($createdAt <= 0) {
            $createdAt = $now;
        }
        if ($updatedAt <= 0) {
            $updatedAt = $now;
        }

        return [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }

    /**
     * @param array<string,mixed> $rule
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @param array{created_at:int,updated_at:int} $timestamps
     * @return array<string,mixed>
     *
     * @throws ValidationException
     */
    private function buildRoleRule(array $rule, array $permissions, ?array $formIds, array $timestamps): array
    {
        $slug = isset($rule['role_slug']) ? (string) $rule['role_slug'] : '';
        if ($slug === '' || !wp_roles()->is_role($slug)) {
            throw new ValidationException($this->exceptionStrings['permissions_role_slug_invalid']);
        }

        return [
            'id'          => $rule['id'],
            'type'        => 'role',
            'role_slug'   => $slug,
            'permissions' => $permissions,
            'form_ids'    => $formIds,
            'created_at'  => $timestamps['created_at'],
            'updated_at'  => $timestamps['updated_at'],
        ];
    }

    /**
     * @param array<string,mixed> $rule
     * @param list<string> $permissions
     * @param list<int>|null $formIds
     * @param array{created_at:int,updated_at:int} $timestamps
     * @return array<string,mixed>
     *
     * @throws ValidationException
     */
    private function buildUserRule(array $rule, array $permissions, ?array $formIds, array $timestamps): array
    {
        $userId = isset($rule['user_id']) ? (int) $rule['user_id'] : 0;
        if ($userId <= 0 || !get_user_by('id', $userId)) {
            throw new ValidationException($this->exceptionStrings['permissions_user_id_invalid']);
        }

        return [
            'id'          => $rule['id'],
            'type'        => 'user',
            'user_id'     => $userId,
            'permissions' => $permissions,
            'form_ids'    => $formIds,
            'created_at'  => $timestamps['created_at'],
            'updated_at'  => $timestamps['updated_at'],
        ];
    }
}
