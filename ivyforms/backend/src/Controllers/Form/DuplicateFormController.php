<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Form;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Factory\Form\FormFactory;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Permissions\AccessRulesFormScopeExtender;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class DuplicateFormController
 *
 * @package IvyForms\Controllers\Form
 */
class DuplicateFormController extends Controller
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
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Check if the form ID is provided
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $formId = Sanitizer::sanitizeId($data->get_param('id')) ;

        $newFormId = $this->formService->duplicateForm($formId);

        $this->fieldService->duplicateFields($formId, $newFormId);
        $this->notificationService->duplicateNotifications($formId, $newFormId);
        $this->confirmationService->duplicateConfirmations($formId, $newFormId);

        $this->accessRulesFormScopeExtender->appendCreatedFormToCurrentUserScope($newFormId);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => $this->formService->getFormById($newFormId)->toArray()
        ], 200);
    }
}
