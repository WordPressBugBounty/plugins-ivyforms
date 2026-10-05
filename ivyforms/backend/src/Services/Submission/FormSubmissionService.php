<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\Services\Submission;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Helpers\EntryHelper;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Entity\Form\Form;
use IvyForms\Factory\Entry\EntryFactory;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Field\FieldDuplicateValidationService;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Mailer\MailerService;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Payment\FormSubmissionPaymentService;
use IvyForms\Services\Payment\MoneyMinorUnits;
use IvyForms\Services\Placeholder\PlaceholderService;
use IvyForms\Services\Security\SecurityService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Response;

/**
 * Validates and processes form submissions (embed and public API).
 */
class FormSubmissionService
{
    private SubmissionUploadService $submissionUploadService;
    private FormService $formService;
    private FieldService $fieldService;
    private FieldDuplicateValidationService $fieldDuplicateValidationService;
    private SecurityService $securityService;
    private EntryService $entryService;
    private NotificationService $notificationService;
    private MailerService $mailerService;
    private ConfirmationService $confirmationService;
    private FormSubmissionPaymentService $formSubmissionPaymentService;

    public function __construct(
        SubmissionUploadService $submissionUploadService,
        FormService $formService,
        FieldService $fieldService,
        FieldDuplicateValidationService $fieldDuplicateValidationService,
        SecurityService $securityService,
        EntryService $entryService,
        NotificationService $notificationService,
        MailerService $mailerService,
        ConfirmationService $confirmationService,
        FormSubmissionPaymentService $formSubmissionPaymentService
    ) {
        $this->submissionUploadService = $submissionUploadService;
        $this->formService = $formService;
        $this->fieldService = $fieldService;
        $this->fieldDuplicateValidationService = $fieldDuplicateValidationService;
        $this->securityService = $securityService;
        $this->entryService = $entryService;
        $this->notificationService = $notificationService;
        $this->mailerService = $mailerService;
        $this->confirmationService = $confirmationService;
        $this->formSubmissionPaymentService = $formSubmissionPaymentService;
    }

