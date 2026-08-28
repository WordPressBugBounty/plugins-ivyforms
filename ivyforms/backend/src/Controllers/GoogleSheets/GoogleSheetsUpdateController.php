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

class GoogleSheetsUpdateController extends Controller
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

        $id = Sanitizer::sanitizeId((int) $data->get_param('id'));
        $updateData = $this->sanitizer->buildUpdateData($data->get_params());

        if ($id <= 0) {
            throw new InvalidArgumentException('Integration ID is required');
        }

        $integration = $this->managerService->updateIntegration($id, $updateData);

        return new WP_REST_Response($integration->toArray(), 200);
    }
}
