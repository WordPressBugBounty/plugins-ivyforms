<?php

namespace IvyForms\Repository\Confirmation;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Repository\BaseRepositoryInterface;

interface ConfirmationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all confirmations by ID.
     *
     * @param int $id
     *
     * @return array<mixed>
     */
    public function getAllById(int $id): array;

    /**
     * Count confirmations for a form.
     *
     * @param int $formId
     * @return int
     */
    public function countByFormId(int $formId): int;

    /**
     * Get the next position value for a new confirmation on a form.
     *
     * @param int $formId
     * @return int
     */
    public function getNextPosition(int $formId): int;

    /**
     * Update positions for confirmations on a form.
     *
     * @param int $formId
     * @param array<int, int> $orderedIds Confirmation IDs in desired order
     * @return void
     */
    public function updatePositions(int $formId, array $orderedIds): void;

    /**
     * Set which confirmation is the default for a form.
     *
     * @param int $formId
     * @param int $defaultConfirmationId
     * @return void
     */
    public function syncDefaultFlag(int $formId, int $defaultConfirmationId): void;
}
