<?php

namespace IvyForms\Common\Helpers\Import\Sources;

use IvyForms\Common\Helpers\Import\FluentFormsImportConverter;
use IvyForms\Common\Helpers\Import\FormImportSourceInterface;

/**
 * Fluent Forms export source.
 *
 * Detection lives here; field mapping stays in FluentFormsImportConverter /
 * FluentFormsFieldMapper.
 */
class FluentFormsImportSource implements FormImportSourceInterface
{
    public const ID = 'fluentforms';

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return self::ID;
    }

    /**
     * {@inheritdoc}
     */
    public function matches(array $importData): bool
    {
        if (isset($importData['form_fields'])) {
            return true;
        }

        if (isset($importData[0]) && is_array($importData[0])) {
            return isset($importData[0]['form_fields']);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(array $importData): array
    {
        return FluentFormsImportConverter::convert($importData);
    }
}
