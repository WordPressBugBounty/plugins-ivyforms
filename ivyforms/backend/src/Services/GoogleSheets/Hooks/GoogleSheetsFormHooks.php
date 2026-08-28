<?php

namespace IvyForms\Services\GoogleSheets\Hooks;

use IvyForms\Repository\GoogleSheets\GoogleSheetsRepositoryInterface;

/**
 * Cleans up Google Sheets integrations when a form is deleted.
 */
class GoogleSheetsFormHooks
{
    private GoogleSheetsRepositoryInterface $repository;

    public function __construct(GoogleSheetsRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function register(): void
    {
        add_action('ivyforms/form/before_delete', [$this, 'onFormDelete']);
    }

    public function onFormDelete(int $formId): void
    {
        if ($formId <= 0) {
            return;
        }

        $this->repository->deleteByFormId($formId);
    }
}
