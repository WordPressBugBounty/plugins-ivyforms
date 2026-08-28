<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Template;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Template\TemplateService;
use WP_REST_Request;
use WP_REST_Response;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class GetTemplateController
 *
 * @package IvyForms\Controllers\Template
 */
class GetTemplateController extends Controller
{
    private TemplateService $templateService;

    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * @param WP_REST_Request<array<string, mixed>> $data
     * @return WP_REST_Response
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // TODO Add validation for template_id with Sanitizer
        //$templateId = Sanitizer::sanitizeText($data->get_param('id'));
        $templateId = sanitize_text_field($data->get_param('id') ?? '');

        if (empty($templateId)) {
            throw new InvalidArgumentException(
                BackendStrings::getTemplateStrings()['required_template_id']
            );
        }

        $template = $this->templateService->getTemplateById($templateId);

        if (!$template) {
            throw new InvalidArgumentException(
                BackendStrings::getTemplateStrings()['template_not_found']
            );
        }

        // Apply the same field layout rules used on form create (one field per row by default).
        $template['form_data'] = $this->templateService->getFormDataFromTemplate($templateId);

        return new WP_REST_Response([
            'success' => true,
            'data' => $template
        ], 200);
    }
}
