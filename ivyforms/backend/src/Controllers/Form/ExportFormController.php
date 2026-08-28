<?php

namespace IvyForms\Controllers\Form;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Form\FormImportExportService;
use IvyForms\Services\Permissions\RoutePermissionService;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\ValueObjects\Form\FormExportOptions;
use WP_REST_Request;
use WP_REST_Response;

class ExportFormController extends Controller
{
    private FormImportExportService $importExportService;
    private RoutePermissionService $routePermissionService;

    public function __construct(
        FormImportExportService $importExportService,
        RoutePermissionService $routePermissionService
    ) {
        $this->importExportService = $importExportService;
        $this->routePermissionService = $routePermissionService;
    }

    /**
     * Handle the export request
     *
     * @param WP_REST_Request<array<string, mixed>> $data
     * @return WP_REST_Response
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Get form IDs from request
        $formIds = $data->get_param('formIds');

        if (empty($formIds)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['no_form_ids_for_export']
            );
        }

        // Sanitize and validate form IDs
        $formIds = array_values(array_unique(array_filter(
            array_map('intval', (array) $formIds),
            static fn (int $id): bool => $id > 0
        )));

        if ($formIds === []) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['no_form_ids_for_export']
            );
        }

        foreach ($formIds as $formId) {
            if (!$this->routePermissionService->canExportForms($formId)) {
                throw new ForbiddenException(
                    BackendStrings::getExceptionStrings()['forbidden']
                );
            }
        }

        $exportOptions = FormExportOptions::fromRequestParams(
            $data->get_param('includeStyles'),
            $data->get_param('includeEntries')
        );

        // Export forms using service
        $exportData = $this->importExportService->exportForms($formIds, $exportOptions);

        return new WP_REST_Response([
            'success' => true,
            'data'    => $exportData,
            'message' => BackendStrings::getCommonStrings()['forms_exported_successfully']
        ], 200);
    }
}
