<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Usage;

use IvyForms\Vendor\Melograno\UsageTracker\Collectors\Plugin\IvyFormsCollector;
use IvyForms\Vendor\Melograno\UsageTracker\Core\UsageTracker;

/**
 * Class UsageTrackingService
 *
 * @package IvyForms\Services\Usage
 */
class UsageTrackingService
{
    private IvyFormsCollector $collector;

    public function __construct(IvyFormsCollector $collector)
    {
        $this->collector = $collector;
    }

    public function init(): void
    {
        UsageTracker::init($this->collector, IVYFORMS_FILE);
    }

    /**
     * @return bool
     */
    public function isSettingEnabled(): bool
    {
        return UsageTracker::getSettings($this->collector)['usageTrackingEnabled'];
    }

    /**
     * @param array{usageTrackingEnabled?: bool} $settings
     *
     * @return array{usageTrackingEnabled: bool}
     */
    public function updateSettings(array $settings): array
    {
        UsageTracker::updateSettings($settings, $this->collector);

        return $settings;
    }
}
