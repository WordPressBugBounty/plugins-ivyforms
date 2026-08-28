<?php

namespace IvyForms\Controllers\GoogleSheets;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\GoogleSheets\GoogleSheetsManagerService;
use WP_REST_Request;
use WP_REST_Response;

class GoogleSheetsDeleteController extends Controller
{
    private GoogleSheetsManagerService $managerService;

    public function __construct(GoogleSheetsManagerService $managerService)
    {
        $this->managerService = $managerService;
    }

    /**
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $id = Sanitizer::sanitizeId((int) $data->get_param('id'));

        if ($id <= 0) {
            throw new InvalidArgumentException('Integration ID is required');
        }

        $deleted = $this->managerService->deleteIntegration($id);

        return new WP_REST_Response(['success' => $deleted], 200);
    }
}
