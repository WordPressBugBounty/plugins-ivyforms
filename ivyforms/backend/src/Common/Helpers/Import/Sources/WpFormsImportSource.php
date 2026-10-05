<?php

namespace IvyForms\Common\Helpers\Import\Sources;

use IvyForms\Common\Helpers\Import\FormImportSourceInterface;
use IvyForms\Common\Helpers\Import\WpFormsImportConverter;

/**
 * WPForms JSON export source.
 */
class WpFormsImportSource implements FormImportSourceInterface
{
    public const ID = 'wpforms';

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
        $form = isset($importData['fields']) ? $importData : ($importData[0] ?? null);

        return is_array($form)
            && isset($form['fields'], $form['settings'])
            && is_array($form['fields'])
            && is_array($form['settings'])
            && array_key_exists('form_title', $form['settings']);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(array $importData): array
    {
        return WpFormsImportConverter::convert($importData);
    }
}
