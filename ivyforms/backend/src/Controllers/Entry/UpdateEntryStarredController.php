<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Entry;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class UpdateEntryStarredController extends Controller
{
    private EntryService $entryService;

    public function __construct(
        EntryService $entryService
    ) {
        $this->entryService = $entryService;
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

        // Check if the entry data is provided
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $entryId = Sanitizer::sanitizeId($data->get_params()['id']);

        if ($entryId <= 0) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_entry_id']
            );
        }

        $value = (bool)($data->get_params()['value']);

        // Update the specific attribute in the database
        $this->entryService->getEntryManager()->updateEntryStarred($entryId, $value);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => [
                'id'        => $entryId,
                'value'     => $value,
            ]
        ], 200);
    }
}
