<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Form;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class UpdateFormController
 *
 * @package IvyForms\Controllers\Form
 */
class UpdateFormStatusController extends Controller
{
    private FormService $formService;

    public function __construct(
        FormService $formService
    ) {
        $this->formService = $formService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Check if the form data is provided
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $formId = Sanitizer::sanitizeId($data->get_params()['id']);

        if ($formId <= 0) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_form_id']
            );
        }

        $value = (bool)($data->get_params()['value']);

        // Update the form status in the database
        $this->formService->updateFormStatus($formId, $value);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => [
                'id'        => $formId,
                'value'     => $value,
            ]
        ], 200);
    }
}
