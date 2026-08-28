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
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Permissions\PermissionResourceResolver;
use IvyForms\Services\Permissions\RoutePermissionService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class DeleteEntriesController extends Controller
{
    private EntryService $entryService;

    private RoutePermissionService $routePermissionService;

    private PermissionResourceResolver $permissionResourceResolver;

    public function __construct(
        EntryService $entryService,
        RoutePermissionService $routePermissionService,
        PermissionResourceResolver $permissionResourceResolver
    ) {
        $this->entryService                 = $entryService;
        $this->routePermissionService       = $routePermissionService;
        $this->permissionResourceResolver   = $permissionResourceResolver;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     * @throws NotFoundException
     * @throws InvalidArgumentException
     * @throws ForbiddenException
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

        $formIdsByEntryId = $this->permissionResourceResolver->formIdsFromEntryIds($ids);

        foreach ($ids as $entryId) {
            $id = (int) $entryId;
            if (!isset($formIdsByEntryId[$id])) {
                throw new ForbiddenException('forbidden');
            }
        }

        foreach (array_unique(array_values($formIdsByEntryId)) as $formId) {
            if (!$this->routePermissionService->canDeleteEntries($formId)) {
                throw new ForbiddenException('forbidden');
            }
        }

        // Bulk delete all entry fields for these entries
        $this->entryService->getDeletionManager()->deleteEntryFieldsByEntryIds($ids);
        // Bulk delete all entries
        $this->entryService->getDeletionManager()->deleteEntriesByIds($ids);

        // Return a success response
        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data' => null,
        ], 200);
    }
}
