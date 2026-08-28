<?php

namespace IvyForms\Repository\Notification;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Repository\BaseRepositoryInterface;

interface NotificationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all notifications by ID.
     *
     * @param int $id
     *
     * @return array<mixed>
     */
    public function getAllById(int $id): array;
    /**
     * Get all active notifications.
     *
     * @return array<mixed>
     */
    public function getAllActiveById(int $id): array;
}
