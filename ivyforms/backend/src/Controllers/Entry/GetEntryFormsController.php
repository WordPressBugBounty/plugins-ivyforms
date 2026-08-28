<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Entry;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Permissions\AdminFormListPermissionService;
use IvyForms\Services\Permissions\IvyFormsAccess;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Returns the forms the current user may view entries for, used to populate the
 * entries-page form filter. Scoped strictly to entries permissions so it does not
 * leak the full All Forms list to entry-only delegates.
 *
 * @package IvyForms\Controllers\Entry
 */
class GetEntryFormsController extends Controller
{
    private FormService $formService;

    private AdminFormListPermissionService $adminFormListPermissionService;

    public function __construct(
        FormService $formService,
        AdminFormListPermissionService $adminFormListPermissionService
    ) {
        $this->formService                    = $formService;
        $this->adminFormListPermissionService = $adminFormListPermissionService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $allForms = $this->formService->getAllForms() ?? [];

        $forms = [];
        if (IvyFormsAccess::hasImplicitElevatedAccess()) {
            foreach ($allForms as $form) {
                $forms[] = ['id' => (int) $form->getId(), 'name' => (string) $form->getName()];
            }

            return $this->buildResponse($forms);
        }

        $allFormIds = array_map(static fn($form) => (int) $form->getId(), $allForms);
        $visibleIds = array_flip($this->adminFormListPermissionService->filterFormIdsVisibleInEntryLists($allFormIds));

        foreach ($allForms as $form) {
            if (!isset($visibleIds[(int) $form->getId()])) {
                continue;
            }
            $forms[] = ['id' => (int) $form->getId(), 'name' => (string) $form->getName()];
        }

        return $this->buildResponse($forms);
    }

    /**
     * @param list<array{id:int,name:string}> $forms
     */
    private function buildResponse(array $forms): WP_REST_Response
    {
        return new WP_REST_Response(['forms' => $forms], 200);
    }
}
