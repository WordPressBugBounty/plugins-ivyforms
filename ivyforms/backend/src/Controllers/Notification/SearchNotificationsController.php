<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Notification;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Notification\NotificationService;
use WP_REST_Request;
use WP_REST_Response;
use IvyForms\Services\Translations\BackendStrings;

class SearchNotificationsController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(
        NotificationService $notificationService
    ) {
        $this->notificationService = $notificationService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Sanitize the filtering data
        $params = Sanitizer::sanitizeSearchParams($data->get_query_params());

        // Call the searchNotifications method
        $notificationsArr = $this->notificationService->searchNotifications($params);

        // Map the data to an array format
        $notifications = array_map(fn($notification) => (array) $notification, $notificationsArr['data']);

        // Return the response with data and meta
        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data' => [
                'data' => $notifications,
                'meta' => $notificationsArr['meta'],
            ],
        ], 200);
    }
}
