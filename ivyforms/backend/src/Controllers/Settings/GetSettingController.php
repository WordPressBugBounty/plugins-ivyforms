<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Settings;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\Services\Usage\UsageTrackingService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Class GetSettingController
 *
 * @package IvyForms\Controllers\Settings
 */
class GetSettingController extends Controller
{
    private SettingsService $settingsService;
    private UsageTrackingService $usageTrackingService;

    public function __construct(
        SettingsService $settingsService,
        UsageTrackingService $usageTrackingService
    ) {
        $this->settingsService = $settingsService;
        $this->usageTrackingService = $usageTrackingService;
    }

    /**
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws InvalidArgumentException
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        // Get URL parameters (category and option)
        $settingsCategory = Sanitizer::sanitizeText($data->get_url_params()['category']);
        $settingsOption   = Sanitizer::sanitizeText($data->get_url_params()['option']);

        if (empty($settingsCategory) || empty($settingsOption)) {
            throw new InvalidArgumentException(
                BackendStrings::getSettingsStrings()['settings_category_or_option_missing']
            );
        }

        $settingValue = $settingsOption === 'usageTrackingEnabled' ?
            $this->usageTrackingService->isSettingEnabled() :
            $this->settingsService->getSetting($settingsCategory, $settingsOption);

        return new WP_REST_Response([
            'message' => BackendStrings::getSettingsStrings()['setting_retrieved'],
            'data'    => [
                'value' => $settingValue,
            ]
        ], 200);
    }
}
