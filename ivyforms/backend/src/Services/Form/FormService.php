<?php

namespace IvyForms\Services\Form;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\Form\Form;
use IvyForms\Factory\Form\FormFactory;
use IvyForms\Repository\Form\FormRepositoryInterface;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class FormService
 *
 * @package IvyForms\Services\Form
 *
 * @SuppressWarnings(PHPMD)
 */
class FormService
{
    private FormRepositoryInterface $formRepository;

    // Constructor injection for the FormRepository via PHP-DI
    public function __construct(FormRepositoryInterface $formRepository)
    {
        $this->formRepository = $formRepository;
    }

    /**
     * Get all forms from the repository.
     *
     * @return mixed[]|null
     */
    public function getAllForms(): ?array
    {
        return $this->formRepository->getAll();
    }

    /**
     * @return list<int>
     */
    public function getAllFormIds(): array
    {
        return $this->formRepository->getAllIds();
    }

    /**
     * Create a new form.
     *
     * @param Form $formData
     *
     * @return int Form ID
     */
    public function createForm(Form $formData): int
    {
        // Create form via repository and return new form ID
        return $this->formRepository->add($formData);
    }

    /**
     * Get a specific form by its ID.
     *
     * @param int $formId
     *
     * @return object FormEntity
     *
     * @throws NotFoundException If the form is not found
     */
    public function getFormById(int $formId): object
    {
        $form = $this->formRepository->getById($formId);

        if (!$form) {
            throw new NotFoundException(
                BackendStrings::getExceptionStrings()['form_not_found']
            );
        }

        return $form;
    }

    /**
     * Update an existing form.
     *
     * @param int $formId
     * @param Form $formData
     *
     * @return bool
     *
     */
    public function updateForm(int $formId, Form $formData): bool
    {
        // Validate form data before updating

        return $this->formRepository->update($formId, $formData);
    }

    /**
     * Update form from params and return tuple [formId, form]
     *
     * @param array<string,mixed> $params
     * @return array{0:int,1:Form}
     * @throws QueryExecutionException|ValidationException
     */
    public function updateFormOrFail(array $params): array
    {
        $form = FormFactory::create($params);
        $formId = $form->getId();
        $this->updateForm($formId, $form);
        if (!$form->getId()) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['failed_to_update_form'] . '.');
        }
        return [$formId, $form];
    }

    /**
     * Preserve the persisted formType when the caller omits it, so a partial update
     * (e.g. settings/name change) cannot silently demote the form's type.
     *
     * @param array<string,mixed> $rawParams Request payload to be sanitized.
     * @param int $formId Form id resolved from the route (URL placeholder).
     * @return array<string,mixed>
     * @throws NotFoundException
     */
    public function withPreservedFormType(array $rawParams, int $formId): array
    {
        if (array_key_exists('formType', $rawParams)) {
            return $rawParams;
        }

        if ($formId <= 0) {
            return $rawParams;
        }

        $existing = $this->getFormById($formId);
        if (method_exists($existing, 'getFormType')) {
            $rawParams['formType'] = $existing->getFormType();
        }

        return $rawParams;
    }

    /**
     * Update the starred field of a form.
     *
     * @param int $formId
     * @param mixed $value
     *
     * @return bool
     */
    public function updateFormStarred(int $formId, $value): bool
    {
        return $this->formRepository->updateFormStarred($formId, $value);
    }

    /**
     * Update the published status of a form.
     *
     * @param int $formId
     * @param mixed $value
     *
     * @return bool
     */
    public function updateFormStatus(int $formId, $value): bool
    {
        return $this->formRepository->updateFormStatus($formId, $value);
    }

    /**
     * Delete a form by its ID.
     *
     * @param int $formId
     *
     * @return int
     */
    public function deleteForm(int $formId): int
    {
        /**
         * Fires before a form is deleted.
         *
         * @param int $formId The ID of the form being deleted.
         */
        do_action('ivyforms/form/before_delete', $formId);

        return $this->formRepository->delete($formId);
    }

    /**
     * Delete multiple forms by their IDs.
     *
     * @param array<int> $formIds
     *
     * @return int Number of deleted forms
     */
    public function deleteForms(array $formIds): int
    {
        foreach ($formIds as $formId) {
            /**
             * Fires before a form is deleted.
             *
             * @param int $formId The ID of the form being deleted.
             */
            do_action('ivyforms/form/before_delete', (int) $formId);
        }

        return $this->formRepository->deleteMany($formIds);
    }

    /**
     * Duplicate a form by its ID.
     *
     * @param int $formId
     *
     * @return int New form ID
     *
     * @throws NotFoundException|ValidationException
     * @throws QueryExecutionException
     */
    public function duplicateForm(int $formId): int
    {
        // Retrieve the original form
        $originalForm = $this->getFormById($formId);

        // Duplicate the form's parameters, excluding the ID
        $newFormData = $originalForm->toArray();
        unset($newFormData['id']);
        $newFormData['name'] .= ' (Copy)';
        $newFormId = $this->createForm(FormFactory::create($newFormData));

        if (!$newFormId) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['failed_to_duplicate_form']);
        }

        return $newFormId;
    }

    /**
     * Check if a form exists by its ID.
     *
     * @param int $formId
     *
     * @return bool
     */
    public function formExists(int $formId): bool
    {
        return $this->formRepository->exists($formId);
    }

    /**
     * Search for forms based on parameters.
     *
     * @param array<string, mixed> $params
     *
     * @return array<mixed>
     */
    public function searchForms(array $params): array
    {
        return $this->formRepository->search($params);
    }

    /**
     * Get the count of form filters.
     *
     * @param bool $shouldGetCount
     *
     * @return array<mixed>
     */
    public function getFilterCount(bool $shouldGetCount): array
    {
        if ($shouldGetCount) {
            return $this->formRepository->getFilterCount();
        }
        return [];
    }

    /**
     * Bulk update a column for multiple forms.
     *
     * @param string $column Column name to update
     * @param mixed $value Value to set
     * @param array<int> $ids Array of form IDs
     * @return int Number of updated forms
     */
    public function bulkUpdateColumn(string $column, $value, array $ids): int
    {
        return $this->formRepository->bulkUpdateColumn($column, $value, $ids);
    }
}
