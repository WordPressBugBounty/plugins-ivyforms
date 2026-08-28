<?php

namespace IvyForms\Services\API;

use Exception;
use IvyForms\Factory\Form\FormFactory;
use IvyForms\Services\Confirmation\ConfirmationService;
use IvyForms\Services\Entry\EntryService;
use IvyForms\Services\FieldOptions\FieldOptionsService;
use IvyForms\Services\Form\FormService;
use IvyForms\Services\Field\FieldService;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Services\Notification\NotificationService;
use IvyForms\Services\Settings\SettingsService;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\Vendor\DI\ContainerBuilder;
use WP_Error;
use IvyForms\Services\API\IvyFormsAPIHelpers;

/**
 * Class IvyFormsAPI
 *
 * @package IvyForms\Services\API
 *
 * This class provides a singleton instance to interact with the IvyForms plugin.
 * It includes methods to manage forms and fields, including creating, updating,
 * deleting, and retrieving forms and fields.
 */
class IvyFormsAPI
{
    use IvyFormsAPIHelpers;

    private static ?self $instance = null;
    private FormService $formService;
    private FieldService $fieldService;
    private FieldOptionsService $fieldOptionsService;
    private NotificationService $notificationService;
    private ConfirmationService $confirmationService;
    private EntryService $entryService;
    private SettingsService $settingsService;
    private function __construct(
        FormService $formService,
        FieldService $fieldService,
        FieldOptionsService $fieldOptionsService,
        NotificationService $notificationService,
        ConfirmationService $confirmationService,
        EntryService $entryService,
        SettingsService $settingsService
    ) {
        $this->formService = $formService;
        $this->fieldService = $fieldService;
        $this->fieldOptionsService = $fieldOptionsService;
        $this->notificationService = $notificationService;
        $this->confirmationService = $confirmationService;
        $this->entryService = $entryService;
        $this->settingsService = $settingsService;
    }

