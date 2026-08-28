<?php

namespace IvyForms\Controllers\GoogleSheets;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\GoogleSheets\GoogleSheetsManagerService;
use WP_REST_Request;
use WP_REST_Response;

class GoogleSheetsListIntegrationsController extends Controller
{
    private GoogleSheetsManagerService $managerService;

    public function __construct(GoogleSheetsManagerService $managerService)
    {
        $this->managerService = $managerService;
    }

    /**
     * @throws ForbiddenException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $formId = Sanitizer::sanitizeId((int) $data->get_param('form_id'));
        $integrations = $this->managerService->getIntegrationsByFormId($formId);

        return new WP_REST_Response([
            'integrations'     => array_map(static function ($integration) {
                return $integration->toArray();
            }, $integrations),
            'max_integrations' => $this->managerService->getMaxIntegrationsPerForm(),
        ], 200);
    }
}
