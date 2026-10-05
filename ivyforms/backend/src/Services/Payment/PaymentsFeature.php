<?php

namespace IvyForms\Services\Payment;

/**
 * Release gate for the payments feature (Square / Stripe / PayPal).
 *
 * While disabled, the payment integration cards stay visible as "Coming soon" on the
 * Integrations page, but nothing can be managed: no Payments admin page, no payment
 * settings pages and no Payment Method field in the form builder.
 */
class PaymentsFeature
{
    public static function isEnabled(): bool
    {
        /** @var mixed $enabled Can be overridden in wp-config.php, so not a compile-time value */
        $enabled = defined('IVYFORMS_PAYMENTS_ENABLED') ? constant('IVYFORMS_PAYMENTS_ENABLED') : false;

        /**
         * Filter whether the payments feature is exposed in the admin UI.
         *
         * @param bool $enabled
         */
        return (bool) apply_filters('ivyforms/payments/enabled', (bool) $enabled);
    }
}
