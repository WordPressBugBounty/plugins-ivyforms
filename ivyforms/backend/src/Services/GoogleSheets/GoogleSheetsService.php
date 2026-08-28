<?php

namespace IvyForms\Services\GoogleSheets;

use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Google Sheets API service — site token storage + Sheets/Drive usage.
 * OAuth app credentials and code/token exchange live in middleware.
 *
 * @SuppressWarnings(PHPMD)
 */
class GoogleSheetsService
{
    private const GOOGLE_USERINFO_URL = 'https://www.googleapis.com/oauth2/v2/userinfo';
    private const DRIVE_FILES_URL = 'https://www.googleapis.com/drive/v3/files';
    private const SHEETS_API_URL = 'https://sheets.googleapis.com/v4/spreadsheets';

    private SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    public function hasCredentials(): bool
    {
        return $this->isOAuthConnection();
    }

    public function isOAuthAppConfigured(): bool
    {
        return $this->getMiddlewareApiBaseUrl() !== '';
    }

    public function createAuthUrl(?string $oauthState = null): string
    {
        $state = $oauthState ?? (defined('IVYFORMS_SITE_URL') ? (string) IVYFORMS_SITE_URL : (string) home_url());

        return $this->fetchMiddlewareAuthorizationUrl($state);
    }

    public function getOAuthNotConfiguredMessage(): string
    {
        return BackendStrings::getIntegrationsStrings()['google_sheets_oauth_not_configured'];
    }

    public function getMiddlewareUnreachableMessage(): string
    {
        return BackendStrings::getIntegrationsStrings()['google_sheets_connection_error'];
    }

    public function isMiddlewareReachable(): bool
    {
        $baseUrl = $this->getMiddlewareApiBaseUrl();

        if ($baseUrl === '') {
            return false;
        }

        $response = wp_remote_get(
            $baseUrl . '/google-sheets/redirect',
            [
                'timeout'     => 5,
                'redirection' => 0,
            ]
        );

        if (is_wp_error($response)) {
            return false;
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);

        return $statusCode >= 200 && $statusCode < 500;
    }

