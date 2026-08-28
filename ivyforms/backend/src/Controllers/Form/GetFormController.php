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
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class GetFormController
 *
 * @package IvyForms\Controllers\Form
 */
class GetFormController extends Controller
{
    private FormService $formService;
    private FieldService $fieldService;
    private ConfirmationService $confirmationService;

    public function __construct(
        FormService $formService,
        FieldService $fieldService,
        ConfirmationService $confirmationService
    ) {
        $this->formService         = $formService;
        $this->fieldService        = $fieldService;
        $this->confirmationService = $confirmationService;
    }

    /**
     * @param WP_REST_Request $data
     * @return WP_REST_Response
     * @throws NotFoundException
     * @throws InvalidArgumentException|ForbiddenException|ValidationException
     * @throws QueryExecutionException
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

        // Sanitize the input data
        $formId = Sanitizer::sanitizeId($data->get_param('id'));

        $form      = $this->formService->getFormById($formId);
        $fieldsMap = $this->fieldService->getAllFieldsWithOptions($form->getId());
        $form->setFields($fieldsMap);

        $defaultConfirmationId = $this->confirmationService->ensureDefaultConfirmationExists($formId);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => array_merge($form->toArray(), [
                'confirmationId'  => $defaultConfirmationId,
                'submissionNonce' => wp_create_nonce('ivyformsFrontSubmissionNonce_' . $formId),
            ]),
        ], 200);
    }
}
