<?php

namespace IvyForms\Services\Payment;

use IvyForms\Entity\Form\Form;

/**
 * Payment provider used during public form submission (Square, Stripe, …).
 */
interface PaymentGatewayInterface
{
    public function getId(): string;

    /**
     * Charge (or begin SCA) after form validation. Must not create the IvyForms entry.
     *
     * @param array<string, mixed> $rawParams
     * @param array<int, object> $formFields
     * @param array<string, mixed> $submissionData
     * @param object $paymentField
     */
    public function processCharge(
        array $rawParams,
        Form $form,
        array $formFields,
        array $submissionData,
        object $paymentField,
        float $total
    ): PaymentChargeResult;
}
