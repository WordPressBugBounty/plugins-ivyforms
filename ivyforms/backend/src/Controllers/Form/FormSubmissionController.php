<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Form;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Infrastructure\WP\REST\SubmissionMultipartReader;
use IvyForms\Services\Submission\FormSubmissionService;
use IvyForms\Services\Submission\SubmissionPayloadParser;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class FormSubmissionController
 *
 * @package IvyForms\Controllers\Form
 */
class FormSubmissionController extends Controller
{
    private FormSubmissionService $formSubmissionService;

    public function __construct(FormSubmissionService $formSubmissionService)
    {
        $this->formSubmissionService = $formSubmissionService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException|NotFoundException|ForbiddenException
     * @throws ValidationException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $params = SubmissionPayloadParser::parseRequestPayload($data);
        $formId = Sanitizer::sanitizeId((int) ($params['formId'] ?? 0));
        Sanitizer::verifySubmissionNonce(isset($params['nonce']) ? (string) $params['nonce'] : null, $formId);

        $uploadedFiles = SubmissionMultipartReader::resolveUploadedFiles($data);
        $result = $this->formSubmissionService->processSubmission($params, $uploadedFiles);

        return new WP_REST_Response([
            'data' => $result,
        ], 200);
    }
}
