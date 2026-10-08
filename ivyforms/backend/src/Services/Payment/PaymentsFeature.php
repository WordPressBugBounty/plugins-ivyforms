<?php

namespace IvyForms\Services\Payment;

/**
 * Release gate for Stripe and PayPal.
 *
 * Square stays "Coming soon" on the Integrations page regardless of this flag.
 * While disabled, Stripe and PayPal cards stay "Coming soon" and nothing else can be
 * managed: no Payments admin page, no payment settings pages and no Payment Method field.
 */
class PaymentsFeature
{
    public static function isEnabled(): bool
    {
        /** @var mixed $enabled Can be overridden in wp-config.php, so not a compile-time value */
        $enabled = defined('IVYFORMS_PAYMENTS_ENABLED') ? constant('IVYFORMS_PAYMENTS_ENABLED') : true;

        /**
         * Filter whether the payments feature is exposed in the admin UI.
         *
         * @param bool $enabled
         */
        return (bool) apply_filters('ivyforms/payments/enabled', (bool) $enabled);
    }
}