    /**
     * Exchange a one-time middleware handoff key for OAuth tokens (server-side POST).
     *
     * @return array{
     *     success: bool,
     *     message: string,
     *     accessToken?: string,
     *     refreshToken?: string,
     *     expiresIn?: int,
     *     email?: string
     * }
     */
    public function exchangeMiddlewareHandoff(string $exchangeKey): array
    {
        $exchangeKey = sanitize_text_field($exchangeKey);

        if ($exchangeKey === '') {
            return [
                'success' => false,
                'message' => BackendStrings::getIntegrationsStrings()['google_sheets_connection_error'],
            ];
        }

        $url = $this->getMiddlewareApiBaseUrl() . '/google-sheets/oauth/exchange';

        if ($url === '/google-sheets/oauth/exchange') {
            return [
                'success' => false,
                'message' => BackendStrings::getIntegrationsStrings()['google_sheets_connection_error'],
            ];
        }

        $response = wp_remote_post($url, [
            'body'    => ['exchange' => $exchangeKey],
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
            ];
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($statusCode < 200 || $statusCode >= 300 || !is_array($body)) {
            $message = is_array($body) && !empty($body['message'])
                ? (string) $body['message']
                : BackendStrings::getIntegrationsStrings()['google_sheets_connection_error'];

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $accessToken = (string) ($body['accessToken'] ?? $body['access_token'] ?? '');
        $refreshToken = (string) ($body['refreshToken'] ?? $body['refresh_token'] ?? '');

        if ($accessToken === '' || $refreshToken === '') {
            return [
                'success' => false,
                'message' => BackendStrings::getIntegrationsStrings()['google_sheets_connection_error'],
            ];
        }

        return [
            'success'      => true,
            'message'      => BackendStrings::getIntegrationsStrings()['google_sheets_connection_success'],
            'accessToken'  => $accessToken,
            'refreshToken' => $refreshToken,
            'expiresIn'    => (int) ($body['expiresIn'] ?? $body['expires_in'] ?? 3600),
            'email'        => sanitize_email((string) ($body['email'] ?? '')),
        ];
    }

    /**
     * Persist tokens received from the OAuth middleware callback.
     */
    public function saveOAuthTokens(
        string $accessToken,
        string $refreshToken,
        int $expiresIn,
        string $email = ''
    ): void {
        $settings = $this->getSettings();

        if ($email === '') {
            $email = $this->fetchUserEmail($accessToken);
        }

        $settings['authType'] = 'oauth';
        $settings['accessToken'] = $accessToken;
        $settings['refreshToken'] = $refreshToken;
        $settings['tokenExpiresAt'] = time() + max(60, $expiresIn - 30);
        $settings['accountEmail'] = $email;
        $settings['connected'] = true;
        $settings['enabled'] = true;

        $this->saveSettings($settings);
    }

    public function isConnected(): bool
    {
        $settings = $this->getSettings();

        return $this->isOAuthConnection() && !empty($settings['refreshToken']);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return $this->settingsService->getSetting('integrations', 'google_sheets') ?? [];
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function saveSettings(array $settings): void
    {
        $this->settingsService->setSetting('integrations', 'google_sheets', $settings);
    }

    public function disconnect(): void
    {
        $settings = $this->getSettings();

        $tokenToRevoke = (string) ($settings['refreshToken'] ?? '');
        if ($tokenToRevoke === '') {
            $tokenToRevoke = (string) ($settings['accessToken'] ?? '');
        }

        if ($tokenToRevoke !== '') {
            $this->revokeOAuthToken($tokenToRevoke);
        }

        $settings['accessToken'] = '';
        $settings['tokenExpiresAt'] = 0;
        $settings['refreshToken'] = '';
        $settings['connected'] = false;
        $settings['accountEmail'] = '';
        $settings['authType'] = '';

        $this->saveSettings($settings);
    }

    private function revokeOAuthToken(string $token): void
    {
        wp_remote_post('https://oauth2.googleapis.com/revoke', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body'    => http_build_query(['token' => $token]),
            'timeout' => 15,
        ]);
    }

    /**
     * @return array<array{id: string, name: string}>
     */
    public function listSpreadsheets(): array
    {
        $url = add_query_arg(
            [
                'q'        => "mimeType='application/vnd.google-apps.spreadsheet' and trashed=false",
                'fields'   => 'files(id,name)',
                'pageSize' => 100,
            ],
            self::DRIVE_FILES_URL
        );

        $response = $this->apiRequest('GET', $url);

        if (!$response['success']) {
            return [];
        }

        $data = json_decode($response['body'], true);
        $files = $data['files'] ?? [];

        return array_map(static function ($file) {
            return [
                'id'   => (string) ($file['id'] ?? ''),
                'name' => (string) ($file['name'] ?? ''),
            ];
        }, is_array($files) ? $files : []);
    }

    /**
     * @return array<array{title: string, sheet_id: int}>
     */
    public function listWorksheets(string $spreadsheetId): array
    {
        $url = self::SHEETS_API_URL . '/' . rawurlencode($spreadsheetId) . '?fields=sheets.properties';

        $response = $this->apiRequest('GET', $url);

        if (!$response['success']) {
            return [];
        }

        $data = json_decode($response['body'], true);
        $sheets = $data['sheets'] ?? [];
        $result = [];

        foreach (is_array($sheets) ? $sheets : [] as $sheet) {
            $properties = $sheet['properties'] ?? [];
            if (empty($properties['title'])) {
                continue;
            }

            $result[] = [
                'title'    => (string) $properties['title'],
                'sheet_id' => (int) ($properties['sheetId'] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    public function listColumns(string $spreadsheetId, string $worksheetName): array
    {
        $range = $this->buildRange($worksheetName, '1:1');
        $url = self::SHEETS_API_URL . '/' . rawurlencode($spreadsheetId) . '/values/' . rawurlencode($range);

        $response = $this->apiRequest('GET', $url);

        if (!$response['success']) {
            return [];
        }

        $data = json_decode($response['body'], true);
        $values = $data['values'][0] ?? [];

        if (!is_array($values)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $values), static function ($value) {
            return trim($value) !== '';
        }));
    }

    /**
     * @param array<int, string> $rowValues
     * @return array{success: bool, message: string, status_code: int|null}
     */
    public function appendRow(string $spreadsheetId, string $worksheetName, array $rowValues): array
    {
        $range = $this->buildRange($worksheetName, 'A:Z');
        $url = self::SHEETS_API_URL . '/' . rawurlencode($spreadsheetId)
            . '/values/' . rawurlencode($range) . ':append?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS';

        $response = $this->apiRequest('POST', $url, [
            'values' => [array_values($rowValues)],
        ]);

        if ($response['success']) {
            return [
                'success'     => true,
                'message'     => 'Row appended successfully',
                'status_code' => $response['status_code'],
            ];
        }

        return [
            'success'     => false,
            'message'     => $response['message'],
            'status_code' => $response['status_code'],
        ];
    }

    /**
     * @param array<string, mixed>|null $body
     * @return array{success: bool, status_code: int|null, body: string, message: string}
     */
    private function apiRequest(string $method, string $url, ?array $body = null, ?string $accessToken = null): array
    {
        $token = $accessToken ?? $this->getValidAccessToken();

        if ($token === null) {
            return [
                'success'     => false,
                'status_code' => null,
                'body'        => '',
                'message'     => 'Google Sheets is not connected',
            ];
        }

        $args = [
            'method'  => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 30,
        ];

        if ($body !== null) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return [
                'success'     => false,
                'status_code' => null,
                'body'        => '',
                'message'     => $response->get_error_message(),
            ];
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);
        $responseBody = (string) wp_remote_retrieve_body($response);

        if ($statusCode === 401 && $accessToken === null) {
            $refreshed = $this->refreshAccessToken();
            if ($refreshed) {
                return $this->apiRequest($method, $url, $body, $this->getValidAccessToken());
            }
        }

        $success = $statusCode >= 200 && $statusCode < 300;

        return [
            'success'     => $success,
            'status_code' => $statusCode,
            'body'        => $responseBody,
            'message'     => $success ? 'OK' : $this->extractErrorMessage($responseBody, $statusCode),
        ];
    }

    private function getValidAccessToken(): ?string
    {
        $settings = $this->getSettings();
        $accessToken = (string) ($settings['accessToken'] ?? '');
        $expiresAt = (int) ($settings['tokenExpiresAt'] ?? 0);

        if ($accessToken === '') {
            return null;
        }

        if ($expiresAt > time() + 60) {
            return $accessToken;
        }

        if (!$this->refreshAccessToken()) {
            return null;
        }

        $settings = $this->getSettings();
        $accessToken = (string) ($settings['accessToken'] ?? '');

        return $accessToken !== '' ? $accessToken : null;
    }

    private function refreshAccessToken(): bool
    {
        $tokenResult = $this->fetchOAuthAccessToken(true);

        if (!$tokenResult['success']) {
            return false;
        }

        $settings = $this->getSettings();
        $settings['accessToken'] = $tokenResult['access_token'];
        $settings['tokenExpiresAt'] = $tokenResult['expires_at'];
        $settings['connected'] = true;

        $this->saveSettings($settings);

        return true;
    }

    /**
     * @return array{success: bool, message: string, access_token?: string, expires_at?: int}
     */
    private function fetchOAuthAccessToken(bool $forceRefresh): array
    {
        $settings = $this->getSettings();
        $accessToken = (string) ($settings['accessToken'] ?? '');
        $expiresAt = (int) ($settings['tokenExpiresAt'] ?? 0);

        if (!$forceRefresh && $accessToken !== '' && $expiresAt > time() + 60) {
            return [
                'success'      => true,
                'message'      => 'OK',
                'access_token' => $accessToken,
                'expires_at'   => $expiresAt,
            ];
        }

        $refreshToken = (string) ($settings['refreshToken'] ?? '');

        if ($refreshToken === '') {
            return [
                'success' => false,
                'message' => BackendStrings::getIntegrationsStrings()['google_sheets_credentials_missing'],
            ];
        }

        $response = wp_remote_post($this->getMiddlewareRefreshUrl(), [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'    => wp_json_encode([
                'refresh_token' => $refreshToken,
                'site_url'      => IVYFORMS_SITE_URL,
            ]),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
            ];
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($statusCode < 200 || $statusCode >= 300 || !is_array($body) || empty($body['access_token'])) {
            $message = is_array($body) && !empty($body['error_description'])
                ? (string) $body['error_description']
                : (is_array($body) && !empty($body['message'])
                    ? (string) $body['message']
                    : 'Failed to refresh Google access token');

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        $expiresIn = (int) ($body['expires_in'] ?? 3600);

        return [
            'success'      => true,
            'message'      => 'OK',
            'access_token' => (string) $body['access_token'],
            'expires_at'   => time() + max(60, $expiresIn - 30),
        ];
    }

    private function isOAuthConnection(): bool
    {
        $settings = $this->getSettings();

        return !empty($settings['refreshToken'])
            || (($settings['authType'] ?? '') === 'oauth' && !empty($settings['accessToken']));
    }

    private function getMiddlewareBaseUrl(): string
    {
        if (defined('IVYFORMS_GOOGLE_SHEETS_MIDDLEWARE_URL') && IVYFORMS_GOOGLE_SHEETS_MIDDLEWARE_URL !== '') {
            return rtrim((string) IVYFORMS_GOOGLE_SHEETS_MIDDLEWARE_URL, '/');
        }

        $filtered = apply_filters('ivyforms/google_sheets/middleware_url', '');

        return is_string($filtered) ? rtrim($filtered, '/') : '';
    }

    private function fetchMiddlewareAuthorizationUrl(string $state): string
    {
        $baseUrl = $this->getMiddlewareApiBaseUrl();

        if ($baseUrl === '') {
            return '';
        }

        $response = wp_remote_get(
            $baseUrl . '/google-sheets/authorize-url?' . http_build_query(['state' => $state]),
            ['timeout' => 15]
        );

        if (is_wp_error($response)) {
            return '';
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if ($statusCode < 200 || $statusCode >= 300 || !is_array($body)) {
            return '';
        }

        return (string) ($body['auth_url'] ?? '');
    }

    private function getMiddlewareRefreshUrl(): string
    {
        return $this->getMiddlewareApiBaseUrl() . '/google-sheets/refresh';
    }

    /**
     * Base URL for server-side middleware calls (token refresh).
     * In Docker, localhost points at the container — use host.docker.internal when applicable.
     */
    private function getMiddlewareApiBaseUrl(): string
    {
        $baseUrl = $this->getMiddlewareBaseUrl();

        if ($baseUrl === '') {
            return '';
        }

        if (str_contains($baseUrl, '://localhost') && $this->isRunningInDockerContainer()) {
            $baseUrl = (string) preg_replace(
                '#^https?://localhost#i',
                'http://host.docker.internal',
                $baseUrl
            );
        }

        /**
         * @param string $baseUrl
         */
        return (string) apply_filters('ivyforms/google_sheets/middleware_api_url', $baseUrl);
    }

    /**
     * Detect Docker without triggering open_basedir warnings on shared hosting.
     */
    private function isRunningInDockerContainer(): bool
    {
        $openBasedir = ini_get('open_basedir');
        if (is_string($openBasedir) && $openBasedir !== '') {
            $allowed = false;
            foreach (explode(PATH_SEPARATOR, $openBasedir) as $root) {
                $root = rtrim($root, '/\\');
                if ($root !== '' && str_starts_with('/.dockerenv', $root)) {
                    $allowed = true;
                    break;
                }
            }

            if (!$allowed) {
                return false;
            }
        }

        return file_exists('/.dockerenv');
    }

    private function fetchUserEmail(string $accessToken): string
    {
        $response = wp_remote_get(self::GOOGLE_USERINFO_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return '';
        }

        $body = json_decode((string) wp_remote_retrieve_body($response), true);

        if (!is_array($body)) {
            return '';
        }

        return sanitize_email((string) ($body['email'] ?? ''));
    }

    private function extractErrorMessage(string $body, int $statusCode): string
    {
        $data = json_decode($body, true);

        if (is_array($data)) {
            if (!empty($data['error']['message'])) {
                return (string) $data['error']['message'];
            }

            if (!empty($data['error_description'])) {
                return (string) $data['error_description'];
            }
        }

        return 'Google API request failed with status ' . $statusCode;
    }

    private function buildRange(string $worksheetName, string $cellRange): string
    {
        $escapedName = str_replace("'", "''", $worksheetName);

        return "'" . $escapedName . "'!" . $cellRange;
    }
}
