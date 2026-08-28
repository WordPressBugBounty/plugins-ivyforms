<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Entry;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
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
 * Class DeleteEntryController
 *
 * @package IvyForms\Application\Controllers\Entry
 */
class DeleteEntryController extends Controller
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
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Check if the entry ID is provided
        if (empty($data->get_param('id'))) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['entry_id_required']
            );
        }
        $id = Sanitizer::sanitizeId($data->get_param('id')) ;

        // Bulk delete all entry fields for the entry
        $this->entryService->getDeletionManager()->deleteEntryFieldsByEntryIds([$id]);

        // Delete the entry itself
        $this->entryService->getDeletionManager()->deleteEntry($id);

        // Return a success response
        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data' => null,
        ], 200);
    }
}
