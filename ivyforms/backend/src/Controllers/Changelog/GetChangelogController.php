<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Controllers\Changelog;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Controllers\Controller;
use IvyForms\Services\Changelog\ChangelogService;
use IvyForms\Services\Settings\SettingsService;
use WP_REST_Request;
use WP_REST_Response;
use IvyForms\Services\API\IvyFormsAPI;

/**
 * Class GetChangelogController
 *
 * @package IvyForms\Controllers\Changelog
 */
class GetChangelogController extends Controller
{
    private ChangelogService $changelogService;
    private SettingsService $settingsService;

    public function __construct(
        ChangelogService $changelogService,
        SettingsService $settingsService
    ) {
        $this->changelogService = $changelogService;
        $this->settingsService = $settingsService;
    }
    /**
     * Get changelog data with translated strings
     *
     * @param WP_REST_Request $data
     *
     * @return WP_REST_Response
     *
     * @throws ForbiddenException
     */
    public function handle(WP_REST_Request $data): WP_REST_Response
    {
        // Verify the nonce
        Sanitizer::verifyNonce($data->get_header('X-WP-Nonce'));

        $changelogData = $this->changelogService->getChangelogData();
        /** @var array<string,mixed> $changelogData */

        // Get stored versions
        $oldLiteVersion = $this->settingsService->getSetting('general', 'changelog_lite_version');
        $oldProVersion = $this->settingsService->getSetting('general', 'changelog_pro_version');

        // Check if Pro plugin is active
        // Use IvyFormsAPI::isProPluginActive() instead of relying on a settings flag
        // which may not be initialized or reliable.
        $isProActive = IvyFormsAPI::isProPluginActive();

        // Extract the decision logic to a helper and avoid deep nesting/else clauses
        $flags = $this->determineChangelogFlags($changelogData, $oldLiteVersion, $oldProVersion, $isProActive);
        $shouldShow = $flags['shouldShow'];
        $showLiteChangelog = $flags['showLiteChangelog'];
        $showProChangelog = $flags['showProChangelog'];

        // Update stored versions if showing changelog
        if ($shouldShow) {
            $this->settingsService->setSetting('general', 'changelog_lite_version', IVYFORMS_VERSION);
            if ($isProActive && !empty($changelogData['pro_version'])) {
                $this->settingsService->setSetting('general', 'changelog_pro_version', $changelogData['pro_version']);
            }
        }

        $responseData = [
            'oldLiteVersion'   => $oldLiteVersion,
            'oldProVersion'    => $oldProVersion,
            'version'      => IVYFORMS_VERSION,
            'release_date' => $changelogData['release_date'],
            'features'     => $showLiteChangelog ? $changelogData['features'] : [],
            'improvements' => $showLiteChangelog ? $changelogData['improvements'] : [],
            'bugfixes'     => $showLiteChangelog ? $changelogData['bugfixes'] : [],
            'shouldShow'   => $shouldShow,
            'showProChangelog' => $showProChangelog,
            'isProActive'  => $isProActive,
        ];

        /**
         * Filter changelog response data to allow Pro plugin to modify version, release date, etc.
         *
         * @param array $responseData The response data array
         * @param array $changelogData The full changelog data from service
         * @param SettingsService $settingsService The settings service instance
         * @return array Modified response data
         */
        $responseData = apply_filters(
            'ivyforms/changelog/response_data',
            $responseData,
            $changelogData,
            $this->settingsService
        );

        return new WP_REST_Response([
            'message' => __('Changelog retrieved successfully', 'ivyforms'),
            'data' => $responseData
        ], 200);
    }

    /**
     * Determine which changelog(s) should be shown.
     * Extracted to reduce complexity and improve testability.
     *
     * @param array<string,mixed> $changelogData
     * @param string|null $oldLiteVersion
     * @param string|null $oldProVersion
     * @param bool $isProActive
     * @return array{shouldShow: bool, showLiteChangelog: bool, showProChangelog: bool}
     */
    private function determineChangelogFlags(
        array $changelogData,
        ?string $oldLiteVersion,
        ?string $oldProVersion,
        bool $isProActive
    ): array {
        $shouldShow = false;
        $showLiteChangelog = false;
        $showProChangelog = false;

        $liteUpdated = $oldLiteVersion !== IVYFORMS_VERSION;

        $proUpdated = false;
        // Use defensive checks for optional pro_version key that may be added by Pro plugin
        if ($isProActive && isset($changelogData['pro_version']) && $changelogData['pro_version'] !== '') {
            $proUpdated = $oldProVersion !== (string) $changelogData['pro_version'];
        }

        if (!$isProActive) {
            // Only lite is active
            if ($liteUpdated) {
                $shouldShow = true;
                $showLiteChangelog = true;
            }

            return [
                'shouldShow' => $shouldShow,
                'showLiteChangelog' => $showLiteChangelog,
                'showProChangelog' => $showProChangelog,
            ];
        }

        // Both lite and pro are active
        if ($proUpdated) {
            // Pro was updated (or both were updated) - show merged changelog
            $shouldShow = true;
            $showLiteChangelog = true;
            $showProChangelog = true;

            return [
                'shouldShow' => $shouldShow,
                'showLiteChangelog' => $showLiteChangelog,
                'showProChangelog' => $showProChangelog,
            ];
        }

        if ($liteUpdated) {
             // Only lite was updated
             $shouldShow = true;
             $showLiteChangelog = true;
        }

         return [
             'shouldShow' => $shouldShow,
             'showLiteChangelog' => $showLiteChangelog,
             'showProChangelog' => $showProChangelog,
         ];
    }
}
