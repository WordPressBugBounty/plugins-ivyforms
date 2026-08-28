<?php

namespace IvyForms\Common\Helpers\Import;

// phpcs:disable PSR1.Files.SideEffects

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contract for a form import source (IvyForms native or third-party builder).
 *
 * Each source owns its own detection and normalization so new builders can
 * register without extending a shared switch.
 */
interface FormImportSourceInterface
{
    /**
     * Stable source identifier (e.g. ivyforms, wsform, fluentforms).
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Whether this source can handle the decoded import payload.
     *
     * @param array<string|int, mixed> $importData
     * @return bool
     */
    public function matches(array $importData): bool;

    /**
     * Convert the payload into the IvyForms import structure
     * (`['forms' => [...]]`).
     *
     * @param array<string|int, mixed> $importData
     * @return array<string, mixed>
     */
    public function normalize(array $importData): array;
}
