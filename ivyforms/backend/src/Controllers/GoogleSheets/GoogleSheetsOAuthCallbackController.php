<?php

namespace IvyForms\Controllers\GoogleSheets;

use IvyForms\Services\GoogleSheets\Helpers\GoogleSheetsOAuthStateHelper;
use IvyForms\Services\GoogleSheets\GoogleSheetsService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles OAuth callback via middleware one-time exchange handoff.
 */
class GoogleSheetsOAuthCallbackController
{
    private GoogleSheetsService $googleSheetsService;

    public function __construct(GoogleSheetsService $googleSheetsService)
    {
        $this->googleSheetsService = $googleSheetsService;
    }

    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        $error = sanitize_text_field((string) $data->get_param('error'));

        if ($error !== '') {
            return $this->redirectTo($this->settingsRedirectUrl(['google_sheets_error' => '1']));
        }

        $state = (string) $data->get_param('state');

        if (!GoogleSheetsOAuthStateHelper::validateState($state)) {
            return $this->redirectTo($this->settingsRedirectUrl(['google_sheets_error' => '1']));
        }

        $exchangeKey = sanitize_text_field((string) $data->get_param('exchange'));

        if ($exchangeKey === '') {
            return $this->redirectTo($this->settingsRedirectUrl(['google_sheets_error' => '1']));
        }

        $result = $this->googleSheetsService->exchangeMiddlewareHandoff($exchangeKey);

        if (!$result['success']) {
            return $this->redirectTo($this->settingsRedirectUrl(['google_sheets_error' => '1']));
        }

        $this->googleSheetsService->saveOAuthTokens(
            (string) $result['accessToken'],
            (string) $result['refreshToken'],
            (int) ($result['expiresIn'] ?? 3600),
            (string) ($result['email'] ?? '')
        );

        return $this->redirectTo($this->settingsRedirectUrl(['google_sheets_connected' => '1']));
    }

    /**
     * @param array<string, string> $params
     */
    private function settingsRedirectUrl(array $params): string
    {
        return add_query_arg($params, admin_url('admin.php?page=ivyforms-settings'))
            . '#/integrations/google_sheets';
    }

    private function redirectTo(string $url): WP_REST_Response
    {
        $response = new WP_REST_Response(null, 302);
        $response->header('Location', $url);

        return $response;
    }
}
