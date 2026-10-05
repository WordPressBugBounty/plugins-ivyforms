<?php

namespace IvyForms\Common\Helpers\Import\Sources;

use IvyForms\Common\Helpers\Import\FormImportSourceInterface;
use IvyForms\Common\Helpers\Import\GravityFormsImportConverter;

/**
 * Gravity Forms JSON export source.
 */
class GravityFormsImportSource implements FormImportSourceInterface
{
    public const ID = 'gravityforms';

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
        if (isset($importData['title'], $importData['fields'])) {
            return is_array($importData['fields']);
        }

        $form = $importData[0] ?? null;

        return isset($importData['version'])
            && is_array($form)
            && isset($form['title'], $form['fields'])
            && is_array($form['fields']);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(array $importData): array
    {
        return GravityFormsImportConverter::convert($importData);
    }
}
