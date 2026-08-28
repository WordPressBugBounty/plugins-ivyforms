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
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Permissions\AdminFormListPermissionService;
use IvyForms\Services\Permissions\IvyFormsAccess;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class SearchFormsController extends Controller
{
    private FormService $formService;

    private EntryService $entryService;

    private AdminFormListPermissionService $adminFormListPermissionService;

    public function __construct(
        FormService $formService,
        EntryService $entryService,
        AdminFormListPermissionService $adminFormListPermissionService
    ) {
        $this->formService                     = $formService;
        $this->entryService                    = $entryService;
        $this->adminFormListPermissionService = $adminFormListPermissionService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Sanitize the filtering data
        $params = Sanitizer::sanitizeSearchParams($data->get_query_params());

        if (!IvyFormsAccess::hasImplicitElevatedAccess()) {
            $allIdsUnique = $this->formService->getAllFormIds();
            $visibleIds   = $this->adminFormListPermissionService->filterFormIdsVisibleInAdminLists($allIdsUnique);
            if ($visibleIds !== $allIdsUnique) {
                $params['visibleFormIds'] = $visibleIds;
            }
        }

        // Call the searchForms method
        $formsArr = $this->formService->searchForms($params);

        // Map the data to an array format
        $forms = array_map(fn($form) => (array) $form, $formsArr['data']);

        $filterCount = $this->formService->getFilterCount($params['shouldGetCount']);

        // Collect form IDs for entry count
        $formIds = array_column($forms, 'id');
        $entryCounts = $this->entryService->getEntryManager()->getEntryCountByFormIds($formIds);

        // Return the response with data, meta, filterCount, and entryCounts
        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data' => [
                'data' => $forms,
                'meta' => $formsArr['meta'],
                'filterCount' => $filterCount,
                'entryCounts' => $entryCounts,
            ],
        ], 200);
    }
}
