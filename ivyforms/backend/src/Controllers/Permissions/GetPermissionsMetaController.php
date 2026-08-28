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
use IvyForms\Repository\Permissions\AccessRulesRepository;
use IvyForms\Repository\Form\FormRepositoryInterface;
use IvyForms\Services\Permissions\PermissionCatalog;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

class GetPermissionsMetaController extends Controller
{
    private AccessRulesRepository $accessRulesRepository;
    private FormRepositoryInterface $formRepository;

    public function __construct(
        AccessRulesRepository $accessRulesRepository,
        FormRepositoryInterface $formRepository
    ) {
        $this->accessRulesRepository = $accessRulesRepository;
        $this->formRepository        = $formRepository;
    }

    /**
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $assignedRoleSlugs = $this->getAssignedRoleSlugs();
        $roles             = $this->buildRoles($assignedRoleSlugs);

        return new WP_REST_Response(
            [
                'message'                => BackendStrings::getCommonStrings()['ok'],
                'permission_definitions' => PermissionCatalog::definitions(),
                'dependency_map'         => PermissionCatalog::dependencyMap(),
                'roles'                  => $roles,
                'forms'                  => $this->formRepository->getIdNamePairs(),
            ],
            200
        );
    }

    /**
     * @return list<string>
     */
    private function getAssignedRoleSlugs(): array
    {
        $assignedRoleSlugs = [];
        $payload           = $this->accessRulesRepository->getPayload();

        foreach ($payload['rules'] as $rule) {
            if (($rule['type'] ?? '') !== 'role' || empty($rule['role_slug'])) {
                continue;
            }
            $assignedRoleSlugs[] = (string) $rule['role_slug'];
        }

        return array_values(array_unique($assignedRoleSlugs));
    }

    /**
     * @param list<string> $assignedRoleSlugs
     * @return list<array{slug:string,name:string,assigned:bool}>
     */
    private function buildRoles(array $assignedRoleSlugs): array
    {
        $wpRoles = wp_roles();
        $roles   = [];

        foreach ($wpRoles->roles as $slug => $info) {
            if ($slug === 'administrator') {
                continue;
            }
            $roles[] = [
                'slug'     => $slug,
                'name'     => translate_user_role($info['name'] ?? $slug),
                'assigned' => in_array($slug, $assignedRoleSlugs, true),
            ];
        }

        return $roles;
    }
}