    /**
     * Check if IvyForms plugin is installed and activated
     *
     * @return bool
     */
    public static function isPluginActive(): bool
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active('ivyforms/ivyforms.php');
    }

    /**
     * Check if Pro plugin is installed and activated
     *
     * @return bool
     */
    public static function isProPluginActive(): bool
    {
        // Check if Pro plugin constant is defined
        if (defined('IVYFORMS_PRO_VERSION')) {
            return true;
        }

        // Alternative check: see if Pro plugin class exists
        return class_exists('IvyFormsPro\\Plugin\\Plugin', false);
    }

    /**
     * Get instance with error handling
     *
     * @return self|WP_Error Returns an instance of IvyFormsAPI or WP_Error on failure
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            return self::handleErrors(function () {
                if (!self::isPluginActive()) {
                    throw new Exception(
                        BackendStrings::getExceptionStrings()['ivyforms_not_installed']
                    );
                }
                if (!file_exists(IVYFORMS_PATH . '/backend/vendor/autoload.php')) {
                    throw new Exception(
                        BackendStrings::getExceptionStrings()['autoload_not_found']
                    );
                }
                if (!file_exists(IVYFORMS_PATH . '/backend/src/Config/container.php')) {
                    throw new Exception(
                        BackendStrings::getExceptionStrings()['container_not_found']
                    );
                }

                $builder = new ContainerBuilder();
                $builder->addDefinitions(IVYFORMS_PATH . '/backend/src/Config/repository.php');
                $builder->addDefinitions(IVYFORMS_PATH . '/backend/src/Config/services.php');

                /**
                 * Allow to hook into container before build
                 * @since 0.1.0
                 *
                 *  Arguments:
                 *  - ContainerBuilder $builder The container builder instance
                 */
                do_action('ivyforms/boot/extend_container_builder', $builder);

                $container = $builder->build();

                $formService = $container->get(FormService::class);
                $fieldService = $container->get(FieldService::class);
                $fieldOptionsService = $container->get(FieldOptionsService::class);
                $notificationService = $container->get(NotificationService::class);
                $confirmationService = $container->get(ConfirmationService::class);
                $entryService = $container->get(EntryService::class);
                $settingsService = $container->get(SettingsService::class);

                self::$instance = new self(
                    $formService,
                    $fieldService,
                    $fieldOptionsService,
                    $notificationService,
                    $confirmationService,
                    $entryService,
                    $settingsService
                );
                return self::$instance;
            });
        }
        return self::$instance;
    }

    /**
     * Get a single form by ID
     *
     * @param int $formId
     * @return object|WP_Error
     */
    public static function getForm(int $formId)
    {
        return self::handleErrors(function () use ($formId) {
            if ($formId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            return self::getInstance()->formService->getFormById($formId);
        });
    }

    /**
     * Get all forms
     *
     * @return mixed[] | WP_Error
     */
    public static function getForms()
    {
        return self::handleErrors(function () {
            return self::getInstance()->formService->getAllForms();
        });
    }

    /**
     * Create a new form
     *
     * @param mixed[] $formData
     * @return int|WP_Error
     */
    public static function createForm(array $formData)
    {
        return self::handleErrors(function () use ($formData) {
            if (empty($formData)) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_data']
                );
            }
            $formData = FormFactory::create($formData);
            return self::getInstance()->formService->createForm($formData);
        });
    }

    /**
     * Update an existing form
     *
     * @param int $formId
     * @param mixed[] $formData
     * @return bool|WP_Error
     */
    public static function updateForm(int $formId, array $formData)
    {
        return self::handleErrors(function () use ($formId, $formData) {
            if ($formId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            if (empty($formData)) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_data']
                );
            }
            $formData = FormFactory::create($formData);
            return self::getInstance()->formService->updateForm($formId, $formData);
        });
    }

    /**
     * Delete a form
     *
     * @param int $formId
     * @return bool | WP_Error
     */
    public static function deleteForm(int $formId)
    {
        return self::handleErrors(function () use ($formId) {
            if ($formId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            self::getInstance()->formService->deleteForm($formId);
            self::getInstance()->fieldService->deleteFieldsByFormId($formId);
            return true;
        });
    }

    /**
     * Check if a form exists
     *
     * @param int $formId
     * @return bool | WP_Error
     */
    public static function formExists(int $formId)
    {
        return self::handleErrors(function () use ($formId) {
            if ($formId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            return self::getInstance()->formService->formExists($formId);
        });
    }

    /**
     * Get all fields for a form
     *
     * @param int $formId
     * @return mixed[] | WP_Error
     */
    public static function getFields(int $formId)
    {
        return self::handleErrors(function () use ($formId) {
            if ($formId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            return self::getInstance()->fieldService->getAllFields($formId);
        });
    }

    /**
     * Delete fields by form ID
     *
     * @param int $formId
     * @return bool | WP_Error
     */
    public static function deleteFields(int $formId)
    {
        return self::handleErrors(function () use ($formId) {
            if ($formId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            self::getInstance()->fieldService->deleteFieldsByFormId($formId);
            return true;
        });
    }
    /**
     * Get all fields for a form
     *
     * @param int $formId
     * @return mixed[] | WP_Error
     */
    public static function getConfirmations(int $formId)
    {
        return self::handleErrors(function () use ($formId) {
            if ($formId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            return self::getInstance()->confirmationService->getConfirmationsById($formId);
        });
    }

    /**
     * Get all notifications for a form
     *
     * @param int $formId
     * @return mixed[] | WP_Error
     */
    public static function getNotifications(int $formId)
    {
        return self::handleErrors(function () use ($formId) {
            if ($formId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            return self::getInstance()->notificationService->getNotificationsByFormId($formId);
        });
    }
    /**
     * Get all field options for a field
     *
     * @param int $fieldId
     * @return mixed[] | WP_Error
     */
    public static function getFieldOptions(int $fieldId)
    {
        return self::handleErrors(function () use ($fieldId) {
            if ($fieldId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_field_id']
                );
            }
            $instance = self::getInstance();
            if ($instance instanceof WP_Error) {
                return $instance;
            }
            return $instance->fieldOptionsService->getByFieldId($fieldId);
        });
    }

    /**
     * Get entries for a specific form, with optional filters
     * @param int $formId
     * @param array<string, mixed> $params
     * @return array<int, mixed>|WP_Error
     */
    public static function getFormEntries(int $formId, array $params = [])
    {
        return self::handleErrors(function () use ($formId, $params) {
            if ($formId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            // Merge formId into filters
            $params['filters']['formId'] = $formId;
            $result = self::getInstance()->entryService->getEntryManager()->searchEntries($params);
            return $result['data'] ?? [];
        });
    }

    /**
     * Get entry fields for given entries
     *
     * @param array<int, mixed> $entries
     * @return array<int, mixed>|WP_Error
     */
    public static function getEntryFields(array $entries)
    {
        return self::handleErrors(function () use ($entries) {
            if (empty($entries)) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            return self::getInstance()->entryService->getEntryFieldManager()->getEntryFields($entries);
        });
    }

    /**
     * Check if an integration is enabled
     *
     * @param string $integration
     * @return bool
     */
    public static function isIntegrationEnabled(string $integration): bool
    {
        $result = self::handleErrors(function () use ($integration) {
            $instance = self::getInstance();
            if ($instance instanceof WP_Error) {
                return false;
            }
            return self::checkIntegrationEnabled($instance, $integration);
        });
        return $result === true;
    }

    /**
     * Get forms with an integration enabled
     *
     * @param string $integration
     * @return array<int, mixed>|WP_Error Array of forms with an integration enabled
     */
    public static function getFormsWithIntegrationEnabled(string $integration)
    {
        return self::handleErrors(function () use ($integration) {
            $allForms = self::getInstance()->formService->getAllForms();
            return self::filterFormsWithIntegration($allForms, $integration);
        });
    }
    /**
     * Check if a specific form has an integration enabled
     *
     * @param int $formId
     * @param string $integration
     * @return bool|WP_Error
     */
    public static function isIntegrationEnabledForForm(int $formId, string $integration)
    {
        return self::handleErrors(function () use ($integration, $formId) {
            if ($formId <= 0) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_form_id']
                );
            }
            $form = self::getInstance()->formService->getFormById($formId);
            if (method_exists($form, 'getIntegrationSettings')) {
                $integrationSettings = $form->getIntegrationSettings();
                return $integrationSettings->isIntegrationEnabled($integration)
                    || empty($integrationSettings->toArray());
            }
            return false;
        });
    }
}
