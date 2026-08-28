<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Deactivation;

use IvyForms\Controllers\Controller;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class SubmitDeactivationFeedbackController
 *
 * @package IvyForms\Controllers\Deactivation
 */
class SubmitDeactivationFeedbackController extends Controller
{
    /**
     * External API URL for storing feedback
     * Change this to your actual server API endpoint
     */
    private static string $feedbackApiUrl =
        'https://wpreportbuilder.com/wp-json/wpreportbuilder/v1/ivyforms-submissions';

    public function __construct()
    {
        // No need for wpdb in this version
    }

    /**
     * Handle deactivation feedback submission
     *
     * @param WP_REST_Request<array<string, mixed>> $request The REST request containing feedback data.
     * *
     * * @return WP_REST_Response The REST response with the result of the submission.
 */
    public function handle(WP_REST_Request $request): WP_REST_Response
    {
        // Sanitize and validate data
        $apiVersion = sanitize_text_field($request->get_param('api_version') ?? '');
        $feedbackKey = sanitize_text_field($request->get_param('feedback_key') ?? '');
        $feedback = sanitize_textarea_field($request->get_param('feedback') ?? '');

        // Validate that at least feedbackKey is provided
        if (empty($feedbackKey)) {
            return new WP_REST_Response(
                ['error' => 'Missing required feedback_key parameter'],
                400
            );
        }

        // Prepare data to send to external API
        $feedbackData = [
            'api_version' => $apiVersion,
            'feedback_key' => $feedbackKey,
            'feedback' => $feedback,
        ];

        // Send feedback to external API server
        $response = wp_remote_post(self::$feedbackApiUrl, [
            'method' => 'POST',
            'timeout' => 10,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => wp_json_encode($feedbackData),
        ]);

        // Handle response errors
        if (is_wp_error($response)) {
            return new WP_REST_Response(
                [
                    'error' => 'Failed to send feedback to server',
                    'message' => $response->get_error_message(),
                ],
                500
            );
        }

        // Check HTTP response code
        $httpCode = wp_remote_retrieve_response_code($response);
        if ($httpCode !== 200 && $httpCode !== 201) {
            $responseBody = wp_remote_retrieve_body($response);
            return new WP_REST_Response(
                [
                    'error' => 'Server returned an error',
                    'http_code' => $httpCode,
                    'server_response' => $responseBody,
                ],
                500
            );
        }

        // Success response
        return new WP_REST_Response(
            [
                'success' => true,
                'message' => 'Feedback sent to server successfully',
            ],
            200
        );
    }
}
