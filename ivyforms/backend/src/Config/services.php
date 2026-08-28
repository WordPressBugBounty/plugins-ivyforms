<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Services\Menu\MenuManager;
use IvyForms\Services\Menu\MenuMapper;
use IvyForms\Services\Menu\MenuService;
use IvyForms\Services\Integrations\PluginInstaller;
use IvyForms\Services\Integrations\IntegrationRegistry;
use IvyForms\Services\Integrations\IntegrationService;
use IvyForms\Services\Permissions\PermissionResolver;
use IvyForms\Services\Permissions\RoutePermissionService;
use IvyForms\Controllers\Integrations\InstallPluginController;
use IvyForms\Controllers\Integration\GetAllIntegrationsController;
use IvyForms\Controllers\Integration\GetIntegrationController;
use IvyForms\Controllers\Form\ExportFormController;
use IvyForms\Controllers\Form\ImportFormController;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Form\FormImportExportService;
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\Field\FieldDuplicateValidationService;
use IvyForms\Services\Field\FieldService;
use IvyForms\Services\Mailer\MailerService;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Security\SecurityService;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Submission\FormSubmissionService;
use IvyForms\Services\Submission\SubmissionDataFormatterService;
use IvyForms\Services\Submission\SubmissionUploadService;
use IvyForms\Services\GoogleSheets\Helpers\GoogleSheetsRequestSanitizer;
use IvyForms\Services\GoogleSheets\Hooks\GoogleSheetsEntryHooks;
use IvyForms\Services\GoogleSheets\Hooks\GoogleSheetsFormHooks;
use IvyForms\Services\GoogleSheets\Hooks\InitializationHook as GoogleSheetsInitializationHook;
use IvyForms\Repository\GoogleSheets\GoogleSheetsRepositoryInterface;
use IvyForms\Services\GoogleSheets\GoogleSheetsDispatcherService;
use IvyForms\Services\GoogleSheets\GoogleSheetsManagerService;
use IvyForms\Services\GoogleSheets\GoogleSheetsService;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsConnectController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsOAuthCallbackController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsCreateController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsDeleteController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsDisconnectController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsListColumnsController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsListIntegrationsController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsListSpreadsheetsController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsListWorksheetsController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsStatusController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsUpdateController;
use IvyForms\Controllers\GoogleSheets\GoogleSheetsUpdateStatusController;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Usage\UsageTrackingService;
use IvyForms\Vendor\Melograno\UsageTracker\Collectors\Plugin\IvyFormsCollector;

