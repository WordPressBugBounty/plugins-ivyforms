<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Form;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Permissions\AdminFormListPermissionService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class GetFormsController
 *
 * @package IvyForms\Controllers\Form
 */
class GetFormsController extends Controller
{
    private FormService $formService;

    private AdminFormListPermissionService $adminFormListPermissionService;

    public function __construct(
        FormService $formService,
        AdminFormListPermissionService $adminFormListPermissionService
    ) {
        $this->formService                     = $formService;
        $this->adminFormListPermissionService = $adminFormListPermissionService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws ForbiddenException
     * @throws InvalidArgumentException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $formsArr = $this->formService->getAllForms() ?? [];

        $forms = [];

        foreach ($formsArr as $form) {
            if (!$this->adminFormListPermissionService->canAccessFormInAdminLists((int) $form->getId())) {
                continue;
            }
            $forms[] = $form->toArray();
        }

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            $forms
        ], 200);
    }
}
