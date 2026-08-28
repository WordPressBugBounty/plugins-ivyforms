<?php

namespace IvyForms\Services\GoogleSheets\Hooks;

use IvyForms\Services\GoogleSheets\GoogleSheetsDispatcherService;

/**
 * Listens to form submissions and dispatches Google Sheets sync.
 */
class GoogleSheetsEntryHooks
{
    private GoogleSheetsDispatcherService $dispatcher;

    public function __construct(GoogleSheetsDispatcherService $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function register(): void
    {
        add_action('ivyforms/form/after_submission', [$this, 'onFormSubmission'], 10, 4);
    }

    /**
     * @param array<string, mixed> $submissionData
     * @param array<mixed> $formFields
     */
    public function onFormSubmission(int $formId, array $submissionData, array $formFields, ?int $entryId): void
    {
        $formattedSubmissionData = $submissionData;
        $formattedSubmissionData['id'] = $entryId;

        // Defer dispatch until after the submission response is sent. WP-Cron is
        // unreliable in containerised environments, and serialising form field
        // entities into transients breaks placeholder resolution on replay.
        add_action('shutdown', function () use ($formId, $formattedSubmissionData, $formFields): void {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            $this->dispatcher->dispatchForSubmission(
                $formId,
                $formattedSubmissionData,
                $formFields
            );
        }, 999);
    }
}
