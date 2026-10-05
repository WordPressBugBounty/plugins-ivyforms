<?php

namespace IvyForms\Common\Helpers\Import;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Helpers\Import\Sources\FluentFormsImportSource;
use IvyForms\Common\Helpers\Import\Sources\GravityFormsImportSource;
use IvyForms\Common\Helpers\Import\Sources\IvyFormsImportSource;
use IvyForms\Common\Helpers\Import\Sources\WpFormsImportSource;
use IvyForms\Common\Helpers\Import\Sources\WsFormImportSource;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Thin normalizer that builds the import-source registry and dispatches to the
 * first matching source.
 *
 * Built-in sources: IvyForms, WS Form, Fluent Forms, WPForms, and Gravity Forms.
 * Add-ons register more via `ivyforms/form/import/register_sources`.
 */
class FormImportNormalizer
{
    /**
     * Detect the source of the payload and convert it when necessary.
     *
     * @param mixed $importData
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function normalize($importData): array
    {
        if (!is_array($importData) || empty($importData)) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['no_import_data_provided']
            );
        }

        return self::createRegistry()->normalize($importData);
    }

    /**
     * Build a registry with built-in sources, then let add-ons register more.
     *
     * @return FormImportSourceRegistry
     */
    public static function createRegistry(): FormImportSourceRegistry
    {
        $registry = new FormImportSourceRegistry();
        $registry->register(new IvyFormsImportSource());
        $registry->register(new WsFormImportSource());
        $registry->register(new FluentFormsImportSource());
        $registry->register(new WpFormsImportSource());
        $registry->register(new GravityFormsImportSource());

        /**
         * Register additional form import sources (CF7, Formidable Forms, etc.).
         *
         * @since 1.0.0
         *
         * @param FormImportSourceRegistry $registry
         */
        do_action('ivyforms/form/import/register_sources', $registry);

        return $registry;
    }
}
