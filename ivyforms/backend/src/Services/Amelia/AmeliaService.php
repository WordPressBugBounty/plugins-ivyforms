<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Amelia;

use IvyForms\Repository\Amelia\AmeliaRepository;
use IvyForms\Services\Integrations\PluginInstaller;

/**
 * Service for Amelia booking URLs linked to IvyForms entries.
 */
class AmeliaService
{
    private const AMELIA_PLUGIN_SLUG = 'ameliabooking';

    private AmeliaRepository $ameliaRepository;
    private PluginInstaller $pluginInstaller;

    public function __construct(AmeliaRepository $ameliaRepository, PluginInstaller $pluginInstaller)
    {
        $this->ameliaRepository = $ameliaRepository;
        $this->pluginInstaller = $pluginInstaller;
    }

    /**
     * Build Amelia admin booking URLs for the given entry.
     *
     * @return array<int, array{bookingId: int|null, packageCustomerId: int|null, url: string}>
     */
    public function getBookingUrlsByEntryId(int $entryId): array
    {
        if (!$this->isAmeliaIntegrationEnabled()) {
            return [];
        }

        $urls = [];

        foreach ($this->ameliaRepository->getBookingsByEntryId($entryId) as $booking) {
            $bookingId = !empty($booking['id']) ? (int) $booking['id'] : null;

            $packageCustomerId = !empty($booking['purchaseId']) ? (int) $booking['purchaseId'] : null;

            $urls[] = [
                'bookingId'         => $bookingId,
                'packageCustomerId' => $packageCustomerId,
                'url'               => $this->buildBookingUrl($booking, $bookingId, $packageCustomerId),
            ];
        }

        return $urls;
    }

    private function isAmeliaIntegrationEnabled(): bool
    {
        $pluginFile = $this->pluginInstaller->getPluginFile(self::AMELIA_PLUGIN_SLUG);

        if (
            $pluginFile === null
            || !$this->pluginInstaller->isPluginInstalled($pluginFile)
            || !$this->pluginInstaller->isPluginActive(self::AMELIA_PLUGIN_SLUG)
        ) {
            return false;
        }

        $ameliaSettings = json_decode(get_option('amelia_settings'), true);

        return !empty($ameliaSettings['featuresIntegrations']['ivy']['enabled']);
    }

    /**
     * @param array{
     *     appointmentId: int|string|null,
     *     id: int|string|null,
     *     purchaseId: int|string|null
     * } $booking
     */
    private function buildBookingUrl(array $booking, ?int $bookingId, ?int $packageCustomerId): string
    {
        $appointmentId = $booking['appointmentId'] ?? null;

        if (empty($appointmentId) && !empty($bookingId)) {
            return admin_url(
                'admin.php?page=wpamelia-bookings&booking=' . $bookingId
            ) . '#/events';
        }

        if (!empty($packageCustomerId)) {
            return admin_url(
                'admin.php?page=wpamelia-bookings&id=' . $packageCustomerId
            ) . '#/packages';
        }

        return admin_url(
            'admin.php?page=wpamelia-bookings&appointment=' . (int) $appointmentId
            . '&booking=' . (int) $bookingId
        ) . '#/appointments';
    }
}
