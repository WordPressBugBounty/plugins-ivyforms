<?php

namespace IvyForms\Controllers\Notification;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class GetNotificationsController
 *
 * @package IvyForms\Controllers\Notification
 */
class GetNotificationsController extends Controller
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
     * @throws ForbiddenException
     * @throws InvalidArgumentException|NotFoundException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));
        $formId = Sanitizer::sanitizeId($data->get_param('id'));
        // TODO: Update this logic to only require the form ID after templates.
        $notificationsArr = $formId
            ? $this->notificationService->getNotificationsByFormId((int) $formId)
            : $this->notificationService->getAllNotifications();

        $notifications = [];

        foreach ($notificationsArr as $notification) {
            $notifications[] = $notification->toArray();
        }

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => $notifications,
        ], 200);
    }
}
