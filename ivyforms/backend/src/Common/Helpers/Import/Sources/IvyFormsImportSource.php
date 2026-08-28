<?php

namespace IvyForms\Common\Helpers\Import\Sources;

use IvyForms\Common\Helpers\Import\FormImportSourceInterface;

/**
 * Native IvyForms export source (top-level "forms" array).
 */
class IvyFormsImportSource implements FormImportSourceInterface
{
    public const ID = 'ivyforms';

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
        return isset($importData['forms']) && is_array($importData['forms']);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(array $importData): array
    {
        return $importData;
    }
}
