<?php

namespace IvyForms\Controllers\Integration;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Integrations\IntegrationRegistry;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Get Single Integration Controller
 *
 * Returns a specific integration by slug
 *
 * @since 1.0.0
 */
class GetIntegrationController extends Controller
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

        $slug = Sanitizer::sanitizeText($data->get_param('slug'));

        if (empty($slug)) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('Integration slug is required', 'ivyforms'),
            ], 400);
        }

        $integration = $this->registry->get($slug);

        if (!$integration) {
            return new WP_REST_Response([
                'success' => false,
                'message' => sprintf(__('Integration "%s" not found', 'ivyforms'), $slug),
            ], 404);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $integration,
        ], 200);
    }
}
