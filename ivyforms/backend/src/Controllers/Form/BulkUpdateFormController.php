<?php

namespace IvyForms\Controllers\Form;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class BulkUpdateFormController
 *
 * @package IvyForms\Controllers\Form
 */
class BulkUpdateFormController extends Controller
{
    private FormService $formService;

    public function __construct(FormService $formService)
    {
        $this->formService = $formService;
    }

    /**
     * Handle bulk update request for forms
     *
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Check if the entry data is provided
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }
        // Sanitize request data
        $sanitized = Sanitizer::sanitizeBulkUpdateData([
            'column' => $data->get_param('column'),
            'value' => $data->get_param('value'),
            'ids' => $data->get_param('ids'),
        ]);

        // Validate IDs
        if (empty($sanitized['ids'])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => BackendStrings::getExceptionStrings()['invalid_or_missing_ids'],
            ], 400);
        }

        // Validate column
        if (empty($sanitized['column'])) {
            return new WP_REST_Response([
                'success' => false,
                'message' => BackendStrings::getExceptionStrings()['table_or_attribute_not_allowed'],
            ], 400);
        }

        $this->formService->bulkUpdateColumn(
            $sanitized['column'],
            $sanitized['value'],
            $sanitized['ids']
        );

        return new WP_REST_Response([
            'success' => true,
            'message' => BackendStrings::getExceptionStrings()['batch_update_successful'],
        ], 200);
    }
}
