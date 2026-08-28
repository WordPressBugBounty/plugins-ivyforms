<?php

namespace IvyForms\Controllers\GoogleSheets;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\GoogleSheets\Helpers\GoogleSheetsOAuthStateHelper;
use IvyForms\Services\GoogleSheets\GoogleSheetsService;
use WP_REST_Request;
use WP_REST_Response;

class GoogleSheetsConnectController extends Controller
{
    private GoogleSheetsService $googleSheetsService;

    public function __construct(GoogleSheetsService $googleSheetsService)
    {
        $this->googleSheetsService = $googleSheetsService;
    }

    /**
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        if (!$this->googleSheetsService->isMiddlewareReachable()) {
            throw new InvalidArgumentException(
                $this->googleSheetsService->getMiddlewareUnreachableMessage()
            );
        }

        $authUrl = $this->googleSheetsService->createAuthUrl(GoogleSheetsOAuthStateHelper::createState());

        if ($authUrl === '') {
            throw new InvalidArgumentException(
                $this->googleSheetsService->getOAuthNotConfiguredMessage()
            );
        }

        return new WP_REST_Response([
            'auth_url' => $authUrl,
        ], 200);
    }
}
