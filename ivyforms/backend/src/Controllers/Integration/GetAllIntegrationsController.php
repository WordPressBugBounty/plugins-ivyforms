<?php

namespace IvyForms\Controllers\Integration;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Integrations\IntegrationRegistry;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Get All Integrations Controller
 *
 * Returns all registered integrations from the registry
 *
 * @since 1.0.0
 */
class GetAllIntegrationsController extends Controller
{
    private IntegrationRegistry $registry;

    public function __construct(IntegrationRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Handle the request
     *
     * @param WP_REST_Request<array<string, mixed>> $data
     * @return WP_REST_Response
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $plan = Sanitizer::sanitizeText($data->get_param('plan'));

        $integrations = $plan
            ? $this->registry->getByPlan($plan)
            : $this->registry->getAll();

        return new WP_REST_Response([
            'success' => true,
            'data' => $integrations,
        ], 200);
    }
}
