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
use IvyForms\Services\Form\FormCreateService;
use IvyForms\Services\Template\TemplateService;
use WP_REST_Request;
use WP_REST_Response;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class AddFormController
 *
 * @package IvyForms\Controllers\Form
 */
class AddFormController extends Controller
{
    private FormCreateService $formCreateService;
    private TemplateService $templateService;

    public function __construct(
        FormCreateService $formCreateService,
        TemplateService $templateService
    ) {
        $this->formCreateService = $formCreateService;
        $this->templateService   = $templateService;
    }

    /**
     * @param WP_REST_Request<array<string, mixed>> $data
     *
     * @return WP_REST_Response
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws ValidationException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        if (empty($data->get_params())) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['invalid_request_data']
            );
        }

        $templateId = sanitize_text_field($data->get_param('template_id'));

        $mergedParams = $data->get_params();
        $formData = [];
        if (!empty($templateId)) {
            $formData = $this->templateService->getFormDataFromTemplate($templateId);
        }
        /**
         * Keys the client may override on create. When creating from a template, field
         * structure and layout always come from TemplateService — not the builder store.
         * Merging frontend fields caused duplicate name/address rows and single-row layout.
         *
         * @param list<string> $keys
         */
        $mergeableKeys = apply_filters('ivyforms/form/add/mergeable_keys', [
            'name',
            'description',
            'published',
            'showTitle',
            'showDescription',
            'storeEntries',
            'fields',
            'integrationSettings',
            'styleSettings',
            'formActionButtons',
            'formType',
        ]);

        $templateLockedKeys = [
            'fields',
            'integrationSettings',
            'styleSettings',
            'formActionButtons',
        ];

        foreach ($mergeableKeys as $key) {
            if (!array_key_exists($key, $mergedParams)) {
                continue;
            }

            if (!empty($templateId) && in_array($key, $templateLockedKeys, true)) {
                continue;
            }

            $formData[$key] = $mergedParams[$key];
        }

        $params = Sanitizer::sanitizeFormData($formData);
        $created = $this->formCreateService->createFromSanitizedParams($params);

        return new WP_REST_Response([
            'message' => BackendStrings::getCommonStrings()['ok'],
            'data'    => $created,
        ], 200);
    }
}
