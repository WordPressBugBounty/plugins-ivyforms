<?php

namespace IvyForms\Controllers\GoogleSheets;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\GoogleSheets\GoogleSheetsService;
use WP_REST_Request;
use WP_REST_Response;

class GoogleSheetsStatusController extends Controller
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

        $settings = $this->googleSheetsService->getSettings();
        $isConnected = $this->googleSheetsService->isConnected();

        return new WP_REST_Response([
            'connected' => $isConnected,
            'email'     => $isConnected
                ? ($settings['accountEmail'] ?? $settings['clientEmail'] ?? '')
                : '',
        ], 200);
    }
}
