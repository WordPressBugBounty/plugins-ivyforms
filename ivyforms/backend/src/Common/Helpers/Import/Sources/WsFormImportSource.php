<?php

namespace IvyForms\Common\Helpers\Import\Sources;

use IvyForms\Common\Helpers\Import\FormImportSourceInterface;
use IvyForms\Common\Helpers\Import\WsFormImportConverter;

/**
 * WS Form export source.
 *
 * Detection lives here; field mapping stays in WsFormImportConverter.
 */
class WsFormImportSource implements FormImportSourceInterface
{
    public const ID = 'wsform';

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
        if (isset($importData['identifier']) && $importData['identifier'] === 'ws_form') {
            return true;
        }

        return isset($importData['groups'])
            && is_array($importData['groups'])
            && array_key_exists('label', $importData);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(array $importData): array
    {
        return WsFormImportConverter::convert($importData);
    }
}
