<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Security\HCaptcha;

use IvyForms\Services\Security\Common\CaptchaServiceInterface;

/**
 * Provides hCaptcha frontend configuration
 *
 * @package IvyForms\Services\Security\HCaptcha
 */
class HCaptchaConfigProvider
{
    /**
     * Get hCaptcha configuration for frontend
     *
     * @param CaptchaServiceInterface|null $service
     * @return array<string, mixed>
     */
    public function getConfig(?CaptchaServiceInterface $service): array
    {
        $config = [
            'configured' => false,
            'type' => 'checkbox',
            'siteKey' => '',
            'scriptUrl' => '',
            'size' => 'normal'
        ];

        if ($service && $service->isConfigured()) {
            $hcaptchaConfig = $service->getFrontendConfig(
                $service->getType(),
                $service->getSiteKey()
            );
            $hcaptchaConfig['configured'] = true;
            return $hcaptchaConfig;
        }

        return $config;
    }
}
