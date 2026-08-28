<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Form;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Field\FieldDuplicateValidationService;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Validates no-duplicates field values before form submit or page navigation.
 */
class ValidateBeforeSubmitController extends Controller
{
    private FormService $formService;
    private FieldService $fieldService;
    private FieldDuplicateValidationService $fieldDuplicateValidationService;

    public function __construct(
        FormService $formService,
        FieldService $fieldService,
        FieldDuplicateValidationService $fieldDuplicateValidationService
    ) {
        $this->formService = $formService;
        $this->fieldService = $fieldService;
        $this->fieldDuplicateValidationService = $fieldDuplicateValidationService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException|NotFoundException
     */
    protected function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $formId = Sanitizer::sanitizeId($data->get_param('formId'));
        Sanitizer::verifySubmissionNonce($data->get_param('nonce'), $formId);

        if ($formId <= 0) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_form_id']
            );
        }

        $form = $this->formService->getFormById($formId);
        $formFields = $this->fieldService->getAllFields($form->getId());

        $pageId = $data->get_param('pageId');
        $pageId = is_string($pageId) && $pageId !== '' ? sanitize_text_field($pageId) : null;

        $values = $data->get_param('values');
        if (!is_array($values)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $submissionData = Sanitizer::sanitizeFormSubmissionData(['values' => $values], $formFields);

        [$isDuplicate, $duplicateErrors] = $this->fieldDuplicateValidationService->checkDuplicateFieldValues(
            $formFields,
            $submissionData,
            $formId,
            $pageId
        );

        $payload = [
            'valid'            => !$isDuplicate,
            'is_duplicate'     => $isDuplicate,
            'duplicate_errors' => $duplicateErrors,
        ];

        /**
         * Filter validate-before-submit API result (integrations e.g. Amelia).
         *
         * @param array{valid: bool, is_duplicate: bool, duplicate_errors: array<int|string, bool>} $payload
         * @param int $formId
         * @param array<string, mixed> $submissionData
         * @param \IvyForms\Entity\Field\Field[] $formFields
         * @param string|null $pageId
         */
        $payload = apply_filters(
            'ivyforms/validate_before_submit_result',
            $payload,
            $formId,
            $submissionData,
            $formFields,
            $pageId
        );

        return new WP_REST_Response([
            'data' => $payload,
        ], 200);
    }
}
