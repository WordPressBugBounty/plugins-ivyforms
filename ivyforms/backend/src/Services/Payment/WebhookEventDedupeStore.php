<?php

namespace IvyForms\Services\Payment;

/**
 * Deduplicates inbound payment webhooks by provider event id.
 */
class WebhookEventDedupeStore
{
    private const TTL = DAY_IN_SECONDS;

    /**
     * Returns true when this event should be processed (first sighting, or no id to key on).
     */
    public static function claim(string $provider, string $eventId): bool
    {
        $provider = sanitize_key($provider);
        $eventId = sanitize_text_field($eventId);
        if ($provider === '' || $eventId === '') {
            return true;
        }

        $name = 'ivyforms_wh_' . $provider . '_' . md5($eventId);
        if (get_transient($name) !== false) {
            return false;
        }

        set_transient($name, 1, self::TTL);

        return true;
    }
}
