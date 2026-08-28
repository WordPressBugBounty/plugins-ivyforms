<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Ai;

use IvyForms\Common\Exceptions\ServiceUnavailableException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Thin wrapper around the WordPress AI Client for text generation features.
 */
class AiTextClient
{
    /**
     * Generate a JSON text response using the configured AI provider.
     *
     * @param array<string, mixed> $schema
     *
     * @throws ServiceUnavailableException
     */
    public function generateJsonText(
        string $userPrompt,
        string $systemInstruction,
        array $schema,
        int $maxTokens = 4096
    ): string {
        $this->assertClientAvailable();

        // Omit using_provider() so WordPress AI Client picks any configured provider.
        $result = wp_ai_client_prompt($userPrompt)
            ->using_system_instruction($systemInstruction)
            ->using_max_tokens($maxTokens)
            ->as_json_response($schema)
            ->generate_text();

        return $this->extractGeneratedText($result);
    }

    /**
     * Generate plain text using the configured AI provider.
     *
     * @throws ServiceUnavailableException
     */
    public function generateText(
        string $userPrompt,
        string $systemInstruction = '',
        int $maxTokens = 1024
    ): string {
        $this->assertClientAvailable();

        $builder = wp_ai_client_prompt($userPrompt)->using_max_tokens($maxTokens);
        if ($systemInstruction !== '') {
            $builder = $builder->using_system_instruction($systemInstruction);
        }

        return $this->extractGeneratedText($builder->generate_text());
    }

    /**
     * @throws ServiceUnavailableException
     */
    private function assertClientAvailable(): void
    {
        if (function_exists('wp_ai_client_prompt')) {
            return;
        }

        throw new ServiceUnavailableException(
            BackendStrings::getAiStrings()['ai_not_available']
        );
    }

    /**
     * @param mixed $result
     *
     * @throws ServiceUnavailableException
     */
    private function extractGeneratedText($result): string
    {
        if (is_wp_error($result)) {
            $this->logProviderFailure($result->get_error_message());

            throw new ServiceUnavailableException(
                BackendStrings::getAiStrings()['ai_generation_failed']
            );
        }

        $text = is_string($result) ? trim($result) : '';
        if ($text === '') {
            $this->logProviderFailure('Empty or non-string generate_text() result');

            throw new ServiceUnavailableException(
                BackendStrings::getAiStrings()['ai_generation_failed']
            );
        }

        return $text;
    }

    private function logProviderFailure(string $detail): void
    {
        error_log('IvyForms AI provider error: ' . $detail);
    }
}
