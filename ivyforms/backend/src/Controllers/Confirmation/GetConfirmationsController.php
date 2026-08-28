<?php

/**
 * @copyright © Melograno Venture Studio. All rights.
 * @licence   See COPYING.md for license details.
 */

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
 * Class GetConfirmationsController
 *
 * @package IvyForms\Controllers\Confirmation
 */
class GetConfirmationsController extends Controller
{
    private ConfirmationService $confirmationService;

    public function __construct(
        ConfirmationService $confirmationService
    ) {
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
        $formId = Sanitizer::sanitizeId($data->get_param('id'));

        $confirmationsArr = $this->confirmationService->getConfirmationsById($formId);

        $confirmations = [];

        foreach ($confirmationsArr as $confirmation) {
            $confirmations[] = $confirmation->toArray();
        }

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => $confirmations,
        ], 200);
    }
}
