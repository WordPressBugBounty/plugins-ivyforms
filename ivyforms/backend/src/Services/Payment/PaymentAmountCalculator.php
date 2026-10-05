<?php

namespace IvyForms\Services\Payment;

/**
 * Server-side payment total calculation from product/quantity fields.
 */
class PaymentAmountCalculator
{
    private ProductSelectionSubtotalCalculator $productSubtotalCalculator;

    public function __construct(?ProductSelectionSubtotalCalculator $productSubtotalCalculator = null)
    {
        $this->productSubtotalCalculator = $productSubtotalCalculator ?? new ProductSelectionSubtotalCalculator();
    }

    /**
     * @param array<int, array<string, mixed>> $formFields
     * @param array<string, mixed> $submissionData
     */
    public function calculateTotal(array $formFields, array $submissionData, string $currency = 'USD'): float
    {
        return MoneyMinorUnits::fromMinor(
            $this->calculateTotalMinor($formFields, $submissionData, $currency),
            $currency
        );
    }

    /**
     * Sum line totals in integer minor units so JPY / rounding cannot drift.
     *
     * @param array<int, array<string, mixed>> $formFields
     * @param array<string, mixed> $submissionData
     */
    public function calculateTotalMinor(
        array $formFields,
        array $submissionData,
        string $currency = 'USD'
    ): int {
        $lines = $this->buildPaymentCartLines($formFields, $submissionData, $currency);
        $total = 0;

        foreach ($lines as $line) {
            $total += (int) $line['lineTotalMinor'];
        }

        return $total;
    }

    /**
     * @param array<int, array<string, mixed>> $formFields
     * @param array<string, mixed> $submissionData
     * @return array<int, array<string, mixed>>
     */
    public function buildPaymentCartLines(
        array $formFields,
        array $submissionData,
        string $currency = 'USD'
    ): array {
        $products = [];
        $quantities = [];

        foreach ($formFields as $field) {
            $type = (string) ($field['type'] ?? '');
            if ($type === 'product') {
                $products[] = $field;
            } elseif ($type === 'quantity') {
                $quantities[] = $field;
            }
        }

        usort(
            $products,
            static fn(array $left, array $right): int =>
                ((int) ($left['fieldIndex'] ?? 0)) <=> ((int) ($right['fieldIndex'] ?? 0))
        );
        usort(
            $quantities,
            static fn(array $left, array $right): int =>
                ((int) ($left['fieldIndex'] ?? 0)) <=> ((int) ($right['fieldIndex'] ?? 0))
        );

        $usedQuantityIndexes = [];
        $lines = [];

        foreach ($products as $position => $productField) {
            $productIndex = (int) ($productField['fieldIndex'] ?? 0);
            $productKey = 'product_' . $productIndex;
            $rawProduct = $submissionData[$productKey] ?? null;

            $quantityField = $this->findLinkedQuantityField(
                $productIndex,
                $quantities,
                $usedQuantityIndexes,
                $position
            );

            if ($quantityField !== null) {
                $usedQuantityIndexes[] = (int) ($quantityField['fieldIndex'] ?? 0);
            }

            $unitSubtotal = $this->productSubtotalCalculator->compute($productField, $rawProduct);
            $quantity = $quantityField !== null
                ? MoneyParser::parseQuantity(
                    $submissionData['quantity_' . (int) ($quantityField['fieldIndex'] ?? 0)] ?? null
                )
                : 1;

            $safeQty = $quantity > 0 ? $quantity : 0;
            $lineTotalMinor = MoneyMinorUnits::toMinor($unitSubtotal, $currency) * $safeQty;
            $lines[] = [
                'productFieldIndex' => $productIndex,
                'lineTotalMinor' => $lineTotalMinor,
                'lineTotal' => MoneyMinorUnits::fromMinor($lineTotalMinor, $currency),
            ];
        }

        return $lines;
    }

    /**
     * @param array<int, array<string, mixed>> $quantities
     * @param array<int, int> $usedQuantityIndexes
     * @return array<string, mixed>|null
     */
    private function findLinkedQuantityField(
        int $productIndex,
        array $quantities,
        array $usedQuantityIndexes,
        int $position
    ): ?array {
        foreach ($quantities as $quantityField) {
            $qIndex = (int) ($quantityField['fieldIndex'] ?? 0);
            if (in_array($qIndex, $usedQuantityIndexes, true)) {
                continue;
            }

            $config = $this->parseQuantityConfig($quantityField);
            if (($config['linkedProductFieldIndex'] ?? null) === $productIndex) {
                return $quantityField;
            }
        }

        $unused = array_values(
            array_filter(
                $quantities,
                static fn(array $qtyField): bool =>
                    !in_array((int) ($qtyField['fieldIndex'] ?? 0), $usedQuantityIndexes, true)
            )
        );

        return $unused[$position] ?? null;
    }

    /**
     * @param array<string, mixed> $field
     * @return array<string, mixed>
     */
    private function parseQuantityConfig(array $field): array
    {
        $raw = (string) ($field['htmlContent'] ?? '');
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
