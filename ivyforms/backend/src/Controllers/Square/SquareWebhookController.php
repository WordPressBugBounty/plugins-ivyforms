<?php

namespace IvyForms\Controllers\Square;

use IvyForms\Services\Integrations\MiddlewareSignatureService;
use IvyForms\Services\Payment\SquareOAuthService;
use IvyForms\Services\Payment\WebhookEventDedupeStore;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Public webhook endpoint — IvyForms middleware forwards signed Square revocation events here.
 */
class SquareWebhookController
{
    private SquareOAuthService $squareOAuthService;
    private MiddlewareSignatureService $middlewareSignatureService;

    public function __construct(
        SquareOAuthService $squareOAuthService,
        MiddlewareSignatureService $middlewareSignatureService
    ) {
        $this->squareOAuthService = $squareOAuthService;
        $this->middlewareSignatureService = $middlewareSignatureService;
    }

    public function __invoke(WP_REST_Request $data): WP_REST_Response
    {
        return $this->handle($data);
    }

    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        $rawBody = $data->get_body();
        $signature = (string) $data->get_header('X-IvyForms-Signature');
        if (!$this->middlewareSignatureService->verifySignature($rawBody, $signature)) {
            return new WP_REST_Response(
                [
                    'message' => BackendStrings::getCommonStrings()['invalid_request_data'],
                    'data' => ['success' => false],
                ],
                403
            );
        }

        $decoded = json_decode($rawBody, true);
        $eventId = is_array($decoded)
            ? (string) ($decoded['event_id'] ?? $decoded['id'] ?? '')
            : '';
        if (!WebhookEventDedupeStore::claim('square', $eventId)) {
            return new WP_REST_Response(
                [
                    'message' => BackendStrings::getCommonStrings()['ok'],
                    'data' => ['success' => true],
                ],
                200
            );
        }

        $eventType = is_array($decoded) ? (string) ($decoded['type'] ?? '') : '';
        if ($eventType !== 'oauth.authorization.revoked') {
            return new WP_REST_Response(
                [
                    'message' => BackendStrings::getCommonStrings()['ok'],
                    'data' => ['success' => true, 'ignored' => true],
                ],
                200
            );
        }

        $this->squareOAuthService->clearSquareSettingsFromRevocationWebhook();
        $this->squareOAuthService->notifyAdminDisconnected();

        return new WP_REST_Response(
            [
                'message' => BackendStrings::getCommonStrings()['ok'],
                'data' => ['success' => true],
            ],
            200
        );
    }
}
