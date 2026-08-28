<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Form;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class UpdateFormController
 */
class UpdateFormController extends Controller
{
    private FormService $formService;
    private FieldService $fieldService;

    public function __construct(
        FormService $formService,
        FieldService $fieldService
    ) {
        $this->formService = $formService;
        $this->fieldService = $fieldService;
    }

    /**
     * @param WP_REST_Request<array<string,mixed>> $data
     * @return WP_REST_Response
     * @throws QueryExecutionException
     * @throws InvalidArgumentException|ValidationException|ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $formId = (int) $data->get_param('id');

        $params = Sanitizer::sanitizeFormData(
            $this->formService->withPreservedFormType($data->get_params(), $formId)
        );

        $this->fieldService->validateFieldsType($params['fields']);

        [$formId, $form] = $this->formService->updateFormOrFail($params);
        if (!$formId) {
            throw new QueryExecutionException(BackendStrings::getAllFormsStrings()['failed_to_update_form'] . '.');
        }

        $existingFieldIds = $this->fieldService->collectExistingFieldIds($formId);
        $submittedFieldIds = [];

        if (!empty($params['fields'])) {
            $submittedFieldIds = $this->fieldService->updateFieldsWithOptions(
                $params['fields'],
                $formId
            );
        }

        if (!empty($existingFieldIds)) {
            $idsToDelete = array_diff($existingFieldIds, $submittedFieldIds);
            if (!empty($idsToDelete)) {
                $this->fieldService->deleteFields($idsToDelete);
            }
        }

        /**
         * After a form row (and fields) are persisted.
         *
         * @param \IvyForms\Entity\Form\Form $form
         * @param int $formId
         * @param string $context create|update
         */
        do_action('ivyforms/form/after_persist', $form, $formId, 'update');

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data' => $form->toArray()
        ], 200);
    }
}
