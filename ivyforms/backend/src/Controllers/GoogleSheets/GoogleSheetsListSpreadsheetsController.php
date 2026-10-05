<?php

namespace IvyForms\Controllers\GoogleSheets;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\GoogleSheets\GoogleSheetsService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Legacy spreadsheet list. The form builder selects a spreadsheet through the
 * Google Picker and no longer calls this route. A drive.file token cannot list
 * the user's spreadsheets, so the response is often empty.
 *
 * @deprecated Prefer the picker-config endpoint and the Picker UI.
 */
class GoogleSheetsListSpreadsheetsController extends Controller
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

        return new WP_REST_Response([
            'spreadsheets' => $this->googleSheetsService->listSpreadsheets(),
        ], 200);
    }
}
