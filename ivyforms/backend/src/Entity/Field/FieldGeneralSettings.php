<?php

namespace IvyForms\Entity\Field;

use IvyForms\ValueObjects\Field\FieldGeneralSettings as FieldGeneralSettingsVO;

/**
 * Class FieldGeneralSettings
 *
 * Entity wrapper for FieldGeneralSettings ValueObject.
 */
class FieldGeneralSettings
{
    private FieldGeneralSettingsVO $fieldGeneralSettings;

    /**
     * @param FieldGeneralSettingsVO $fieldGeneralSettings
     */
    public function __construct(FieldGeneralSettingsVO $fieldGeneralSettings)
    {
        $this->fieldGeneralSettings = $fieldGeneralSettings;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->fieldGeneralSettings->getLabel();
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->fieldGeneralSettings->isRequired();
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string
    {
        return $this->fieldGeneralSettings->getPlaceholder();
    }

    /**
     * @return bool
     */
    public function isHideLabel(): bool
    {
        return $this->fieldGeneralSettings->isHideLabel();
    }

    /**
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->fieldGeneralSettings->isReadOnly();
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->fieldGeneralSettings->getDescription();
    }

    /**
     * @return string
     */
    public function getRequiredMessage(): string
    {
        return $this->fieldGeneralSettings->getRequiredMessage();
    }

    /**
     * @return string
     */
    public function getCssClasses(): string
    {
        return $this->fieldGeneralSettings->getCssClasses();
    }

    /**
     * @return bool
     */
    public function isShuffleOptions(): bool
    {
        return $this->fieldGeneralSettings->isShuffleOptions();
    }

    /**
     * @return bool
     */
    public function isShowValues(): bool
    {
        return $this->fieldGeneralSettings->isShowValues();
    }

    /**
     * @return bool
     */
    public function isEnableSearch(): bool
    {
        return $this->fieldGeneralSettings->isEnableSearch();
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->fieldGeneralSettings->isVisible();
    }

    /**
     * Get the phone format of the form field.
     *
     * @return string The phone format of the form field.
     */
    public function getPhoneFormat(): string
    {
        return $this->fieldGeneralSettings->getPhoneFormat();
    }

    /**
     * Set the phone format of the form field.
     *
     * @param string $format The phone format to set.
     */
    public function setPhoneFormat(string $format): void
    {
        $this->fieldGeneralSettings->setPhoneFormat($format);
    }
    /**
     * Get minimum value constraint.
     *
     * @return float|null
     */
    public function getMinValue(): ?float
    {
        return $this->fieldGeneralSettings->getMinValue();
    }

    /**
     * Set minimum value constraint.
     *
     * @param float|null $minValue
     */
    public function setMinValue(?float $minValue): void
    {
        $this->fieldGeneralSettings->setMinValue($minValue);
    }

    /**
     * Get maximum value constraint.
     *
     * @return float|null
     */
    public function getMaxValue(): ?float
    {
        return $this->fieldGeneralSettings->getMaxValue();
    }

    /**
     * Set maximum value constraint.
     *
     * @param float|null $maxValue
     */
    public function setMaxValue(?float $maxValue): void
    {
        $this->fieldGeneralSettings->setMaxValue($maxValue);
    }

    /**
     * Get step increment.
     * @return float
     */
    public function getStep(): float
    {
        return $this->fieldGeneralSettings->getStep();
    }

    /**
     * Set step value.
     * @param float $step
     */
    public function setStep(float $step): void
    {
        $this->fieldGeneralSettings->setStep($step);
    }

    /**
     * Get show grid value.
     * @return bool
     */
    public function getShowGrid(): bool
    {
        return $this->fieldGeneralSettings->getShowGrid();
    }

    /**
     * Set show grid value.
     * @param bool $showGrid
     */
    public function setShowGrid(bool $showGrid): void
    {
        $this->fieldGeneralSettings->setShowGrid($showGrid);
    }

    /**
     * Get number format key.
     * @return string
     */
    public function getNumberFormat(): string
    {
        return $this->fieldGeneralSettings->getNumberFormat();
    }

    /**
     * Set number format key.
     * @param string $numberFormat
     */
    public function setNumberFormat(string $numberFormat): void
    {
        $this->fieldGeneralSettings->setNumberFormat($numberFormat);
    }

    /**
     * Get the phone auto-detect status of the form field.
     *
     * @return bool The phone auto-detect status of the form field.
     */
    public function isPhoneAutoDetect(): bool
    {
        return $this->fieldGeneralSettings->isPhoneAutoDetect();
    }

    /**
     * Set the phone auto-detect status of the form field.
     *
     * @param bool $val The phone auto-detect status to set.
     */
    public function setPhoneAutoDetect(bool $val): void
    {
        $this->fieldGeneralSettings->setPhoneAutoDetect($val);
    }

    /**
     * Get the HTML content of the form field.
     *
     * @return string The HTML content.
     */
    public function getHtmlContent(): string
    {
        return $this->fieldGeneralSettings->getHtmlContent();
    }

    /**
     * Set the HTML content of the form field.
     *
     * @param string $htmlContent The HTML content to set.
     */
    public function setHtmlContent(string $htmlContent): void
    {
        $this->fieldGeneralSettings->setHtmlContent($htmlContent);
    }

    /**
     * Get showRatingText flag.
     *
     * @return bool
     */
    public function isShowRatingText(): bool
    {
        return $this->fieldGeneralSettings->isShowRatingText();
    }

    /**
     * Set showRatingText flag.
     *
     * @param bool $val
     */
    public function setShowRatingText(bool $val): void
    {
        $this->fieldGeneralSettings->setShowRatingText($val);
    }

    /**
     * Get the agreement text of the form field.
     *
     * @return string The agreement text.
     */
    public function getAgreementText(): string
    {
        return $this->fieldGeneralSettings->getAgreementText();
    }

    /**
     * Set the agreement text of the form field.
     *
     * @param string $agreementText The agreement text to set.
     */
    public function setAgreementText(string $agreementText): void
    {
        $this->fieldGeneralSettings->setAgreementText($agreementText);
    }

    /**
     * @return bool
     */
    public function isLimitRange(): bool
    {
        return $this->fieldGeneralSettings->isLimitRange();
    }

    /**
     * @param bool $limitRange
     */
    public function setLimitRange(bool $limitRange): void
    {
        $this->fieldGeneralSettings->setLimitRange($limitRange);
    }

    /**
     * @return array<string, bool|string>
     */
    public function toArray(): array
    {
        return $this->fieldGeneralSettings->toArray();
    }
}
