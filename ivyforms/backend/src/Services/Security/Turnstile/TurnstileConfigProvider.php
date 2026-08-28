<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Security\Turnstile;

use IvyForms\Services\Security\Common\CaptchaServiceInterface;

/**
 * Provides Turnstile frontend configuration
 *
 * @package IvyForms\Services\Security\Turnstile
 */
class TurnstileConfigProvider
{
    /**
     * Get Turnstile configuration for frontend
     *
     * @param CaptchaServiceInterface|null $service
     * @return array<string, mixed>
     */
    public function getConfig(?CaptchaServiceInterface $service): array
    {
        $config = [
            'configured' => false,
            'siteKey' => '',
            'scriptUrl' => '',
            'theme' => 'auto'
        ];

        if ($service && $service->isConfigured()) {
            $turnstileConfig = $service->getFrontendConfig(
                $service->getType(),
                $service->getSiteKey()
            );
            $turnstileConfig['configured'] = true;
            return $turnstileConfig;
        }

        return $config;
    }
}
