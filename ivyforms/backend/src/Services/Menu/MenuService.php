<?php

namespace IvyForms\Services\Menu;

use IvyForms\Routes\Routes;
use IvyForms\Services\Ai\AiAvailability;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Permissions\IvyFormsAccess;
use IvyForms\Services\Permissions\RoutePermissionService;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Renders menu pages
 */
class MenuService
{
    private ?RoutePermissionService $routePermissionService;

    public function __construct(?RoutePermissionService $routePermissionService = null)
    {
        $this->routePermissionService = $routePermissionService;
    }

    /**
     * Effective IvyForms permissions for the current user, keyed by permission key.
     *
     * Exposed to the frontend so the UI can gate actions (hide "+ Form", etc.) to match
     * the backend permission checks. Backend validation always remains the source of truth.
     *
     * @return array<string, bool>
     */
    private function currentUserPermissions(): array
    {
        $perm = $this->routePermissionService;
        if ($perm === null) {
            return [];
        }

        return [
            'view_forms_list'      => $perm->canViewForms(),
            'add_edit_forms'       => $perm->canEditForms(),
            'delete_forms'         => $perm->canDeleteForms(),
            'view_entries_admin'   => $perm->canViewEntries(),
            'delete_entries_admin' => $perm->canDeleteEntries(),
            'access_settings_page' => $perm->canAccessSettings(),
        ];
    }

    /**
     * @return array{available: bool, status: string, actionUrl: string, connectorsUrl: string}
     */
    private function currentAiAvailability(): array
    {
        $status = AiAvailability::getStatus();
        $actionUrl = '';

        if ($status === AiAvailability::STATUS_WORDPRESS_UPDATE_REQUIRED) {
            $actionUrl = AiAvailability::getWordPressUpdateUrl();
        } elseif ($status === AiAvailability::STATUS_AI_CLIENT_MISSING) {
            $actionUrl = AiAvailability::getAiPluginInstallUrl();
        } elseif ($status === AiAvailability::STATUS_TEXT_GENERATION_UNAVAILABLE) {
            $actionUrl = AiAvailability::getConnectorsSettingsUrl();
        }

        return [
            'available' => $status === AiAvailability::STATUS_AVAILABLE,
            'status' => $status,
            'actionUrl' => $actionUrl,
            'connectorsUrl' => AiAvailability::getConnectorsSettingsUrl(),
        ];
    }

