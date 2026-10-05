<?php

namespace IvyForms\Services\Payment;

use IvyForms\Entity\Form\Form;

/**
 * Resolves the Payments "Customer" column from mapped billing fields.
 */
class SubmissionPaymentCustomerName
{
    /**
     * @param array<int, object> $formFields
     * @param array<int|string, mixed> $submissionData
     * @param array<string, mixed> $submissionByTypeIndex
     */
    public static function resolve(
        ?string $fromCharge,
        Form $form,
        string $gatewayId,
        array $formFields,
        array $submissionData,
        array $submissionByTypeIndex
    ): ?string {
        if ($fromCharge !== null && $fromCharge !== '') {
            return $fromCharge;
        }

        return FormSubmissionPaymentService::resolveCustomerNameFromBuyerInfo(
            SquareBuyerInfoBuilder::build(
                self::settingsForGateway($form, $gatewayId),
                $submissionByTypeIndex,
                $formFields,
                $submissionData
            )
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function settingsForGateway(Form $form, string $gatewayId): array
    {
        $integrationSettings = $form->getIntegrationSettings();
        if ($integrationSettings === null) {
            return [];
        }

        foreach (array_unique([$gatewayId, 'paypal', 'stripe', 'square']) as $slug) {
            $settings = $integrationSettings->get($slug);
            if (!is_array($settings)) {
                continue;
            }
            if (self::hasBuyerMapping($settings)) {
                return $settings;
            }
        }

        $fallback = $integrationSettings->get($gatewayId);

        return is_array($fallback) ? $fallback : [];
    }

    /**
     * @param array<string, mixed> $settings
     */
    private static function hasBuyerMapping(array $settings): bool
    {
        return (string) ($settings['billingNameField'] ?? '') !== ''
            || (string) ($settings['buyerEmailField'] ?? '') !== '';
    }
}