    /**
     * @param array<string, mixed> $params Normalized submission payload
     * @param array<string, mixed> $uploadedFiles Multipart files from the transport layer
     *
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException|NotFoundException|ForbiddenException|ValidationException
     */
    public function processSubmission(
        array $params,
        array $uploadedFiles = [],
        ?int $formIdOverride = null
    ): array {
        $context = $this->buildSubmissionContext($params, $uploadedFiles, $formIdOverride);

        $duplicateResult = $this->buildDuplicateResultIfNeeded($context);
        if ($duplicateResult !== null) {
            return $duplicateResult;
        }

        do_action(
            'ivyforms/form/before_submission',
            $context['formId'],
            $context['submissionData'],
            $context['formFields']
        );

        $paymentOutcome = $this->formSubmissionPaymentService->processWhenRequired(
            $context['params'],
            $context['form'],
            $context['formFields'],
            $context['submissionData'],
            $this->formSubmissionPaymentService->findPaymentMethodField($context['formFields'])
        );

        $earlyPaymentResult = $this->buildEarlyPaymentResult($paymentOutcome);
        if ($earlyPaymentResult !== null) {
            return $earlyPaymentResult;
        }

        $entry = $this->persistEntryWithOrphanProtection(
            $context['form'],
            $context['formFields'],
            $context['submissionData'],
            $context['formId'],
            $paymentOutcome
        );
        if (isset($entry['payment_error'])) {
            return $entry;
        }

        $paymentPersistenceResult = $this->persistPaymentWithOrphanProtection(
            $entry,
            $context['formId'],
            $paymentOutcome
        );
        if ($paymentPersistenceResult !== null) {
            return $paymentPersistenceResult;
        }

        $entryId = (int) ($entry['id'] ?? 0);
        if ($entryId > 0 && !empty($paymentOutcome['squareOrderId'])) {
            $this->formSubmissionPaymentService->recordOrderForEntry(
                $entryId,
                (string) $paymentOutcome['squareOrderId'],
                isset($paymentOutcome['squareCustomerId']) ? (string) $paymentOutcome['squareCustomerId'] : null,
                MoneyMinorUnits::toMinor(
                    (float) $paymentOutcome['paymentTotal'],
                    (string) $paymentOutcome['paymentCurrency']
                ),
                (string) $paymentOutcome['paymentCurrency']
            );
        }
        $entryId = $entry['id'] ?? null;
        $placeholderData = $this->buildPlaceholderData($context['formFields'], $context['submissionData'], $entryId);
        $notificationsResult = $this->dispatchNotifications($context, $placeholderData);
        $confirmationPayload = $this->confirmationService->sanitizeProcessedPayload(
            $this->confirmationService->processConfirmations(
                $context['formId'],
                $context['submissionData'],
                $context['formFields'],
                $placeholderData['fieldData'],
                $placeholderData['generalData'],
                $context['fieldLabels']
            )
        );

        do_action(
            'ivyforms/form/after_submission',
            $context['formId'],
            $context['submissionData'],
            $context['formFields'],
            $entryId
        );

        return $this->buildSuccessResult($entry, $notificationsResult, $confirmationPayload);
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $uploadedFiles
     * @param array<\IvyForms\Entity\Field\Field> $formFields
     *
     * @return array<string, mixed>
     */
    public function mergeUploadedFilesIntoValues(
        array $params,
        array $uploadedFiles,
        array $formFields
    ): array {
        return $this->submissionUploadService->mergeUploadedFilesIntoValues(
            $params,
            $uploadedFiles,
            $formFields
        );
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $uploadedFiles
     *
     * @return array{
     *     formId: int,
     *     form: Form,
     *     formFields: array<int, mixed>,
     *     submissionData: array<string, mixed>,
     *     params: array<string, mixed>,
     *     fieldLabels: array<string, string>
     * }
     *
     * @throws InvalidArgumentException|NotFoundException|ForbiddenException|ValidationException
     */
    private function buildSubmissionContext(
        array $params,
        array $uploadedFiles,
        ?int $formIdOverride = null
    ): array {
        if ($formIdOverride !== null) {
            $params['formId'] = $formIdOverride;
        }

        if (empty($params) || !isset($params['values']) || !is_array($params['values'])) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $formId = Sanitizer::sanitizeId((int) ($params['formId'] ?? 0));

        if ($formId <= 0) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_form_id']
            );
        }

        $form = $this->formService->getFormById($formId);
        $formFields = $this->fieldService->getAllFields($form->getId());
        $this->fieldService->validateFieldsType($formFields);

        $fieldLabels = PlaceholderService::buildFieldLabels($formFields);
        $this->securityService->validateFormSubmission($params, $formFields);

        $params = $this->mergeUploadedFilesIntoValues($params, $uploadedFiles, $formFields);
        foreach (['sourceId', 'idempotencyKey', 'paymentMethodId', 'paymentIntentId', 'paypalOrderId'] as $key) {
            if (isset($params[$key])) {
                $params[$key] = sanitize_text_field((string) $params[$key]);
            }
        }
        $submissionData = Sanitizer::sanitizeFormSubmissionData($params, $formFields);

        do_action('ivyforms/form/validate_submission', $submissionData, $formFields);

        return [
            'formId' => $formId,
            'form' => $form,
            'formFields' => $formFields,
            'submissionData' => $submissionData,
            'params' => $params,
            'fieldLabels' => $fieldLabels,
        ];
    }

