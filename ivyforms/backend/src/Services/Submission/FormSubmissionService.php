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
use IvyForms\Services\Placeholder\PlaceholderService;
use IvyForms\Services\Security\SecurityService;
use IvyForms\Services\Translations\BackendStrings;

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

    public function __construct(
        SubmissionUploadService $submissionUploadService,
        FormService $formService,
        FieldService $fieldService,
        FieldDuplicateValidationService $fieldDuplicateValidationService,
        SecurityService $securityService,
        EntryService $entryService,
        NotificationService $notificationService,
        MailerService $mailerService,
        ConfirmationService $confirmationService
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

        $entry = $this->storeEntryIfEnabled(
            $context['form'],
            $context['formId'],
            $context['formFields'],
            $context['submissionData']
        );
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
        $submissionData = Sanitizer::sanitizeFormSubmissionData($params, $formFields);

        do_action('ivyforms/form/validate_submission', $submissionData, $formFields);

        return [
            'formId' => $formId,
            'form' => $form,
            'formFields' => $formFields,
            'submissionData' => $submissionData,
            'fieldLabels' => $fieldLabels,
        ];
    }

    /**
     * @param array{
     *     formId: int,
     *     form: Form,
     *     formFields: array<int, mixed>,
     *     submissionData: array<string, mixed>,
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
}
