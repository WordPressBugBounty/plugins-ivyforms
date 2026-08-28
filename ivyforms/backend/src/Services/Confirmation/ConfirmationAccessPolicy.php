<?php

namespace IvyForms\Services\Confirmation;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Entity\Confirmation\Confirmation;
use IvyForms\Repository\Confirmation\ConfirmationRepositoryInterface;
use IvyForms\Services\Translations\BackendStrings;

/**
 * License-aware confirmation management rules (Lite downgrade + Pro filters).
 */
class ConfirmationAccessPolicy
{
    private ConfirmationRepositoryInterface $confirmationRepository;

    public function __construct(ConfirmationRepositoryInterface $confirmationRepository)
    {
        $this->confirmationRepository = $confirmationRepository;
    }

    /**
     * @param int $formId
     * @param callable $createDefault fn(int $formId): int
     * @return int
     */
    public function ensureDefaultExists(int $formId, callable $createDefault): int
    {
        if ($this->confirmationRepository->countByFormId($formId) === 0) {
            return (int) $createDefault($formId);
        }

        $confirmations = $this->confirmationRepository->getAllById($formId);
        foreach ($confirmations as $confirmation) {
            if ($confirmation->isDefault()) {
                return $confirmation->getId();
            }
        }

        return $confirmations[0]->getId();
    }

    /**
     * @param array<Confirmation> $confirmations
     */
    public function resolvePrimaryEditableId(array $confirmations): int
    {
        foreach ($confirmations as $confirmation) {
            if ($confirmation->isEnabled()) {
                return $confirmation->getId();
            }
        }

        return $confirmations[0]->getId();
    }

    /**
     * @param int $confirmationId
     * @param int $formId
     * @param string $action
     * @param int $primaryEditableId
     * @return bool
     */
    public function canManage(int $confirmationId, int $formId, string $action, int $primaryEditableId): bool
    {
        $default = $this->resolveDefaultManagePermission($confirmationId, $formId, $action, $primaryEditableId);

        /**
         * @param bool $default
         * @param int $confirmationId
         * @param int $formId
         * @param string $action
         */
        return (bool) apply_filters(
            'ivyforms/can_manage_confirmation',
            $default,
            $confirmationId,
            $formId,
            $action
        );
    }

    /**
     * @param int $confirmationId
     * @param int $formId
     * @param string $action
     * @param int $primaryEditableId
     * @return bool
     */
    private function resolveDefaultManagePermission(
        int $confirmationId,
        int $formId,
        string $action,
        int $primaryEditableId
    ): bool {
        if (apply_filters('ivyforms/can_create_multiple_confirmations', false, $formId)) {
            return true;
        }

        if ($this->confirmationRepository->countByFormId($formId) <= 1) {
            return $action === 'edit';
        }

        if ($action === 'edit') {
            return $confirmationId === $primaryEditableId;
        }

        return false;
    }

    /**
     * @param int $confirmationId
     * @param int $formId
     * @param string $action
     * @return void
     * @throws ForbiddenException
     */
    public function assertCanManage(int $confirmationId, int $formId, string $action): void
    {
        $confirmations = $this->confirmationRepository->getAllById($formId);
        $primaryId = $this->resolvePrimaryEditableId($confirmations);

        if (!$this->canManage($confirmationId, $formId, $action, $primaryId)) {
            throw new ForbiddenException(
                BackendStrings::getSettingsFormBuilderStrings()['confirmation_locked_pro_required']
            );
        }
    }

    /**
     * @param int $formId
     * @return void
     * @throws ForbiddenException
     */
    public function assertCanAdd(int $formId): void
    {
        $count = $this->confirmationRepository->countByFormId($formId);
        if ($count >= 1 && !apply_filters('ivyforms/can_create_multiple_confirmations', false, $formId)) {
            throw new ForbiddenException(
                BackendStrings::getSettingsFormBuilderStrings()['multiple_confirmations_pro_required']
            );
        }
    }
}