return [

    MenuManager::class => function ($container) {
        return new MenuManager(
            new MenuService($container->get(RoutePermissionService::class)),
            new MenuMapper($container->get(PermissionResolver::class))
        );
    },

    PluginInstaller::class => function () {
        return new PluginInstaller();
    },

    InstallPluginController::class => function ($container) {
        return new InstallPluginController(
            $container->get(PluginInstaller::class)
        );
    },

    IntegrationRegistry::class => function () {
        return new IntegrationRegistry();
    },

    IntegrationService::class => function ($container) {
        return new IntegrationService(
            $container->get(IntegrationRegistry::class),
            $container->get(\IvyForms\Services\GoogleSheets\Hooks\InitializationHook::class)
        );
    },
    SubmissionDataFormatterService::class => function () {
        return new SubmissionDataFormatterService();
    },

    SubmissionUploadService::class => function () {
        return new SubmissionUploadService();
    },

    FormSubmissionService::class => function ($container) {
        return new FormSubmissionService(
            $container->get(SubmissionUploadService::class),
            $container->get(FormService::class),
            $container->get(FieldService::class),
            $container->get(FieldDuplicateValidationService::class),
            $container->get(SecurityService::class),
            $container->get(EntryService::class),
            $container->get(NotificationService::class),
            $container->get(MailerService::class),
            $container->get(ConfirmationService::class)
        );
    },

    GoogleSheetsRequestSanitizer::class => static function () {
        return new GoogleSheetsRequestSanitizer();
    },

    GoogleSheetsService::class => static function ($container) {
        return new GoogleSheetsService(
            $container->get(SettingsService::class)
        );
    },

    GoogleSheetsManagerService::class => static function ($container) {
        return new GoogleSheetsManagerService(
            $container->get(GoogleSheetsRepositoryInterface::class)
        );
    },

    GoogleSheetsDispatcherService::class => static function ($container) {
        return new GoogleSheetsDispatcherService(
            $container->get(GoogleSheetsRepositoryInterface::class),
            $container->get(GoogleSheetsService::class),
            $container->get(SubmissionDataFormatterService::class)
        );
    },

    GoogleSheetsEntryHooks::class => static function ($container) {
        return new GoogleSheetsEntryHooks(
            $container->get(GoogleSheetsDispatcherService::class)
        );
    },

    GoogleSheetsFormHooks::class => static function ($container) {
        return new GoogleSheetsFormHooks(
            $container->get(GoogleSheetsRepositoryInterface::class)
        );
    },

    GoogleSheetsInitializationHook::class => static function ($container) {
        return new GoogleSheetsInitializationHook(
            $container->get(SettingsService::class)
        );
    },

    GoogleSheetsConnectController::class => static function ($container) {
        return new GoogleSheetsConnectController(
            $container->get(GoogleSheetsService::class)
        );
    },

    GoogleSheetsOAuthCallbackController::class => static function ($container) {
        return new GoogleSheetsOAuthCallbackController(
            $container->get(GoogleSheetsService::class)
        );
    },

    GoogleSheetsDisconnectController::class => static function ($container) {
        return new GoogleSheetsDisconnectController(
            $container->get(GoogleSheetsService::class)
        );
    },

    GoogleSheetsStatusController::class => static function ($container) {
        return new GoogleSheetsStatusController(
            $container->get(GoogleSheetsService::class)
        );
    },

    GoogleSheetsListSpreadsheetsController::class => static function ($container) {
        return new GoogleSheetsListSpreadsheetsController(
            $container->get(GoogleSheetsService::class)
        );
    },

    GoogleSheetsListWorksheetsController::class => static function ($container) {
        return new GoogleSheetsListWorksheetsController(
            $container->get(GoogleSheetsService::class)
        );
    },

    GoogleSheetsListColumnsController::class => static function ($container) {
        return new GoogleSheetsListColumnsController(
            $container->get(GoogleSheetsService::class)
        );
    },

    GoogleSheetsListIntegrationsController::class => static function ($container) {
        return new GoogleSheetsListIntegrationsController(
            $container->get(GoogleSheetsManagerService::class)
        );
    },

    GoogleSheetsCreateController::class => static function ($container) {
        return new GoogleSheetsCreateController(
            $container->get(GoogleSheetsManagerService::class),
            $container->get(GoogleSheetsRequestSanitizer::class)
        );
    },

    GoogleSheetsUpdateController::class => static function ($container) {
        return new GoogleSheetsUpdateController(
            $container->get(GoogleSheetsManagerService::class),
            $container->get(GoogleSheetsRequestSanitizer::class)
        );
    },

    GoogleSheetsUpdateStatusController::class => static function ($container) {
        return new GoogleSheetsUpdateStatusController(
            $container->get(GoogleSheetsManagerService::class)
        );
    },

    GoogleSheetsDeleteController::class => static function ($container) {
        return new GoogleSheetsDeleteController(
            $container->get(GoogleSheetsManagerService::class)
        );
    },

    FormImportExportService::class => function ($container) {
        return new FormImportExportService(
            $container->get(FormService::class),
            $container->get(FieldService::class),
            $container->get(NotificationService::class),
            $container->get(ConfirmationService::class),
            $container->get(\IvyForms\Services\Permissions\AccessRulesFormScopeExtender::class)
        );
    },

    ExportFormController::class => function ($container) {
        return new ExportFormController(
            $container->get(FormImportExportService::class),
            $container->get(RoutePermissionService::class)
        );
    },

    ImportFormController::class => function ($container) {
        return new ImportFormController(
            $container->get(FormImportExportService::class)
        );
    },

    IvyFormsCollector::class => function () {
        return new IvyFormsCollector();
    },

    UsageTrackingService::class => function ($container) {
        return new UsageTrackingService($container->get(IvyFormsCollector::class));
    },

//    GetAllIntegrationsController::class => function ($container) {
//        return new GetAllIntegrationsController(
//            $container->get(IntegrationRegistry::class)
//        );
//    },
//
//    GetIntegrationController::class => function ($container) {
//        return new GetIntegrationController(
//            $container->get(IntegrationRegistry::class)
//        );
//    },
];
