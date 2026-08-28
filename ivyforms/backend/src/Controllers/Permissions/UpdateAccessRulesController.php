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
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Repository\Permissions\AccessRulesRepository;
use IvyForms\Services\Permissions\AccessRulesRoleCapabilitySynchronizer;
use IvyForms\Services\Permissions\AccessRulesValidator;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class UpdateAccessRulesController extends Controller
{
    private AccessRulesRepository $accessRulesRepository;
    private AccessRulesValidator $accessRulesValidator;

    public function __construct(
        AccessRulesRepository $accessRulesRepository,
        AccessRulesValidator $accessRulesValidator
    ) {
        $this->accessRulesRepository = $accessRulesRepository;
        $this->accessRulesValidator  = $accessRulesValidator;
    }

    /**
     * @throws ForbiddenException
     * @throws ValidationException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $body = $data->get_json_params();
        if (!is_array($body) || !isset($body['rules']) || !is_array($body['rules'])) {
            throw new ValidationException(
                BackendStrings::getExceptionStrings()['rules_payload_required']
            );
        }

        $normalized = $this->accessRulesValidator->validateAndNormalize($body['rules']);
        $previousUserIds = AccessRulesRoleCapabilitySynchronizer::extractUserIdsFromUserRules(
            $this->accessRulesRepository->getPayload()['rules']
        );
        $this->accessRulesRepository->saveRules($normalized);
        AccessRulesRoleCapabilitySynchronizer::syncFromRules($normalized, $previousUserIds);

        return new WP_REST_Response(
            [
                'message' => BackendStrings::getPermissionsStrings()['permissions_saved'],
                'rules'   => $normalized,
            ],
            200
        );
    }
}
