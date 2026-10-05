<?php

namespace IvyForms\Services\Payment;

/**
 * Computes per-product subtotals from authoritative field config and submission values.
 *
 * Never prices from client entry snapshots — snapshot priceAmount is display-only.
 */
class ProductSelectionSubtotalCalculator
{
    private ProductSubmissionSnapshotHelper $snapshotHelper;

    public function __construct(?ProductSubmissionSnapshotHelper $snapshotHelper = null)
    {
        $this->snapshotHelper = $snapshotHelper ?? new ProductSubmissionSnapshotHelper();
    }

    /**
     * @param array<string, mixed> $productField
     * @param mixed $rawValue
     */
    public function compute(array $productField, $rawValue): float
    {
        $rawValue = $this->snapshotHelper->normalizeProductSubmissionValue($rawValue);

        $config = $this->parseProductConfig($productField);
        $productType = (string) ($config['productType'] ?? 'single');
        $options = is_array($productField['fieldOptions'] ?? null) ? $productField['fieldOptions'] : [];

        if ($productType === 'single') {
            return MoneyParser::parseToFloat((string) ($config['paymentAmount'] ?? '0'));
        }

        if ($productType === 'user_defined') {
            // Amount is the submitted value (not from field config pricing maps).
            $amount = is_array($rawValue)
                ? 0.0
                : MoneyParser::parseToFloat((string) ($rawValue ?? ''));

            return $this->clampUserDefinedAmount($config, $amount);
        }

        if ($productType === 'checkbox') {
            return $this->sumCheckboxSelections($config, $options, $rawValue);
        }

        return $this->sumScalarSelection($config, $options, $rawValue);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, array<string, mixed>> $options
     * @param mixed $rawValue
     */
    private function sumCheckboxSelections(array $config, array $options, $rawValue): float
    {
        $values = is_array($rawValue)
            ? array_map('strval', $rawValue)
            : array_filter(array_map('trim', explode(',', (string) $rawValue)));

        $sum = 0.0;
        foreach ($values as $val) {
            $sum += $this->optionUnitPrice($config, $val, $options);
        }

        return $sum;
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, array<string, mixed>> $options
     * @param mixed $rawValue
     */
    private function sumScalarSelection(array $config, array $options, $rawValue): float
    {
        $scalar = is_array($rawValue) && !empty($rawValue) ? (string) $rawValue[0] : (string) ($rawValue ?? '');
        if (trim($scalar) === '') {
            return 0.0;
        }

        return $this->optionUnitPrice($config, $scalar, $options);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, array<string, mixed>> $options
     */
    private function optionUnitPrice(array $config, string $optionValue, array $options): float
    {
        $prices = is_array($config['prices'] ?? null) ? $config['prices'] : [];
        $needle = trim($optionValue);

        $fromConfig = $this->priceFromMap($prices, $needle);
        if ($fromConfig !== null) {
            return $fromConfig;
        }

        foreach ($options as $option) {
            $value = (string) ($option['value'] ?? '');
            $id = (string) ($option['id'] ?? '');
            if ($value !== $needle && $id !== $needle) {
                continue;
            }

            foreach ([$value, $id] as $key) {
                $fromOption = $this->priceFromMap($prices, $key);
                if ($fromOption !== null) {
                    return $fromOption;
                }
            }
        }

        return 0.0;
    }

    /**
     * @param array<string, mixed> $prices
     */
    private function priceFromMap(array $prices, string $key): ?float
    {
        if ($key === '') {
            return null;
        }

        foreach ([$key, (string) (int) $key] as $mapKey) {
            if (!isset($prices[$mapKey]) || !is_string($prices[$mapKey]) || trim($prices[$mapKey]) === '') {
                continue;
            }

            return MoneyParser::parseToFloat($prices[$mapKey]);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $field
     * @return array<string, mixed>
     */
    private function parseProductConfig(array $field): array
    {
        $raw = (string) ($field['htmlContent'] ?? '');
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Out-of-range user amounts become 0 so the charge is rejected, not clamped up/down.
     *
     * @param array<string, mixed> $config
     */
    private function clampUserDefinedAmount(array $config, float $amount): float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        $min = $this->optionalBound($config, 'minAmount');
        $max = $this->optionalBound($config, 'maxAmount');
        if ($min !== null && $amount < $min) {
            return 0.0;
        }
        if ($max !== null && $amount > $max) {
            return 0.0;
        }

        return $amount;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function optionalBound(array $config, string $key): ?float
    {
        if (!isset($config[$key]) || !is_scalar($config[$key])) {
            return null;
        }

        $raw = trim((string) $config[$key]);
        if ($raw === '') {
            return null;
        }

        $parsed = MoneyParser::parseToFloat($raw);

        return $parsed > 0 ? $parsed : null;
    }
}
