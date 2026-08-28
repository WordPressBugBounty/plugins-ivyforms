<?php

namespace IvyForms\Common\Helpers\Import;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Registry of form import sources.
 *
 * Sources are tried in registration order; the first match wins.
 */
class FormImportSourceRegistry
{
    /**
     * @var array<int, FormImportSourceInterface>
     */
    private array $sources = [];

    /**
     * Register an import source.
     *
     * @param FormImportSourceInterface $source
     * @return void
     */
    public function register(FormImportSourceInterface $source): void
    {
        $this->sources[] = $source;
    }

    /**
     * @return array<int, FormImportSourceInterface>
     */
    public function getAll(): array
    {
        return $this->sources;
    }

    /**
     * Find the first registered source that matches the payload.
     *
     * @param array<string|int, mixed> $importData
     * @return FormImportSourceInterface|null
     */
    public function findMatching(array $importData): ?FormImportSourceInterface
    {
        foreach ($this->sources as $source) {
            if ($source->matches($importData)) {
                return $source;
            }
        }

        return null;
    }

    /**
     * Normalize a payload using the first matching registered source.
     *
     * @param array<string|int, mixed> $importData
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public function normalize(array $importData): array
    {
        $source = $this->findMatching($importData);
        if ($source === null) {
            throw new InvalidArgumentException(
                BackendStrings::getExceptionStrings()['unsupported_import_format']
            );
        }

        return $source->normalize($importData);
    }
}
