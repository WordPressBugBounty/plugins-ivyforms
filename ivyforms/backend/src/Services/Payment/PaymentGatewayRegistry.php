<?php

namespace IvyForms\Services\Payment;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Registry of payment gateways available during form submission.
 */
class PaymentGatewayRegistry
{
    /**
     * @var array<string, PaymentGatewayInterface>
     */
    private array $gateways = [];

    public function register(PaymentGatewayInterface $gateway): void
    {
        $this->gateways[$gateway->getId()] = $gateway;
    }

    public function has(string $id): bool
    {
        return isset($this->gateways[$id]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function get(string $id): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$id])) {
            throw new InvalidArgumentException(
                BackendStrings::getPaymentStrings()['payment_gateway_unavailable']
            );
        }

        return $this->gateways[$id];
    }

    /**
     * @return array<string, PaymentGatewayInterface>
     */
    public function all(): array
    {
        return $this->gateways;
    }
}