    /**
     * Submenu page render function
     * @param string $page
     */
    public function render(string $page): void
    {
        $page = trim($page);
        $scriptId = IVYFORMS_DEV ? 'ivyforms_scripts_dev_vite' : 'ivyforms_script_index';
        $scriptSrc = IVYFORMS_DEV ?
                'http://localhost:5173/src/assets/js/admin/admin.ts' :
            IVYFORMS_URL . 'frontend/dist/admin.js';
        $styleSrc = IVYFORMS_DEV ? '' : IVYFORMS_URL . 'frontend/dist/index.css';
        $scriptVersion = IVYFORMS_DEV ? null : IVYFORMS_VERSION;

        wp_enqueue_script(
            $scriptId,
            $scriptSrc,
            [],
            $scriptVersion,
            true
        );

        // TODO: Check including fonts on build
        // @phpstan-ignore-next-line
        if ($styleSrc) {
            wp_enqueue_style(
                'ivyforms_style_admin',
                IVYFORMS_URL . 'frontend/dist/admin.css',
                [],
                $scriptVersion
            );
            wp_enqueue_style(
                'ivyforms_style_index',
                $styleSrc,
                [],
                $scriptVersion
            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_regular_woff2',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Regular.woff2',
//                [],
//                $scriptVersion
//            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_regular_woff',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Regular.woff',
//                [],
//                $scriptVersion
//            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_medium_woff2',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Medium.woff2',
//                [],
//                $scriptVersion
//            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_medium_woff',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Medium.woff',
//                [],
//                $scriptVersion
//            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_bold_woff2',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Bold.woff2',
//                [],
//                $scriptVersion
//            );
//            wp_enqueue_style(
//                'ivyforms_font_roboto_bold_woff',
//                IVYFORMS_URL . 'frontend/dist/Roboto-Bold.woff',
//                [],
//                $scriptVersion
//            );
        }

        $this->localizeScripts($scriptId);

        /**
         * Hook to enqueue additional scripts and styles in admin area
         * @since 0.1.0
         *
         * Arguments:
         *  - string $scriptId The main script handle ID (for dependencies)
         *  - string $page      WordPress admin menu slug (e.g. ivyforms-builder)
         */
        do_action('ivyforms/admin/enqueue_scripts', $scriptId, $page);

        if ($page === 'ivyforms-builder') {
            // FormBuilder.vue welcome-page image picker (Pro); not needed on other admin pages
            wp_enqueue_media();
        }

        include IVYFORMS_PATH . '/view/backend/view.php';
    }

    /**
     * Localize all admin data required by the frontend app.
     *
     * @param string $scriptId Main script handle used as the localization target.
     */
    private function localizeScripts(string $scriptId): void
    {
        $settingsStorage = new SettingsService();

        wp_localize_script(
            $scriptId,
            'wpIvySettings',
            $settingsStorage->getFrontendSettings()
        );

        $backendLabels = array_merge(
            BackendStrings::getNewFormStrings(),
            BackendStrings::getStylesFormBuilderStrings(),
            BackendStrings::getSettingsFormBuilderStrings(),
            BackendStrings::getWelcomePageStrings(),
            BackendStrings::getResultsFormBuilderStrings(),
            BackendStrings::getEmptyStatesStrings(),
            BackendStrings::getEntityFormStrings(),
            BackendStrings::getAllFormsStrings(),
            BackendStrings::getDashboardStrings(),
            BackendStrings::getCommonStrings(),
            BackendStrings::getComponentsStrings(),
            BackendStrings::getEntriesStrings(),
            BackendStrings::getIntegrationsStrings(),
            BackendStrings::getExceptionStrings(),
            BackendStrings::getTemplateStrings(),
            BackendStrings::getAiStrings(),
            BackendStrings::getSettingsStrings(),
            BackendStrings::getSecurityStrings(),
            BackendStrings::getPermissionsStrings(),
            BackendStrings::getProUpgradeDialogStrings(),
            BackendStrings::getChangelogStrings()
        );

        /**
         * Filter backend labels before localizing
         * Allows Pro version to merge additional strings
         *
         * @since 0.1.0
         * @param array $backendLabels Array of backend label strings
         */
        $backendLabels = apply_filters('ivyforms/admin/backend_labels', $backendLabels);

        wp_localize_script(
            $scriptId,
            'wpIvyLabels',
            $backendLabels
        );

        wp_localize_script(
            $scriptId,
            'wpIvyUrls',
            [
                'pluginURL' => IVYFORMS_URL,
                'siteURL'   => esc_url_raw(site_url()),
            ]
        );

        $aiAvailability = $this->currentAiAvailability();

        wp_localize_script(
            $scriptId,
            'wpIvyApiSettings',
            [
                'root'             => esc_url_raw(rest_url()),
                'nonce'            => wp_create_nonce('wp_rest'),
                'namespace'        => Routes::$routeNamespace,
                'isAdministrator'  => IvyFormsAccess::currentUserCanAccessPermissionsSettings(),
                'permissions'      => $this->currentUserPermissions(),
                'aiAvailable'      => $aiAvailability['available'],
                'aiStatus'         => $aiAvailability['status'],
                'aiActionUrl'      => $aiAvailability['actionUrl'],
                'aiConnectorsUrl'  => $aiAvailability['connectorsUrl'],
            ]
        );

        wp_localize_script(
            $scriptId,
            'wpIvyDateFormat',
            [
                'dateFormat'     => get_option('date_format'),
                'timeFormat'     => get_option('time_format'),
                'dateTimeFormat' => get_option('date_format') . ' ' . get_option('time_format'),
                'locale'         => get_locale(),
            ]
        );
    }
}