    /**
     * @param array{
     *     formId: int,
     *     form: Form,
     *     formFields: array<int, mixed>,
     *     submissionData: array<string, mixed>,
     *     params: array<string, mixed>,
     *     fieldLabels: array<string, string>
     * } $context
     *
     * @return array<string, mixed>|null
     */
    private function buildDuplicateResultIfNeeded(array $context): ?array
    {
        [$isDuplicate, $duplicateErrors] = $this->fieldDuplicateValidationService->checkDuplicateFieldValues(
            $context['formFields'],
            $context['submissionData'],
            $context['formId']
        );

        if (!$isDuplicate) {
            return null;
        }

        return [
            'success' => null,
            'entry' => ['stored' => false],
            'confirmation' => null,
            'is_duplicate' => true,
            'duplicate_errors' => $duplicateErrors,
        ];
    }

    /**
     * @param array<int, mixed> $formFields
     * @param array<string, mixed> $submissionData
     *
     * @return array{stored: bool, id?: int}
     */
    private function storeEntryIfEnabled(
        Form $form,
        int $formId,
        array $formFields,
        array $submissionData
    ): array {
        if (!$form->isStoreEntries()) {
            return ['stored' => false];
        }

        $entryData = EntryHelper::buildEntryData($formId);
        $entryObj = EntryFactory::create($entryData);
        $entryId = $this->entryService->getEntryManager()->createEntry($entryObj);
        $this->entryService->getEntryFieldManager()->addEntryFields($formFields, $entryId, $submissionData);

        return [
            'stored' => true,
            'id' => $entryId,
        ];
    }

    /**
     * @param array<int, mixed> $formFields
     * @param array<string, mixed> $submissionData
     *
     * @return array{fieldData: array<string, mixed>, generalData: array<string, mixed>}
     */
    private function buildPlaceholderData(array $formFields, array $submissionData, ?int $entryId): array
    {
        return [
            'fieldData' => PlaceholderService::buildFieldData($formFields, $submissionData),
            'generalData' => PlaceholderService::buildGeneralData($entryId ?? 0, $submissionData),
        ];
    }

    /**
     * @param array{
     *     formId: int,
     *     form: Form,
     *     formFields: array<int, mixed>,
     *     submissionData: array<string, mixed>,
     *     params: array<string, mixed>,
     *     fieldLabels: array<string, string>
     * } $context
     * @param array{fieldData: array<string, mixed>, generalData: array<string, mixed>} $placeholderData
     *
     * @return mixed
     */
    private function dispatchNotifications(array $context, array $placeholderData)
    {
        return $this->notificationService->processNotifications(
            $context['formId'],
            $context['submissionData'],
            $context['formFields'],
            $placeholderData['fieldData'],
            $placeholderData['generalData'],
            $this->mailerService,
            $context['fieldLabels']
        );
    }

    /**
     * @param array{stored: bool, id?: int} $entry
     * @param mixed $notificationsResult
     * @param array{message: string, result: array<string, mixed>|null} $confirmationPayload
     *
     * @return array<string, mixed>
     */
    private function buildSuccessResult(
        array $entry,
        $notificationsResult,
        array $confirmationPayload
    ): array {
        return [
            'success' => $notificationsResult,
            'entry' => $entry,
            'confirmation' => $confirmationPayload['message'],
            'confirmationResult' => $confirmationPayload['result'],
        ];
    }

    /**
     * @param array<string, mixed> $paymentOutcome
     * @return array<string, mixed>|null
     */
    private function buildEarlyPaymentResult(array $paymentOutcome): ?array
    {
        $errorResponse = $paymentOutcome['errorResponse'] ?? null;
        if ($errorResponse instanceof WP_REST_Response) {
            return $this->normalizePaymentErrorResult($errorResponse);
        }

        if (empty($paymentOutcome['requiresAction'])) {
            return null;
        }

        return [
            'requires_action' => true,
            'client_secret' => $paymentOutcome['clientSecret'] ?? null,
            'payment_intent_id' => $paymentOutcome['paymentIntentId'] ?? null,
            'payment_method' => $paymentOutcome['paymentMethod'] ?? 'stripe',
            'success' => false,
        ];
    }

