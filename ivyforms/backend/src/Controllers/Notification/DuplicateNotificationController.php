<?php

namespace IvyForms\Controllers\Notification;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Factory\Notification\NotificationFactory;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class DuplicateNotificationController
 *
 * @package IvyForms\Controllers\Notification
 */
class DuplicateNotificationController extends Controller
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
     * @throws NotFoundException
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));
        // Validate request data
        $notificationId = Sanitizer::sanitizeId($data->get_param('id'));
        if (empty($notificationId)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['notification_id_required']
            );
        }

        // Retrieve the original notification
        $originalNotification = $this->notificationService->getNotificationById($notificationId);

        // Duplicate the notification's parameters, excluding the ID
        $newNotificationData = $originalNotification->toArray();
        unset($newNotificationData['id']); // Exclude the ID
        $newNotificationData['name'] .= ' (Copy)'; // Append "(Copy)" to the name
        $newNotification = NotificationFactory::create($newNotificationData);

        // Save the new notification to the database
        $newNotificationId = $this->notificationService->createNotification($newNotification);
        if (!$newNotificationId) {
            throw new QueryExecutionException(
                BackendStrings::getSettingsFormBuilderStrings()['failed_to_duplicate_notification' . '.']
            );
        }

        // Set the new notification ID
        $newNotification->setId($newNotificationId);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => $newNotification->toArray()
        ], 200);
    }
}
