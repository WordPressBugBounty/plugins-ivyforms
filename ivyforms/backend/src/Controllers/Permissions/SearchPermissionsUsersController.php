<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 */

namespace IvyForms\Controllers\Permissions;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit;
}

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Permissions\IvyFormsAccess;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;
use WP_User;
use WP_User_Query;

class SearchPermissionsUsersController extends Controller
{
    private const USERS_RESULT_LIMIT = 50;

    private const MIN_SEARCH_LENGTH = 2;

    /**
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));
        $search = sanitize_text_field((string) $data->get_param('search'));
        $search = trim($search);

        $args = [
            'orderby'      => 'user_email',
            'order'        => 'ASC',
            'fields'       => ['ID', 'user_email'],
            'role__not_in' => ['administrator'],
            'count_total'  => false,
            'number'       => self::USERS_RESULT_LIMIT,
        ];

        if ($search !== '' && strlen($search) >= self::MIN_SEARCH_LENGTH) {
            $args['search']         = '*' . $search . '*';
            $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
        }

        $query = new WP_User_Query($args);
        $users = [];
        foreach ($query->get_results() as $row) {
            $userId = is_object($row) ? (int) ($row->ID ?? 0) : 0;
            if ($userId <= 0) {
                continue;
            }

            $user = get_userdata($userId);
            if (!$user instanceof WP_User) {
                continue;
            }

            // Exclude users who already have full / implicit IvyForms or site-admin access.
            if (IvyFormsAccess::userShouldBeExcludedFromPermissionsPicker($user)) {
                continue;
            }

            $email = trim((string) $user->user_email);
            if ($email === '') {
                continue;
            }

            $users[] = [
                'id'    => $userId,
                'email' => $email,
                'label' => $email,
            ];
        }

        return new WP_REST_Response(
            [
                'message' => BackendStrings::getCommonStrings()['ok'],
                'users'   => $users,
            ],
            200
        );
    }
}
