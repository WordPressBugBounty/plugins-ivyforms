<?php

namespace IvyForms\Controllers\GoogleSheets;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\GoogleSheets\Helpers\GoogleSheetsRequestSanitizer;
use IvyForms\Services\GoogleSheets\GoogleSheetsManagerService;
use WP_REST_Request;
use WP_REST_Response;

class GoogleSheetsCreateController extends Controller
{
    private GoogleSheetsManagerService $managerService;
    private GoogleSheetsRequestSanitizer $sanitizer;

    public function __construct(
        GoogleSheetsManagerService $managerService,
        GoogleSheetsRequestSanitizer $sanitizer
    ) {
        $this->managerService = $managerService;
        $this->sanitizer = $sanitizer;
    }

    /**
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $params = $this->sanitizer->sanitizeCreateData($data->get_params());

        if (!$params['form_id'] || !$params['spreadsheet_id'] || !$params['worksheet_name']) {
            throw new InvalidArgumentException('Form ID, spreadsheet ID, and worksheet name are required');
        }

        $integration = $this->managerService->createIntegration(
            $params['form_id'],
            $params['spreadsheet_id'],
            $params['spreadsheet_name'],
            $params['worksheet_name'],
            $params['field_mapping'],
            $params['smart_logic'],
            $params['enabled']
        );

        return new WP_REST_Response($integration->toArray(), 200);
    }
}
