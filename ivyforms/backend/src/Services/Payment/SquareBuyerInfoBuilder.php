<?php

namespace IvyForms\Services\Payment;

/**
 * Builds Square buyer payload fields from form submission values.
 */
class SquareBuyerInfoBuilder
{
    /**
     * @param array<string, mixed> $squareFormSettings
     * @param array<string, mixed> $submissionByTypeIndex
     * @param array<int, object> $formFields
     * @param array<int|string, mixed> $submissionData Field-id keyed submission values
     * @return array<string, mixed>
     */
    public static function build(
        array $squareFormSettings,
        array $submissionByTypeIndex,
        array $formFields = [],
        array $submissionData = []
    ): array {
        $buyerInfo = [];
        $source = $submissionData !== [] ? $submissionData : $submissionByTypeIndex;

        $email = self::mappedEmail(
            $squareFormSettings,
            $submissionByTypeIndex,
            $formFields,
            $source
        );
        if ($email !== '') {
            $buyerInfo['email'] = $email;
        }

        $nameParts = self::mappedNameParts(
            $squareFormSettings,
            $submissionByTypeIndex,
            $formFields,
            $source
        );
        if ($nameParts !== null) {
            $buyerInfo['billingAddress'] = $nameParts;
        }

        $addressValue = SquareBuyerSubmissionValues::mappedValue(
            (string) ($squareFormSettings['billingAddressField'] ?? ''),
            $submissionByTypeIndex,
            $formFields,
            $source
        );
        if (is_array($addressValue)) {
            $buyerInfo['billingAddress'] = self::mergeBillingAddress(
                $buyerInfo['billingAddress'] ?? [],
                $addressValue
            );
        }

        return $buyerInfo;
    }

    /**
     * @param array<string, mixed> $buyerInfo
     */
    public static function customerName(array $buyerInfo): ?string
    {
        $fromAddress = self::billingFullName($buyerInfo);
        if ($fromAddress !== null) {
            return $fromAddress;
        }

        $email = sanitize_email((string) ($buyerInfo['email'] ?? ''));

        return $email !== '' ? $email : null;
    }

    /**
     * @param array<string, mixed> $squareFormSettings
     * @param array<string, mixed> $submissionByTypeIndex
     * @param array<int, object> $formFields
     * @param array<int|string, mixed> $source
     */
    private static function mappedEmail(
        array $squareFormSettings,
        array $submissionByTypeIndex,
        array $formFields,
        array $source
    ): string {
        return sanitize_email((string) (SquareBuyerSubmissionValues::mappedValue(
            (string) ($squareFormSettings['buyerEmailField'] ?? ''),
            $submissionByTypeIndex,
            $formFields,
            $source
        ) ?? ''));
    }

    /**
     * @param array<string, mixed> $squareFormSettings
     * @param array<string, mixed> $submissionByTypeIndex
     * @param array<int, object> $formFields
     * @param array<int|string, mixed> $source
     * @return array{first_name: string, last_name: string}|null
     */
    private static function mappedNameParts(
        array $squareFormSettings,
        array $submissionByTypeIndex,
        array $formFields,
        array $source
    ): ?array {
        $nameKey = (string) ($squareFormSettings['billingNameField'] ?? '');

        return SquareBuyerSubmissionValues::nameParts(
            SquareBuyerSubmissionValues::mappedValue(
                $nameKey,
                $submissionByTypeIndex,
                $formFields,
                $source
            ),
            $nameKey,
            $formFields,
            $source
        );
    }

    /**
     * @param array<string, mixed> $billingAddress
     * @param array<string, mixed> $addressValue
     * @return array<string, mixed>
     */
    private static function mergeBillingAddress(array $billingAddress, array $addressValue): array
    {
        return array_merge($billingAddress, [
            'address_line_1' => sanitize_text_field((string) ($addressValue['streetAddress'] ?? '')),
            'address_line_2' => sanitize_text_field((string) ($addressValue['addressLine2'] ?? '')),
            'locality' => sanitize_text_field((string) ($addressValue['city'] ?? '')),
            'administrative_district_level_1' => sanitize_text_field((string) ($addressValue['state'] ?? '')),
            'postal_code' => sanitize_text_field((string) ($addressValue['zip'] ?? '')),
            'country' => sanitize_text_field((string) ($addressValue['country'] ?? '')),
        ]);
    }

    /**
     * @param array<string, mixed> $buyerInfo
     */
    private static function billingFullName(array $buyerInfo): ?string
    {
        $billing = $buyerInfo['billingAddress'] ?? null;
        if (!is_array($billing)) {
            return null;
        }

        $name = trim(
            (string) ($billing['first_name'] ?? '') . ' ' . (string) ($billing['last_name'] ?? '')
        );

        return $name !== '' ? $name : null;
    }
}
