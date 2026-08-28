<?php

namespace IvyForms\Controllers\Form;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Form\FormImportExportService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class ImportFormController extends Controller
{
    private FormImportExportService $importExportService;

    public function __construct(FormImportExportService $importExportService)
    {
        $this->importExportService = $importExportService;
    }

    /**
     * Handle the import request
     *
     * @param WP_REST_Request<array<string, mixed>> $data
     * @return WP_REST_Response
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $importedForms = $this->importExportService->importFromRawPayload(
            $data->get_param('importData')
        );
        if (empty($importedForms)) {
            throw new InvalidArgumentException(
                BackendStrings::getCommonStrings()['no_forms_imported']
            );
        }

        return new WP_REST_Response([
            'success' => true,
            'data'    => $importedForms,
            'message' => BackendStrings::getCommonStrings()['forms_imported_successfully']
        ], 200);
    }
}
