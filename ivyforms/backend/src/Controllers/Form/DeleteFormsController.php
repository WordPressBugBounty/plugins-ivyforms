<?php

namespace IvyForms\Controllers\Form;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Permissions\RoutePermissionService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class DeleteFormsController extends Controller
{
    private FormService $formService;
    private FieldService $fieldService;
    private NotificationService $notificationService;
    private ConfirmationService $confirmationService;
    private EntryService $entryService;

    private RoutePermissionService $routePermissionService;

    public function __construct(
        FormService $formService,
        FieldService $fieldService,
        NotificationService $notificationService,
        ConfirmationService $confirmationService,
        EntryService $entryService,
        RoutePermissionService $routePermissionService
    ) {
        $this->formService              = $formService;
        $this->fieldService             = $fieldService;
        $this->notificationService      = $notificationService;
        $this->confirmationService      = $confirmationService;
        $this->entryService             = $entryService;
        $this->routePermissionService   = $routePermissionService;
    }

    /**
     * Delete forms by IDs
     *
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException
     * @throws ForbiddenException
     * @throws QueryExecutionException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Check if the form IDs are provided
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $ids = Sanitizer::sanitizeIds($data->get_param('ids'));
        if ($ids === []) {
            return new WP_REST_Response([
                'success' => false,
                'message' => BackendStrings::getExceptionStrings()['invalid_or_missing_ids'],
            ], 400);
        }

        foreach ($ids as $formId) {
            if (!$this->routePermissionService->canDeleteForms((int) $formId)) {
                throw new ForbiddenException('forbidden');
            }
        }

        $this->entryService->getDeletionManager()->deleteEntriesByFormIds($ids);
        $this->confirmationService->deleteConfirmationsByFormIds($ids);
        $this->notificationService->deleteNotificationsByFormIds($ids);
        $this->fieldService->deleteFieldsByFormIds($ids);
        $this->formService->deleteForms($ids);

        return new WP_REST_Response(
            [
                'message' => BackendStrings::getCommonStrings()['ok'],
                'data'    => [],
            ],
            200
        );
    }
}
