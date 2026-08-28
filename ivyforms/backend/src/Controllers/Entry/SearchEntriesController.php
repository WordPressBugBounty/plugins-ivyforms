<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Entry;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Factory\Entry\EntryFactory;
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Permissions\AdminFormListPermissionService;
use IvyForms\Services\Permissions\IvyFormsAccess;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class SearchEntriesController extends Controller
{
    private EntryService $entryService;

    private FormService $formService;

    private AdminFormListPermissionService $adminFormListPermissionService;

    public function __construct(
        EntryService $entryService,
        FormService $formService,
        AdminFormListPermissionService $adminFormListPermissionService
    ) {
        $this->entryService                     = $entryService;
        $this->formService                      = $formService;
        $this->adminFormListPermissionService   = $adminFormListPermissionService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Sanitize the filtering data
        $params = Sanitizer::sanitizeSearchParams($data->get_query_params());

        if (!IvyFormsAccess::hasImplicitElevatedAccess()) {
            $allIdsUnique = $this->formService->getAllFormIds();
            $visibleIds   = $this->adminFormListPermissionService->filterFormIdsVisibleInEntryLists($allIdsUnique);
            if ($visibleIds !== $allIdsUnique) {
                $params['visibleFormIds'] = $visibleIds;
            }
        }

        // Call the searchEntries method
        $entriesArr = $this->entryService->getEntryManager()->searchEntries($params);

        // Map the data to Entry entities
        $entries = array_map(function ($entryData) {
            $entryEntity = EntryFactory::create($entryData);
            return $entryEntity->toArray();
        }, $entriesArr['data']);

        $filterCount = $this->entryService->getEntryManager()->getFilterCount($params);
        // Pass `true` to retrieve raw field values for the result table.
        $entryFields = $this->entryService->getEntryFieldManager()->getEntryFields($entriesArr['data'], true);
        $formIdToName = $entriesArr['formIdToName'] ?? [];

        // Return the response with data and meta
        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data' => [
                'data' => $entries,
                'meta' => $entriesArr['meta'] ?? [],
                'filterCount' => $filterCount,
                'entryFields' => $entryFields,
                'formIdToName' => $formIdToName,
            ],
        ], 200);
    }
}
