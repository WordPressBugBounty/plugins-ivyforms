<?php

namespace IvyForms\Controllers\Confirmation;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Factory\Confirmation\ConfirmationFactory;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class UpdateConfirmationController
 *
 * @package IvyForms\Controllers\Confirmation
 */
class UpdateConfirmationController extends Controller
{
    private ConfirmationService $confirmationService;

    public function __construct(ConfirmationService $confirmationService)
    {
        $this->confirmationService = $confirmationService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws ForbiddenException
     * @throws ValidationException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));
        // Validate and sanitize input data
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $params = Sanitizer::sanitizeConfirmationData($data->get_params());

        if (empty(trim($params['name'] ?? ''))) {
            throw new InvalidArgumentException(
                BackendStrings::getSettingsFormBuilderStrings()['confirmation_name_required']
            );
        }

        $confirmation = ConfirmationFactory::create($params);

        $confirmationId = $confirmation->getId();

        $this->confirmationService->updateConfirmation($confirmationId, $confirmation);

        if (!$confirmation->getId()) {
            throw new QueryExecutionException(
                BackendStrings::getSettingsFormBuilderStrings()['failed_to_update_confirmation'] . '.'
            );
        }

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => $confirmation->toArray()
        ], 200);
    }
}
