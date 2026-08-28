<?php

namespace IvyForms\Services\API;

use WP_Error;
use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\NotFoundException;
use IvyForms\Common\Exceptions\QueryExecutionException;
use IvyForms\Common\Exceptions\ValidationException;
use Exception;

/**
 * Trait IvyFormsAPIHelpers
 *
 * Contains helper methods for IvyFormsAPI to reduce complexity.
 */
trait IvyFormsAPIHelpers
{
    /**
     * Handle errors uniformly across all API methods
     *
     * @param callable $callback The function to execute
     * @return mixed The result of the callback or WP_Error on failure
     */
    protected static function handleErrors(callable $callback)
    {
        try {
            return $callback();
        } catch (InvalidArgumentException $e) {
            return new WP_Error('invalid_argument', $e->getMessage(), ['status' => 400]);
        } catch (NotFoundException $e) {
            return new WP_Error('not_found', $e->getMessage(), ['status' => 404]);
        } catch (QueryExecutionException $e) {
            return new WP_Error('query_execution_error', $e->getMessage(), ['status' => 500]);
        } catch (ValidationException $e) {
            return new WP_Error('validation_error', $e->getMessage(), ['status' => 422]);
        } catch (Exception $e) {
            return new WP_Error('unknown_error', $e->getMessage(), ['status' => 500]);
        }
    }

    /**
     * Filter forms that have an integration enabled
     *
     * @param array<int, mixed> $forms
     * @return array<int, mixed>
     */
    private static function filterFormsWithIntegration(array $forms, string $integration): array
    {
        return array_filter($forms, function ($form) use ($integration) {
            if (method_exists($form, 'getIntegrationSettings')) {
                $integrationSettings = $form->getIntegrationSettings();
                return $integrationSettings->isIntegrationEnabled($integration)
                    || empty($integrationSettings->toArray());
            }
            return false;
        });
    }

    /**
     * Check if a specific integration is enabled in settings
     *
     * @param self $instance
     * @param string $integration
     * @return bool
     */
    private static function checkIntegrationEnabled(self $instance, string $integration): bool
    {
        $integrations = $instance->settingsService->getCategorySettings('integrations');
        return !empty($integrations[$integration]['enabled']) && $integrations[$integration]['enabled'] === true;
    }
}
