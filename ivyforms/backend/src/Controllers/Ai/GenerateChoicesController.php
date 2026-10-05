<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Controllers\Ai;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\ServiceUnavailableException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Ai\AiChoiceGenerationService;
use IvyForms\Services\Translations\BackendStrings;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles AI choice generation from a prompt and prior dialog exchanges.
 */
class GenerateChoicesController extends Controller
{
    private AiChoiceGenerationService $aiChoiceGenerationService;

    public function __construct(AiChoiceGenerationService $aiChoiceGenerationService)
    {
        $this->aiChoiceGenerationService = $aiChoiceGenerationService;
    }

    /**
     * @param WP_REST_Request<array<string, mixed>> $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException
     * @throws ServiceUnavailableException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $prompt = sanitize_textarea_field((string) ($data->get_param('prompt') ?? ''));
        if ($prompt === '') {
            throw new InvalidArgumentException(
                BackendStrings::getAiStrings()['ai_choices_prompt_required']
            );
        }

        $fieldType = sanitize_key((string) ($data->get_param('fieldType') ?? ''));
        $conversationHistory = $this->sanitizeConversationHistory($data->get_param('history'));
        $choices = $this->aiChoiceGenerationService->generateChoices(
            $prompt,
            $fieldType,
            $conversationHistory
        );

        return new WP_REST_Response($choices, 200);
    }

    /**
     * @param mixed $history
     *
     * @return array<int, array<string, mixed>>
     */
    private function sanitizeConversationHistory($history): array
    {
        if (!is_array($history)) {
            return [];
        }

        $sanitizedHistory = [];
        foreach ($history as $turn) {
            if (!is_array($turn)) {
                continue;
            }

            $sanitizedChoices = [];
            $choices = $turn['choices'] ?? [];
            if (is_array($choices)) {
                foreach ($choices as $choice) {
                    if (!is_array($choice)) {
                        continue;
                    }

                    $sanitizedChoices[] = [
                        'label' => sanitize_text_field((string) ($choice['label'] ?? '')),
                        'value' => sanitize_text_field((string) ($choice['value'] ?? '')),
                    ];
                }
            }

            $sanitizedHistory[] = [
                'prompt' => sanitize_textarea_field((string) ($turn['prompt'] ?? '')),
                'title' => sanitize_text_field((string) ($turn['title'] ?? '')),
                'choices' => $sanitizedChoices,
            ];
        }

        return $sanitizedHistory;
    }
}
