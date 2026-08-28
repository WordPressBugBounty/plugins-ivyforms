<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ServiceUnavailableException;
use IvyForms\Common\Exceptions\FieldValidationException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Services\Translations\BackendStrings;
use RuntimeException;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class Controller
 *
 * @package IvyForms\Controllers
 */
abstract class Controller
{
    /**
     * Handles the request and returns a structured array response.
     *
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     */
    public function __invoke(WP_REST_Request $data): WP_REST_Response
    {
        try {
            $response = $this->handle($data);

            if ($response->get_status() !== 200) {
                throw new RuntimeException(
                    BackendStrings::getExceptionStrings()['unexpected_http_status'] . ": {$response->get_status()}"
                );
            }

            return new WP_REST_Response([
                'message' => BackendStrings::getCommonStrings()['ok'],
                'data'    => $response->get_data(),
            ], 200);
        } catch (ForbiddenException $e) {
            // Log errors
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Error 403: ' . $e->getMessage());
            }
            // Handle forbidden errors
            // HTTP 403 FORBIDDEN
            return new WP_REST_Response([
                'message' => $e->getMessage(),
                'data'    =>  []
            ], 403);
        } catch (NotFoundException $e) {
            // Log errors
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Error 404: ' . $e->getMessage());
            }
            // Handle not found errors
            // HTTP 404 NOT FOUND
            return new WP_REST_Response([
                'message' => $e->getMessage(),
                'data'    =>  []
            ], 404);
        } catch (ServiceUnavailableException $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Error 503: ' . $e->getMessage());
            }

            return new WP_REST_Response([
                'message' => $e->getMessage(),
                'data'    => []
            ], 503);
        } catch (InvalidArgumentException $e) {
            // Log errors
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Error: ' . $e->getMessage());
            }
            // Handle validation or invalid arguments
            // HTTP 400 Bad Request
            return new WP_REST_Response([
                'message' => $e->getMessage(),
                'data'    =>  []
            ], 400);
        } catch (ValidationException $e) {
            // Log errors
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Validation error: ' . $e->getMessage());
            }
            // Handle validation for data
            // HTTP 422 Unprocessable Content
            $data = [];
            $fieldErrors = $e->getFieldErrors();
            if ($fieldErrors !== []) {
                if ($e instanceof FieldValidationException) {
                    $data['field_errors'] = $fieldErrors;
                } else {
                    $data['fieldErrors'] = $fieldErrors;
                }
            }

            return new WP_REST_Response([
                'message' => $e->getMessage(),
                'data'    => $data,
            ], 422);
        } catch (QueryExecutionException $e) {
            // Log errors
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Database error: ' . $e->getMessage());
            }
            // Handle validation for database errors
            // HTTP 500 Internal Server Error
            return new WP_REST_Response([
                'message' => 'Database error: ' . $e->getMessage(),
                'data'    =>  []
            ], 500);
        } catch (\Exception $e) {
            // Log errors
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Unexpected error occurred: ' . $e->getMessage());
            }
            // General error handling
            // HTTP 500 Internal Server Error
            return new WP_REST_Response([
                'message' => 'Unexpected error occurred: ' . $e->getMessage(),
                'data'    =>  []
            ], 500);
        }
    }

    /**
     * Abstract method to handle the WP_REST_Request.
     *
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     */
    abstract protected function handle(WP_REST_Request $data): WP_REST_Response;
}