    /**
     * @param array<int, mixed> $formFields
     * @param array<string, mixed> $submissionData
     * @param array<string, mixed> $paymentOutcome
     * @return array<string, mixed>
     */
    private function persistEntryWithOrphanProtection(
        Form $form,
        array $formFields,
        array $submissionData,
        int $formId,
        array $paymentOutcome
    ): array {
        $transactionId = (string) ($paymentOutcome['paymentResult']['transactionId'] ?? '');
        if (
            $transactionId !== ''
            && $this->formSubmissionPaymentService->paymentAlreadyLinkedToEntry($transactionId)
        ) {
            return $this->normalizePaymentErrorResult(
                $this->formSubmissionPaymentService->duplicatePaymentErrorResponse()
            );
        }

        try {
            $entry = $this->storeEntryIfEnabled($form, $formId, $formFields, $submissionData);
        } catch (\Throwable $exception) {
            $this->logPersistFailure('entry', $exception);
            $this->recordOrphanedPaymentSafely($formId, $paymentOutcome);

            return $this->orphanedPaymentResult();
        }

        if (!empty($entry['stored']) && empty($entry['id']) && $paymentOutcome['paymentResult'] !== null) {
            $this->recordOrphanedPaymentSafely($formId, $paymentOutcome);

            return $this->orphanedPaymentResult();
        }

        return $entry;
    }

    /**
     * @param array<string, mixed> $entry
     * @param array<string, mixed> $paymentOutcome
     * @return array<string, mixed>|null
     */
    private function persistPaymentWithOrphanProtection(
        array $entry,
        int $formId,
        array $paymentOutcome
    ): ?array {
        try {
            $recorded = $this->formSubmissionPaymentService->recordSubmissionPayment(
                (int) ($entry['id'] ?? 0),
                $formId,
                $paymentOutcome
            );
        } catch (\Throwable $exception) {
            $this->logPersistFailure('payment', $exception);
            $this->rollbackEntryIfNeeded($entry);
            $this->recordOrphanedPaymentSafely($formId, $paymentOutcome);

            return $this->orphanedPaymentResult();
        }

        if ($recorded) {
            return null;
        }

        $this->rollbackEntryIfNeeded($entry);

        return $this->normalizePaymentErrorResult(
            $this->formSubmissionPaymentService->duplicatePaymentErrorResponse()
        );
    }

    /**
     * @param array<string, mixed> $paymentOutcome
     */
    private function recordOrphanedPaymentSafely(int $formId, array $paymentOutcome): void
    {
        try {
            $this->formSubmissionPaymentService->recordOrphanedPayment($formId, $paymentOutcome);
        } catch (\Throwable $exception) {
            $this->logPersistFailure('orphaned payment', $exception);
        }
    }

    private function logPersistFailure(string $stage, \Throwable $exception): void
    {
        error_log(sprintf(
            '[IvyForms] Failed to persist %s after payment: %s in %s:%d',
            $stage,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function orphanedPaymentResult(): array
    {
        return $this->normalizePaymentErrorResult(new WP_REST_Response([
            'message' => BackendStrings::getPaymentStrings()['payment_failed'],
            'data' => [
                'success' => false,
                'payment_error' => true,
            ],
        ], 200));
    }

    /**
     * @param array{stored: bool, id?: int} $entry
     */
    private function rollbackEntryIfNeeded(array $entry): void
    {
        if (empty($entry['stored']) || empty($entry['id'])) {
            return;
        }

        $entryId = (int) $entry['id'];
        $this->entryService->getDeletionManager()->deleteEntryFieldsByEntryIds([$entryId]);
        $this->entryService->getDeletionManager()->deleteEntry($entryId);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizePaymentErrorResult(WP_REST_Response $errorResponse): array
    {
        $payload = $errorResponse->get_data();
        $payload = is_array($payload) ? $payload : [];

        return [
            'payment_error' => true,
            'message' => (string) (
                $payload['message'] ?? BackendStrings::getPaymentStrings()['payment_failed']
            ),
            'success' => false,
            'entry' => ['stored' => false],
            'confirmation' => null,
        ];
    }
}
