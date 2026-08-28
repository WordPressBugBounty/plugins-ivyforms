<?php

namespace IvyForms\Services\Confirmation;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Sanitizer\HtmlSanitizer;
use IvyForms\Entity\Confirmation\Confirmation;
use IvyForms\Factory\Confirmation\ConfirmationFactory;
use IvyForms\Repository\Confirmation\ConfirmationRepositoryInterface;
use IvyForms\Services\Media\ImageService;
use IvyForms\Services\Placeholder\PlaceholderService;
use IvyForms\Services\Translations\BackendStrings;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ConfirmationService
{
    private ConfirmationRepositoryInterface $confirmationRepository;
    private ImageService $imageService;
    private ConfirmationAccessPolicy $accessPolicy;
    private ConfirmationResolver $confirmationResolver;
    private ConfirmationOrderService $confirmationOrderService;

    public function __construct(
        ConfirmationRepositoryInterface $confirmationRepository,
        ImageService $imageService,
        ConfirmationAccessPolicy $accessPolicy,
        ConfirmationResolver $confirmationResolver,
        ConfirmationOrderService $confirmationOrderService
    ) {
        $this->confirmationRepository = $confirmationRepository;
        $this->imageService = $imageService;
        $this->accessPolicy = $accessPolicy;
        $this->confirmationResolver = $confirmationResolver;
        $this->confirmationOrderService = $confirmationOrderService;
    }

    /**
     * Create a new confirmation.
     *
     * @param Confirmation $confirmationData
     *
     * @return int Confirmation ID
     */
    public function createConfirmation(Confirmation $confirmationData): int
    {
        $formId = $confirmationData->getFormId();
        $this->accessPolicy->assertCanAdd($formId);

        if ($confirmationData->getPosition() === 0 && $this->confirmationRepository->countByFormId($formId) === 0) {
            $confirmationData->setIsDefault(true);
        } elseif ($confirmationData->getPosition() === 0) {
            $confirmationData->setPosition($this->confirmationRepository->getNextPosition($formId));
        }

        $this->processMessageImages($confirmationData);

        return $this->confirmationRepository->add($confirmationData);
    }

    /**
     * Update an existing confirmation.
     *
     * @param int $confirmationId
     * @param Confirmation $confirmationData
     *
     * @return bool
     */
    public function updateConfirmation(int $confirmationId, Confirmation $confirmationData): bool
    {
        $existing = $this->getConfirmationById($confirmationId);
        // Always authorize and persist against the stored owner — never trust client formId.
        $formId = $existing->getFormId();
        $confirmationData->setFormId($formId);

        $this->accessPolicy->assertCanManage($confirmationId, $formId, 'edit');

        $confirmationData->setPosition($existing->getPosition());
        $confirmationData->setIsDefault($existing->isDefault());

        $this->processMessageImages($confirmationData);

        return $this->confirmationRepository->update($confirmationId, $confirmationData);
    }

    /**
     * Get confirmations by form ID.
     *
     * @param int $id
     *
     * @return array<Confirmation>
     *
     * @throws NotFoundException If no confirmations are found
     */
    public function getConfirmationsById(int $id): array
    {
        $this->ensureDefaultConfirmationExists($id);

        $confirmation = $this->confirmationRepository->getAllById($id);

        if (empty($confirmation)) {
            throw new NotFoundException(
                BackendStrings::getExceptionStrings()['confirmation_not_found']
            );
        }

        return $confirmation;
    }

    /**
     * Ensure a form has at least one default confirmation.
     *
     * @param int $formId
     * @return int Default confirmation ID
     */
    public function ensureDefaultConfirmationExists(int $formId): int
    {
        return $this->accessPolicy->ensureDefaultExists(
            $formId,
            fn (int $id): int => $this->createDefaultConfirmationForForm($id)
        );
    }

    /**
     * Get a specific confirmation by its ID.
     *
     * @param int $id
     *
     * @return object ConfirmationEntity
     *
     * @throws NotFoundException If the confirmation is not found
     */
    public function getConfirmationById(int $id): object
    {
        $confirmation = $this->confirmationRepository->getById($id);

        if (!$confirmation) {
            throw new NotFoundException(
                BackendStrings::getExceptionStrings()['confirmation_not_found']
            );
        }

        return $confirmation;
    }

    /**
     * Delete a single confirmation.
     *
     * @param int $confirmationId
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws NotFoundException
     */
    public function deleteConfirmation(int $confirmationId): void
    {
        $confirmation = $this->getConfirmationById($confirmationId);
        $formId = $confirmation->getFormId();

        $this->accessPolicy->assertCanManage($confirmationId, $formId, 'delete');

        $count = $this->confirmationRepository->countByFormId($formId);

        if ($count <= 1) {
            throw new InvalidArgumentException(
                BackendStrings::getSettingsFormBuilderStrings()['cannot_delete_last_confirmation']
            );
        }

        $this->confirmationRepository->delete($confirmationId);

        $remaining = $this->confirmationRepository->getAllById($formId);
        if (!empty($remaining)) {
            $orderedIds = array_map(static fn ($item) => $item->getId(), $remaining);
            $this->confirmationOrderService->applyOrder($formId, $orderedIds);
        }
    }

    /**
     * Delete confirmations by multiple form IDs.
     *
     * @param array<int> $formIds
     * @return int Number of deleted confirmations
     * @throws InvalidArgumentException If no form IDs are provided
     */
    public function deleteConfirmationsByFormIds(array $formIds): int
    {
        if (empty($formIds)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['no_form_ids_provided']
            );
        }

        return $this->confirmationRepository->deleteManyByForeignKeyValues($formIds);
    }

    /**
     * Duplicate a single confirmation.
     *
     * @param int $confirmationId
     *
     * @return int New confirmation ID
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public function duplicateConfirmation(int $confirmationId): int
    {
        $original = $this->getConfirmationById($confirmationId);
        $formId = $original->getFormId();

        $this->accessPolicy->assertCanManage($confirmationId, $formId, 'duplicate');
        $this->accessPolicy->assertCanAdd($formId);

        $newData = $original->toArray();
        unset($newData['id'], $newData['pageUrl']);
        $copyLabel = BackendStrings::getNewFormStrings()['copy_label'];
        $newData['name'] = $newData['name'] . $copyLabel;
        $newData['position'] = $this->confirmationRepository->getNextPosition($formId);
        $newData['isDefault'] = false;
        $newData['enabled'] = false;

        $newConfirmation = ConfirmationFactory::create($newData);
        $newId = $this->createConfirmation($newConfirmation);

        if (!$newId) {
            throw new QueryExecutionException(
                BackendStrings::getSettingsFormBuilderStrings()['failed_to_duplicate_confirmation']
            );
        }

        $newConfirmation->setId($newId);
        return $newId;
    }

    /**
     * Search confirmations with pagination and sorting.
     *
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function searchConfirmations(array $params): array
    {
        $result = $this->confirmationRepository->search($params);

        $formId = isset($params['filters']['formId']) ? (int) $params['filters']['formId'] : 0;
        if ($formId > 0 && ($result['meta']['total'] ?? 0) === 0) {
            $this->ensureDefaultConfirmationExists($formId);

            return $this->confirmationRepository->search($params);
        }

        return $result;
    }

    /**
     * Reorder confirmations for a form.
     *
     * @param int $formId
     * @param array<int, int> $orderedIds
     *
     * @return void
     * @throws ForbiddenException
     */
    public function reorderConfirmations(int $formId, array $orderedIds): void
    {
        if (!apply_filters('ivyforms/can_create_multiple_confirmations', false, $formId)) {
            throw new ForbiddenException(
                BackendStrings::getSettingsFormBuilderStrings()['multiple_confirmations_pro_required']
            );
        }

        $this->confirmationOrderService->validateReorderIds($formId, $orderedIds);
        $this->confirmationOrderService->applyOrder($formId, $orderedIds);
    }

    /**
     * Duplicate confirmations from one form to another.
     *
     * @param int $originalFormId
     * @param int $newFormId
     *
     * @return void
     * @throws NotFoundException
     */
    public function duplicateConfirmations(int $originalFormId, int $newFormId): void
    {
        $confirmations = $this->getConfirmationsById($originalFormId);
        foreach ($confirmations as $index => $confirmation) {
            $confirmationData = $confirmation->toArray();
            unset($confirmationData['id'], $confirmationData['pageUrl']);
            $confirmationData['formId'] = $newFormId;
            $confirmationData['position'] = $index;
            $confirmationData['isDefault'] = $index === 0;

            $entity = ConfirmationFactory::create($confirmationData);
            $this->processMessageImages($entity);
            $this->confirmationRepository->add($entity);
        }
    }

    /**
     * Create a default confirmation for a form.
     *
     * @param int $formId
     * @return int Confirmation ID
     */
    public function createDefaultConfirmationForForm(int $formId): int
    {
        $strings = BackendStrings::getSettingsFormBuilderStrings();
        $defaultConfirmation = [
            'formId'    => $formId,
            'name'      => $strings['default_confirmation_name'],
            'type'      => 'successMessage',
            'enabled'   => 1,
            'showForm'  => 0,
            'message'   => BackendStrings::getAllFormsStrings()['thanks_reaching'],
            'url'       => '',
            'page'      => '',
            'position'  => 0,
            'isDefault' => 1,
            'smartLogic' => [
                'enabled' => false,
                'match' => 'any',
                'rules' => [],
            ],
        ];
        $confirmation = ConfirmationFactory::create($defaultConfirmation);
        $confirmationId = $this->confirmationRepository->add($confirmation);
        if (!$confirmationId) {
            throw new QueryExecutionException(
                BackendStrings::getSettingsFormBuilderStrings()['failed_to_create_confirmation']
            );
        }
        $confirmation->setId($confirmationId);
        return $confirmationId;
    }

    /**
     * Resolve the first active confirmation for a form (by list order).
     * Used when submission data is unavailable (e.g. builder preview).
     *
     * @param int $formId
     * @return Confirmation|null
     * @throws NotFoundException
     */
    public function getActiveConfirmation(int $formId): ?Confirmation
    {
        $confirmations = $this->getConfirmationsById($formId);
        foreach ($confirmations as $confirmation) {
            if ($confirmation->isEnabled()) {
                return $confirmation;
            }
        }

        return null;
    }

    /**
     * Resolve which confirmation applies after submission (conditional logic + default fallback).
     *
     * @param int $formId
     * @param array<string, mixed> $submissionData
     * @param array<int, mixed> $formFields
     * @return Confirmation|null
     * @throws NotFoundException
     */
    public function resolveActiveConfirmation(
        int $formId,
        array $submissionData,
        array $formFields
    ): ?Confirmation {
        return $this->confirmationResolver->resolve(
            $this->getConfirmationsById($formId),
            $submissionData,
            $formFields,
            $formId
        );
    }

    /**
     * Build the public confirmation payload for a resolved confirmation.
     *
     * @param Confirmation $confirmation
     * @param array<string, mixed> $fieldData
     * @param array<string, mixed> $generalData
     * @param array<string, string> $fieldLabels
     * @return array<string, mixed>
     */
    public function buildConfirmationResult(
        Confirmation $confirmation,
        array $fieldData,
        array $generalData,
        array $fieldLabels = []
    ): array {
        $type = $confirmation->getType();
        $message = '';

        if ($type === 'successMessage') {
            $message = PlaceholderService::replacePlaceholders(
                $confirmation->getMessage(),
                $fieldData,
                $generalData,
                $fieldLabels
            );
        }

        return [
            'id' => $confirmation->getId(),
            'type' => $type,
            'message' => $message,
            'showForm' => $confirmation->isShowForm(),
            'url' => $confirmation->getUrl(),
            'pageUrl' => $confirmation->getPageUrl(),
            'isDefault' => $confirmation->isDefault(),
        ];
    }

    /**
     * Process confirmations for the form.
     *
     * @param int $formId
     * @param array<string, mixed> $submissionData
     * @param array<int, mixed> $formFields
     * @param array<string, mixed> $fieldData
     * @param array<string, mixed> $generalData
     * @param array<string, string> $fieldLabels
     * @return array<string, mixed>
     * @throws NotFoundException
     */
    public function processConfirmations(
        int $formId,
        array $submissionData,
        array $formFields,
        array $fieldData,
        array $generalData,
        array $fieldLabels = []
    ): array {
        $confirmation = $this->resolveActiveConfirmation($formId, $submissionData, $formFields);
        if ($confirmation === null) {
            $confirmation = $this->getActiveConfirmation($formId);
        }
        if ($confirmation === null) {
            return [
                'message' => '',
                'result' => null,
            ];
        }

        $result = $this->buildConfirmationResult($confirmation, $fieldData, $generalData, $fieldLabels);

        return [
            'message' => $result['message'] ?? '',
            'result' => $result,
        ];
    }

    /**
     * Sanitize confirmation message and redirect URLs for the submission API response.
     *
     * @param array<string, mixed> $confirmationPayload
     * @return array{message: string, result: array<string, mixed>|null}
     */
    public function sanitizeProcessedPayload(array $confirmationPayload): array
    {
        $safeConfirmationMessage = HtmlSanitizer::sanitizeEditorContent(
            $confirmationPayload['message'] ?? ''
        );
        $confirmationResult = $confirmationPayload['result'] ?? null;

        if (!is_array($confirmationResult) || !isset($confirmationResult['message'])) {
            return [
                'message' => $safeConfirmationMessage,
                'result' => is_array($confirmationResult) ? $confirmationResult : null,
            ];
        }

        $confirmationResult['message'] = $safeConfirmationMessage;
        if (!empty($confirmationResult['url'])) {
            $confirmationResult['url'] = esc_url_raw((string) $confirmationResult['url']);
        }
        if (!empty($confirmationResult['pageUrl'])) {
            $confirmationResult['pageUrl'] = esc_url_raw((string) $confirmationResult['pageUrl']);
        }

        return [
            'message' => $safeConfirmationMessage,
            'result' => $confirmationResult,
        ];
    }

    private function processMessageImages(Confirmation $confirmationData): void
    {
        $message = $confirmationData->getMessage();
        if ($message === '') {
            return;
        }

        $confirmationData->setMessage(
            $this->imageService->processImagesInContent($message, 'confirmation')
        );
    }
}
