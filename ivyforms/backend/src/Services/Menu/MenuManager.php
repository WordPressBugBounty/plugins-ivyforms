<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Menu;

use Closure;
use IvyForms\Services\Permissions\IvyFormsAccess;

class MenuManager
{
    private MenuMapper $menu;
    private MenuService $menuService;

    public function __construct(
        MenuService $menuService,
        MenuMapper $menu
    ) {
        $this->menuService = $menuService; // Dependency injected by PHP-DI
        $this->menu = $menu;
    }

    public function registerMenus(): void
    {
        // Hook into WordPress 'admin_menu' action
        add_action('admin_menu', [$this, 'addOptionsPages']);

        // Add admin CSS for upgrade menu styling
        add_action('admin_enqueue_scripts', [$this, 'addUpgradeMenuStyles']);
    }

    public function addOptionsPages(): void
    {
        add_menu_page(
            'IvyForms',
            'IvyForms',
            IvyFormsAccess::CAP_ACCESS_PLUGIN,
            'ivyforms',
            null, // @phpstan-ignore argument.type
            IVYFORMS_URL . 'frontend/src/assets/images/logos/logo-only-admin.svg'
        );

        foreach (($this->menu)() as $menu) {
            $this->handleMenuItem($menu);
        }

        remove_submenu_page('ivyforms', 'ivyforms');

        // Add JavaScript to make upgrade menu open in new tab
        add_action('admin_footer', [$this, 'addUpgradeMenuScript']);
    }

    /**
     *
     * @param mixed[] $menu
     * */
    public function handleMenuItem(array $menu): void
    {
        // For external links (like upgrade menu), just add without callback
        if (isset($menu['isExternal']) && $menu['isExternal']) {
            add_submenu_page(
                $menu['parentSlug'],
                $menu['pageTitle'],
                $menu['menuTitle'],
                $menu['capability'],
                $menu['menuSlug'],
                ''
            );
            return;
        }

        $this->addSubmenuPage(
            $menu['parentSlug'],
            $menu['pageTitle'],
            $menu['menuTitle'],
            $menu['capability'],
            $menu['menuSlug'],
            function () use ($menu) {
                $this->menuService->render($menu['menuSlug']);
            }
        );
    }

    private function addSubmenuPage(
        string $parentSlug,
        string $pageTitle,
        string $menuTitle,
        string $capability,
        string $menuSlug,
        Closure $function
    ): void {
        add_submenu_page(
            $parentSlug,
            $pageTitle,
            $menuTitle,
            $capability,
            $menuSlug,
            $function
        );
    }

    /**
     * Add CSS styling for upgrade menu
     */
    public function addUpgradeMenuStyles(): void
    {
        // Register an empty / dummy stylesheet (NO file needed)
        wp_register_style('ivyforms-admin-menu', false);
        wp_enqueue_style('ivyforms-admin-menu');

        $customCss = '
            #adminmenu .ivyforms-upgrade-to-pro {
                display: flex;
                align-items: flex-start;
                font-weight: 600 !important;
                color: #ffffff !important;
            }
            #adminmenu .ivyforms-upgrade-to-pro svg {
                width: 18px;
                height: 16px;
                flex-shrink: 0;
            }
            #adminmenu li a:has(.ivyforms-upgrade-to-pro) {
                background-color: #F69D0B !important;
            }
            #adminmenu li a:has(.ivyforms-upgrade-to-pro):hover {
                background-color: #E08D00 !important;
            }
        ';
        wp_add_inline_style('ivyforms-admin-menu', $customCss);
    }

    /**
     * Add JavaScript to make upgrade menu link open in new tab
     */
    public function addUpgradeMenuScript(): void
    {
        ?>
        <script>
        (function() {
            const upgradeLink = document.querySelector('#adminmenu a[href*="ivyforms.com/pricing"]');
            if (upgradeLink) {
                upgradeLink.setAttribute('target', '_blank');
                upgradeLink.setAttribute('rel', 'noopener nofollow noreferrer');
            }
        })();
        </script>
        <?php
    }
}
