<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Template;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Template\TemplateService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class GetTemplatesController
 *
 * @package IvyForms\Controllers\Template
 */
class GetTemplatesController extends Controller
{
    private TemplateService $templateService;

    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * @param WP_REST_Request<array<string, mixed>> $request
     * @return WP_REST_Response
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $request): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($request->get_header('X-WP-Nonce'));

        $templates = $this->templateService->getAllTemplateMetas();
        $categories = $this->templateService->getTemplateCategories();

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'templates' => $templates,
                'categories' => $categories
            ]
        ], 200);
    }
}
