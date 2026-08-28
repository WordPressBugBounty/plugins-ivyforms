<?php

namespace IvyForms\Controllers\Notification;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Controllers\Controller;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Permissions\PermissionResourceResolver;
use IvyForms\Services\Permissions\RoutePermissionService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class DeleteNotificationsController
 *
 * @package IvyForms\Controllers\Notification
 */
class DeleteNotificationsController extends Controller
{
    private NotificationService $notificationService;

    private RoutePermissionService $routePermissionService;

    private PermissionResourceResolver $permissionResourceResolver;

    public function __construct(
        NotificationService $notificationService,
        RoutePermissionService $routePermissionService,
        PermissionResourceResolver $permissionResourceResolver
    ) {
        $this->notificationService            = $notificationService;
        $this->routePermissionService         = $routePermissionService;
        $this->permissionResourceResolver     = $permissionResourceResolver;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));
        $notificationIds =  Sanitizer::sanitizeIds($data->get_param('ids'));

        if (empty($notificationIds)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['notification_ids_required']
            );
        }

        foreach ($notificationIds as $notificationId) {
            $formId = $this->permissionResourceResolver->formIdFromNotificationId((int) $notificationId);
            if ($formId === null || !$this->routePermissionService->canEditForms($formId)) {
                throw new ForbiddenException('forbidden');
            }
        }

        $this->notificationService->deleteNotifications($notificationIds);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => []
        ], 200);
    }
}
