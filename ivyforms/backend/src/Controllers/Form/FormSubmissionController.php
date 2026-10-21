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
use IvyForms\Services\Translations\BackendStrings;
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

        if (SubmissionPayloadParser::isDiscardedOversizedBody($data)) {
            $exception = new ValidationException(
                BackendStrings::getExceptionStrings()['file_upload_failed']
            );
            $exception->setErrorCode('file_upload_failed');
            throw $exception;
        }

        $params = SubmissionPayloadParser::parseRequestPayload($data);
        $formId = Sanitizer::sanitizeId((int) ($params['formId'] ?? 0));
        Sanitizer::verifySubmissionNonce(isset($params['nonce']) ? (string) $params['nonce'] : null, $formId);

        $uploadedFiles = SubmissionMultipartReader::resolveUploadedFiles($data);
        $result = $this->formSubmissionService->processSubmission($params, $uploadedFiles);

        if (!empty($result['requires_action'])) {
            return new WP_REST_Response([
                'message' => BackendStrings::getCommonStrings()['ok'],
                'data' => [
                    'success' => false,
                    'requires_action' => true,
                    'client_secret' => $result['client_secret'] ?? null,
                    'payment_intent_id' => $result['payment_intent_id'] ?? null,
                    'payment_method' => $result['payment_method'] ?? 'stripe',
                ],
            ], 200);
        }

        if (!empty($result['payment_error'])) {
            return new WP_REST_Response([
                'message' => (string) (
                    $result['message'] ?? BackendStrings::getPaymentStrings()['payment_failed']
                ),
                'data' => [
                    'success' => false,
                    'payment_error' => true,
                ],
            ], 200);
        }

        return new WP_REST_Response([
            'data' => $result,
        ], 200);
    }
}
