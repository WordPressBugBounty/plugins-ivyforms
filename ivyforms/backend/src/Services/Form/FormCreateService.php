<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Form;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\Form\Form;
use IvyForms\Factory\Form\FormFactory;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Permissions\AccessRulesFormScopeExtender;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Shared create path for blank/template and AI-generated forms.
 */
class FormCreateService
{
    private FormService $formService;
    private FieldService $fieldService;
    private NotificationService $notificationService;
    private ConfirmationService $confirmationService;
    private AccessRulesFormScopeExtender $accessRulesFormScopeExtender;

    public function __construct(
        FormService $formService,
        FieldService $fieldService,
        NotificationService $notificationService,
        ConfirmationService $confirmationService,
        AccessRulesFormScopeExtender $accessRulesFormScopeExtender
    ) {
        $this->formService                  = $formService;
        $this->fieldService                 = $fieldService;
        $this->notificationService          = $notificationService;
        $this->confirmationService          = $confirmationService;
        $this->accessRulesFormScopeExtender = $accessRulesFormScopeExtender;
    }

    /**
     * Persist a sanitized form payload with defaults, fields, hooks, and access scope.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ValidationException
     */
    public function createFromSanitizedParams(array $params): array
    {
        $form = FormFactory::create($params);
        $formId = $this->formService->createForm($form);
        if (!$formId) {
            throw new QueryExecutionException(
                BackendStrings::getAllFormsStrings()['failed_to_create_form']
            );
        }

        try {
            $confirmationId = $this->persistDefaultsAndFields($formId, $params);
        } catch (\Throwable $e) {
            $this->deleteOrphanedForm($formId);
            throw $e;
        }

        $form->setId($formId);
        $this->finalizeCreate($form, $formId);

        return array_merge($form->toArray(), ['confirmationId' => $confirmationId]);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ValidationException
     */
    private function persistDefaultsAndFields(int $formId, array $params): int
    {
        $adminEmail = get_option('admin_email');
        $formName = is_string($params['name'] ?? null) ? $params['name'] : '';
        $this->notificationService->createDefaultNotificationForForm(
            $formId,
            $formName,
            $adminEmail
        );

        $confirmationId = $this->confirmationService->createDefaultConfirmationForForm($formId);

        if (!empty($params['fields'])) {
            $this->fieldService->saveFieldsWithOptions($params['fields'], $formId);
        }

        return $confirmationId;
    }

    private function finalizeCreate(Form $form, int $formId): void
    {
        /**
         * After a form row (and related defaults) are persisted.
         *
         * @param Form $form
         * @param int $formId
         * @param string $context create|update
         */
        do_action('ivyforms/form/after_persist', $form, $formId, 'create');

        $this->accessRulesFormScopeExtender->appendCreatedFormToCurrentUserScope($formId);
    }

    private function deleteOrphanedForm(int $formId): void
    {
        $this->rollbackCreatedFormRecords($formId);

        try {
            $this->formService->deleteForm($formId);
        } catch (\Throwable $cleanupError) {
            $this->logCleanupFailure($formId, $cleanupError);
        }
    }

    private function rollbackCreatedFormRecords(int $formId): void
    {
        $formIds = [$formId];

        try {
            $this->notificationService->deleteNotificationsByFormIds($formIds);
        } catch (\Throwable $cleanupError) {
            $this->logCleanupFailure($formId, $cleanupError);
        }

        try {
            $this->confirmationService->deleteConfirmationsByFormIds($formIds);
        } catch (\Throwable $cleanupError) {
            $this->logCleanupFailure($formId, $cleanupError);
        }

        try {
            $this->fieldService->deleteFieldsByFormIds($formIds);
        } catch (\Throwable $cleanupError) {
            $this->logCleanupFailure($formId, $cleanupError);
        }
    }

    private function logCleanupFailure(int $formId, \Throwable $cleanupError): void
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        error_log(
            sprintf(
                'IvyForms: failed to delete orphaned form %d after create failure: %s',
                $formId,
                $cleanupError->getMessage()
            )
        );
    }
}
