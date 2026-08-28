<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

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
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class DeleteFormController
 *
 * @package IvyForms\Controllers\Form
 */
class DeleteFormController extends Controller
{
    private FormService $formService;
    private FieldService $fieldService;
    private NotificationService $notificationService;
    private ConfirmationService $confirmationService;
    private EntryService $entryService;

    public function __construct(
        FormService $formService,
        FieldService $fieldService,
        NotificationService $notificationService,
        ConfirmationService $confirmationService,
        EntryService $entryService
    ) {
        $this->formService = $formService;
        $this->fieldService = $fieldService;
        $this->notificationService = $notificationService;
        $this->confirmationService = $confirmationService;
        $this->entryService = $entryService;
    }

    /**
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

        // Check if the form ID is provided
        if (empty($data->get_param('id'))) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['form_id_required']
            );
        }

        // Sanitize the input data
        $formId = Sanitizer::sanitizeId($data->get_param('id'));

        $this->entryService->getDeletionManager()->deleteEntriesByFormIds([$formId]);
        $this->confirmationService->deleteConfirmationsByFormIds([$formId]);
        $this->notificationService->deleteNotificationsByFormIds([$formId]);
        $this->fieldService->deleteFieldsByFormIds([$formId]);
        $this->formService->deleteForm($formId);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => []
        ], 200);
    }
}
