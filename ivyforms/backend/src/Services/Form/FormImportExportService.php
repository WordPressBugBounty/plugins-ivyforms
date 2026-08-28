<?php

namespace IvyForms\Services\Form;

use IvyForms\Common\Helpers\FormImportValidator;
use IvyForms\Common\Helpers\Import\FormImportNormalizer;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\ValueObjects\Form\FormExportOptions;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Permissions\AccessRulesFormScopeExtender;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\Factory\Form\FormFactory;
use IvyForms\Factory\Notification\NotificationFactory;
use IvyForms\Factory\Confirmation\ConfirmationFactory;

class FormImportExportService
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
        $this->formService = $formService;
        $this->fieldService = $fieldService;
        $this->notificationService = $notificationService;
        $this->confirmationService = $confirmationService;
        $this->accessRulesFormScopeExtender = $accessRulesFormScopeExtender;
    }

    /**
     * Export multiple forms.
     *
     * @param array<int> $formIds
     * @param FormExportOptions|null $exportOptions Export section options
     * @return array<string, mixed>
     */
    public function exportForms(array $formIds, ?FormExportOptions $exportOptions = null): array
    {
        $exportOptions = $exportOptions ?? FormExportOptions::withStyles();

        /**
         * Allows extensions to adjust export options (e.g. include entries).
         *
         * @since 0.1.0
         *
         * @param FormExportOptions $exportOptions Export options from the request.
         * @param array<int> $formIds Form IDs being exported.
         * @return FormExportOptions Modified export options.
         */
        $exportOptions = apply_filters('ivyforms/form/import_export/export_options', $exportOptions, $formIds);
        $exportedForms = [];

        foreach ($formIds as $formId) {
            $formData = $this->exportSingleForm($formId, $exportOptions);
            if ($formData) {
                $exportedForms[] = $formData;
            }
        }

        $exportPayload = [
            'version'      => IVYFORMS_VERSION,
            'export_date'  => current_time('mysql'),
            'forms'        => $exportedForms,
            'forms_count'  => count($exportedForms)
        ];

        /**
         * Allows extending the final form export payload.
         * Useful for adding top-level metadata or future sections related to export/import.
         *
         * @since 0.1.0
         *
         * @param array<string, mixed> $exportPayload The complete export payload.
         * @param array<int> $formIds The requested form IDs.
         * @param array<int, array<string, mixed>> $exportedForms The exported forms payload.
         * @return array<string, mixed> Modified export payload.
         */
        return apply_filters('ivyforms/form/import_export/export_payload', $exportPayload, $formIds, $exportedForms);
    }

    /**
     * Export a single form with all its data.
     *
     * @param int $formId
     * @param FormExportOptions $exportOptions Export section options
     * @return array<string, mixed>
     * @throws NotFoundException
     * @throws ValidationException|QueryExecutionException
     */
    private function exportSingleForm(int $formId, FormExportOptions $exportOptions): array
    {
        $form = $this->formService->getFormById($formId);
        $fields = $this->fieldService->getAllFieldsWithOptions($formId);
        $idToIndexMap = FormImportValidator::buildFieldIdToIndexMap($fields);
        $notifications = $this->notificationService->getNotificationsByFormId($formId);
        $confirmations = $this->confirmationService->getConfirmationsById($formId);
        $confirmation = !empty($confirmations) ? $confirmations[0] : null;

        $formExportData = FormImportValidator::prepareFormDataForExport($form);
        if (!$exportOptions->includesStyles()) {
            $formExportData = FormImportValidator::stripStyleSettingsFromFormExport($formExportData);
        }

        $exportSections = [
            'form'          => $formExportData,
            'fields'        => FormImportValidator::prepareFieldsDataForExport($fields, $idToIndexMap),
            'notifications' => FormImportValidator::prepareNotificationsDataForExport($notifications),
            'confirmation'  => FormImportValidator::prepareConfirmationDataForExport($confirmation),
        ];

        /**
         * Allows extending exported sections for a single form.
         * Useful for adding future sections like entries, styles, or other custom form data.
         *
         * @since 0.1.0
         *
         * @param array<string, mixed> $exportSections The current export sections for a single form.
         * @param int $formId The source form ID.
         * @param mixed $form The source form entity.
         * @param array<int, array<string, mixed>> $fields The source form fields.
         * @param array<int, mixed> $notifications The source notifications.
         * @param mixed $confirmation The source confirmation entity.
         * @param FormExportOptions $exportOptions Export options for this request.
         * @return array<string, mixed> Modified export sections.
         */
        return apply_filters(
            'ivyforms/form/import_export/export_sections',
            $exportSections,
            $formId,
            $form,
            $fields,
            $notifications,
            $confirmation,
            $exportOptions
        );
    }

    /**
     * Normalize a raw import payload (IvyForms or third-party), validate it,
     * and import the resulting forms.
     *
     * Shared entry point for REST, MCP, and any future API callers.
     *
     * @param mixed $importData
     * @return array<int, array<string, mixed>>
     * @throws InvalidArgumentException
     */
    public function importFromRawPayload($importData): array
    {
        $normalizedData = FormImportNormalizer::normalize($importData);
        $formsData = FormImportValidator::validateImportPayload($normalizedData);
        $this->assertHasMappedFields($formsData);

        return $this->importForms($formsData);
    }

    /**
     * Reject imports where every form has zero mapped fields.
     *
     * @param array<int, array<string, mixed>> $formsData
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertHasMappedFields(array $formsData): void
    {
        foreach ($formsData as $formData) {
            if (!empty($formData['fields']) && is_array($formData['fields'])) {
                return;
            }
        }

        throw new InvalidArgumentException(
            BackendStrings::getExceptionStrings()['import_no_supported_fields']
        );
    }

    /**
     * Import multiple forms
     * @param array<int, array<string, mixed>> $formsData
     * @return array<int, array<string, mixed>>
     */
    public function importForms(array $formsData): array
    {
        /**
         * Allows preprocessing imported forms data before per-form import starts.
         * Useful for normalizing or enriching custom import sections.
         *
         * @since 0.1.0
         *
         * @param array<int, array<string, mixed>> $formsData The validated forms data to import.
         * @return array<int, array<string, mixed>> Modified forms data.
         */
        $formsData = apply_filters('ivyforms/form/import_export/import_forms_data', $formsData);

        $importedForms = [];
        foreach ($formsData as $formData) {
            $importedForm = $this->importSingleForm($formData);
            if ($importedForm) {
                $importedForms[] = $importedForm;
            }
        }
        return $importedForms;
    }

    /**
     * Import a single form with all its data
     * @param array<string, mixed> $formData
     * @return array<string, mixed>|null
     */
    private function importSingleForm(array $formData): ?array
    {
        /**
         * Allows preprocessing a single imported form payload before core import runs.
         * Useful for future custom sections such as entries or styles.
         *
         * @since 0.1.0
         *
         * @param array<string, mixed> $formData The single form payload being imported.
         * @return array<string, mixed> Modified single form payload.
         */
        $formData = apply_filters('ivyforms/form/import_export/import_form_data', $formData);

        $currentUser = wp_get_current_user();
        $author = $currentUser->user_login ?? 'admin';
        $formArray = FormImportValidator::prepareFormArrayForImport(
            $formData,
            $author,
            current_time('mysql'),
            BackendStrings::getCommonStrings()['submit']
        );
        $formId = $this->createFormForImport($formArray);
        if (!$formId) {
            return null;
        }

        $this->accessRulesFormScopeExtender->appendCreatedFormToCurrentUserScope($formId);

        /**
         * Fires after the core form record is created during import.
         * Useful for importing form-related metadata that only needs the new form ID.
         *
         * @since 0.1.0
         *
         * @param int $formId The newly created form ID.
         * @param array<string, mixed> $formData The imported single form payload.
         * @param array<string, mixed> $formArray The prepared form array used to create the form.
         */
        do_action('ivyforms/form/import_export/after_form_created', $formId, $formData, $formArray);

        $formName = $this->resolveImportedFormName($formArray);
        $this->importFieldsForForm($formData, $formId);
        $this->importNotificationsForForm($formData, $formId, $formName);
        $this->importConfirmationForForm($formData, $formId);

        /**
         * Fires after the core import process finishes for a single form.
         * Useful for importing dependent sections such as entries or styles.
         *
         * @since 0.1.0
         *
         * @param int $formId The newly created form ID.
         * @param array<string, mixed> $formData The imported single form payload.
         * @param array<string, mixed> $formArray The prepared form array used to create the form.
         * @param string $formName The resolved imported form name.
         */
        do_action('ivyforms/form/import_export/after_import', $formId, $formData, $formArray, $formName);

        return [

            'id'   => $formId,
            'name' => $formName
        ];
    }

    /**
     * Create a form record for import.
     * @param array<string, mixed> $formArray
     * @return int|null
     */
    private function createFormForImport(array $formArray): ?int
    {
        $form = FormFactory::create($formArray);
        $formId = $this->formService->createForm($form);
        if (!$formId) {
            return null;
        }

        return $formId;
    }

    /**
     * Resolve final form name for import response and defaults.
     * @param array<string, mixed> $formArray
     * @return string
     */
    private function resolveImportedFormName(array $formArray): string
    {
        if (!empty($formArray['name'])) {
            return (string)$formArray['name'];
        }

        return BackendStrings::getAllFormsStrings()['imported_form'];
    }

    /**
     * Import form fields if present.
     * @param array<string, mixed> $formData
     * @param int $formId
     * @return void
     */
    private function importFieldsForForm(array $formData, int $formId): void
    {
        $fieldsData = $formData['fields'] ?? [];
        if (empty($fieldsData)) {
            return;
        }

        $fieldsData = FormImportValidator::normalizeFieldsForImport($fieldsData);

        $this->fieldService->saveFieldsWithOptions($fieldsData, $formId);
    }

    /**
     * Import notifications or create defaults when missing.
     * @param array<string, mixed> $formData
     * @param int $formId
     * @param string $formName
     * @return void
     */
    private function importNotificationsForForm(array $formData, int $formId, string $formName): void
    {
        $notificationsData = $formData['notifications'] ?? [];
        if (empty($notificationsData)) {
            $adminEmail = get_option('admin_email');
            $this->notificationService->createDefaultNotificationForForm(
                $formId,
                $formName,
                is_string($adminEmail) ? $adminEmail : ''
            );
            return;
        }

        foreach ($notificationsData as $notificationData) {
            $notificationData['id'] = 0;
            $notificationData['formId'] = $formId;
            $notification = NotificationFactory::create($notificationData);
            $this->notificationService->createNotification($notification);
        }
    }

    /**
     * Import confirmation or create default one when missing.
     * @param array<string, mixed> $formData
     * @param int $formId
     * @return void
     */
    private function importConfirmationForForm(array $formData, int $formId): void
    {
        $confirmationData = $formData['confirmation'] ?? null;
        if (is_object($confirmationData)) {
            $confirmationData = (array)$confirmationData;
        }

        if (!is_array($confirmationData) || empty($confirmationData)) {
            $this->confirmationService->createDefaultConfirmationForForm($formId);
            return;
        }

        $confirmationData['id'] = 0;
        $confirmationData['formId'] = $formId;
        $confirmation = ConfirmationFactory::create($confirmationData);
        $this->confirmationService->createConfirmation($confirmation);
    }
}
