<?php

namespace IvyForms\Controllers\GoogleSheets;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\GoogleSheets\GoogleSheetsService;
use WP_REST_Request;
use WP_REST_Response;

class GoogleSheetsListWorksheetsController extends Controller
{
    private GoogleSheetsService $googleSheetsService;

    public function __construct(GoogleSheetsService $googleSheetsService)
    {
        $this->googleSheetsService = $googleSheetsService;
    }

    /**
     * @throws ForbiddenException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $spreadsheetId = Sanitizer::sanitizeText((string) $data->get_param('spreadsheet_id'));

        return new WP_REST_Response([
            'worksheets' => $this->googleSheetsService->listWorksheets($spreadsheetId),
        ], 200);
    }
}
