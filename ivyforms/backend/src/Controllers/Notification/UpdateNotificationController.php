<?php

namespace IvyForms\Controllers\Notification;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Factory\Notification\NotificationFactory;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class UpdateNotificationController
 *
 * @package IvyForms\Controllers\Notification
 */
class UpdateNotificationController extends Controller
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));
        // Validate and sanitize input data
        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $params = Sanitizer::sanitizeNotificationData($data->get_params());

        $notification = NotificationFactory::create($params);

        $notificationId = $notification->getId();

        $this->notificationService->updateNotification($notificationId, $notification);

        if (!$notification->getId()) {
            throw new QueryExecutionException(
                BackendStrings::getSettingsFormBuilderStrings()['failed_to_update_notification'] . '.'
            );
        }

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => $notification->toArray()
        ], 200);
    }
}
