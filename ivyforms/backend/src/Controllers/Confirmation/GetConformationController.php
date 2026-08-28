<?php

namespace IvyForms\Controllers\Confirmation;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class GetConformationController
 *
 * @package IvyForms\Controllers\Confirmation
 */
class GetConformationController extends Controller
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
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));
        $confirmationId = Sanitizer::sanitizeId($data->get_param('id'));

        if (empty($confirmationId)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['confirmation_id_required']
            );
        }

        $confirmation = $this->confirmationService->getConfirmationById($confirmationId);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => $confirmation->toArray()
        ], 200);
    }
}
