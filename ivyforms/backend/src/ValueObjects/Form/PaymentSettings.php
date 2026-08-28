<?php

namespace IvyForms\ValueObjects\Form;

use IvyForms\Common\Constants\SupportedCurrencyCodes;
use IvyForms\Services\Template\TemplateDefaults;

/**
 * Form-level payment display settings (currency for product fields, totals, etc.).
 */
final class PaymentSettings
{
    private string $currency;

    /**
     * @param array<string, mixed>|null $settings Raw settings (typically already sanitized).
     */
    public function __construct($settings = null)
    {
        if (!is_array($settings) || empty($settings)) {
            $settings = TemplateDefaults::getDefaultPaymentSettings();
        }

        $code = isset($settings['currency'])
            ? strtoupper(trim((string) $settings['currency']))
            : SupportedCurrencyCodes::DEFAULT_CODE;
        $this->currency = SupportedCurrencyCodes::isAllowed($code) ? $code : SupportedCurrencyCodes::DEFAULT_CODE;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $code): void
    {
        $normalized = strtoupper(trim($code));
        $this->currency = SupportedCurrencyCodes::isAllowed($normalized)
            ? $normalized
            : SupportedCurrencyCodes::DEFAULT_CODE;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'paymentSettings' => [
                'currency' => $this->currency,
            ],
        ];
    }
}
