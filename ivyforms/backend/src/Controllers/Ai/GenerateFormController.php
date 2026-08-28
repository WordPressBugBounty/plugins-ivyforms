<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Controllers\Ai;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ServiceUnavailableException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Ai\AiFormGenerationService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles single-pass AI form generation from a prompt.
 */
class GenerateFormController extends Controller
{
    private AiFormGenerationService $aiFormGenerationService;

    public function __construct(AiFormGenerationService $aiFormGenerationService)
    {
        $this->aiFormGenerationService = $aiFormGenerationService;
    }

    /**
     * @param WP_REST_Request<array<string, mixed>> $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ServiceUnavailableException
     * @throws ValidationException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $prompt = sanitize_textarea_field((string) ($data->get_param('prompt') ?? ''));
        if ($prompt === '') {
            throw new InvalidArgumentException(
                BackendStrings::getAiStrings()['ai_prompt_required']
            );
        }

        $form = $this->aiFormGenerationService->generateAndCreateForm($prompt);

        return new WP_REST_Response($form, 200);
    }
}
