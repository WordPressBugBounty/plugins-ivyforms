<?php

namespace IvyForms\Controllers\Entry;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Permissions\PermissionResourceResolver;
use IvyForms\Services\Permissions\RoutePermissionService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class BulkUpdateEntryController
 *
 * @package IvyForms\Controllers\Entry
 */
class BulkUpdateEntryController extends Controller
{
    private EntryService $entryService;

    private RoutePermissionService $routePermissionService;

    private PermissionResourceResolver $permissionResourceResolver;

    public function __construct(
        EntryService $entryService,
        RoutePermissionService $routePermissionService,
        PermissionResourceResolver $permissionResourceResolver
    ) {
        $this->entryService               = $entryService;
        $this->routePermissionService     = $routePermissionService;
        $this->permissionResourceResolver = $permissionResourceResolver;
    }

    /**
     * Handle bulk update request for entries
     *
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException
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

        // Sanitize request data
        $sanitized = Sanitizer::sanitizeBulkUpdateData([
            'column' => $data->get_param('column'),
            'value' => $data->get_param('value'),
            'ids' => $data->get_param('ids'),
        ]);

        // Validate IDs
        if (empty($sanitized['ids'])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => BackendStrings::getExceptionStrings()['invalid_or_missing_ids'],
            ], 400);
        }

        // Validate column
        if (empty($sanitized['column'])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => BackendStrings::getExceptionStrings()['table_or_attribute_not_allowed'],
            ], 400);
        }

        $formIdsByEntryId = $this->permissionResourceResolver->formIdsFromEntryIds($sanitized['ids']);

        foreach ($sanitized['ids'] as $entryId) {
            $id = (int) $entryId;
            if (!isset($formIdsByEntryId[$id])) {
                throw new ForbiddenException('forbidden');
            }
        }

        foreach (array_unique(array_values($formIdsByEntryId)) as $formId) {
            if (!$this->routePermissionService->canEditEntries($formId)) {
                throw new ForbiddenException('forbidden');
            }
        }

        $this->entryService->getEntryManager()->bulkUpdateColumn(
            $sanitized['column'],
            $sanitized['value'],
            $sanitized['ids']
        );

        return new WP_REST_Response([
            'success' => true,
            'message' => BackendStrings::getExceptionStrings()['batch_update_successful'],
        ], 200);
    }
}
