<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\ValueObjects\Field;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\ValueObjects\Field\ConfirmationGeneralSettings;
use IvyForms\ValueObjects\Field\TimeFieldGeneralSettings;
use IvyForms\ValueObjects\Field\DateFieldGeneralSettings;

/**
 * Class FieldGeneralSettings
 *
 * @SuppressWarnings(PHPMD)
 * Encapsulates general settings for a field.
 */
final class FieldGeneralSettings
{
    /**
     * @var string
     */
    private string $label;
    /**
     * @var bool
     */
    private bool $required;
    /**
     * @var string
     */
    private string $placeholder;
    /**
     * @var bool
     */
    private bool $hideLabel;
    /**
     * @var bool
     */
    private bool $readOnly;
    /**
     * @var string
     */
    private string $description;
    /**
     * @var string
     */
    private string $requiredMessage;
    /**
     * @var string
     */
    private string $cssClasses;
    /**
     * @var bool
     */
    private bool $shuffleOptions;
    /**
     * @var bool
     */
    private bool $showValues;
    /**
     * @var bool
     */
    private bool $enableSearch;
    /**
     * @var bool
     */
    private bool $visible;
    /**
     * @var string
     */
    private string $phoneFormat;
    /**
     * @var bool
     */
    private bool $phoneAutoDetect;
    /**
     * @var float|null
     */
    public ?float $minValue;
    /**
     * @var float|null
     */
    public ?float $maxValue;
    /**
     * @var float Step increment for number fields
     */
    public float $step;
    /**
     * @var bool Show grid marks on slider
     */
    public bool $showGrid;
    /**
     * @var string Number format key
     */
    public string $numberFormat;
    /**
     * Confirmation settings grouped in a dedicated value object.
     * @var ConfirmationGeneralSettings
     */
    private ConfirmationGeneralSettings $confirmationSettings;
    /**
     * Time settings grouped in a dedicated value object.
     * @var TimeFieldGeneralSettings
     */
    private TimeFieldGeneralSettings $timeSettings;
    /**
     * Date settings grouped in a dedicated value object.
     * @var DateFieldGeneralSettings
     */
    private DateFieldGeneralSettings $dateSettings;
    /**
     * Whether to show text labels alongside rating.
     * @var bool
     */
    private bool $showRatingText;
    /**
     * @var string HTML content for HTML field type
     */
    private string $htmlContent;
    /**
     * @var string Agreement text for GDPR field type
     */
    private string $agreementText;
    /**
     * @var bool
     */
    private bool $limitRange;

    /**
     * Public constructor
     *
     * @param string $label
     * @param bool $required
     * @param string $placeholder
     * @param bool $hideLabel
     * @param bool $readOnly
     * @param string $description
     * @param string $requiredMessage
     * @param string $cssClasses
     * @param bool $shuffleOptions
     * @param bool $showValues
     * @param bool $enableSearch
     * @param string $phoneFormat
     * @param bool $phoneAutoDetect
     * @param float|null $minValue
     * @param float|null $maxValue
     * @param float $step
     * @param bool $showGrid
     * @param string $numberFormat
     * @param ConfirmationGeneralSettings $confirmationSettings
     * @param TimeFieldGeneralSettings $timeSettings
     * @param DateFieldGeneralSettings $dateSettings
     * @param bool $visible
     * @param bool $showRatingText
     * @param string $htmlContent
     * @param bool $limitRange
     */
    /**
     * @SuppressWarnings("ExcessiveParameterList")
     * @SuppressWarnings("BooleanArgumentFlag")
     */
    public function __construct(
        string $label,
        bool $required,
        string $placeholder,
        bool $hideLabel,
        bool $readOnly,
        string $description,
        string $requiredMessage,
        string $cssClasses,
        bool $shuffleOptions,
        bool $showValues,
        bool $enableSearch,
        string $phoneFormat,
        bool $phoneAutoDetect,
        ?float $minValue,
        ?float $maxValue,
        float $step,
        bool $showGrid,
        string $numberFormat,
        ConfirmationGeneralSettings $confirmationSettings,
        TimeFieldGeneralSettings $timeSettings,
        DateFieldGeneralSettings $dateSettings,
        bool $showRatingText,
        bool $visible = true,
        string $htmlContent = '',
        string $agreementText = '',
        bool $limitRange = false
    ) {
        $this->label = $label;
        $this->required = $required;
        $this->placeholder = $placeholder;
        $this->hideLabel = $hideLabel;
        $this->readOnly = $readOnly;
        $this->description = $description;
        $this->requiredMessage = $requiredMessage;
        $this->cssClasses = $cssClasses;
        $this->shuffleOptions = $shuffleOptions;
        $this->showValues = $showValues;
        $this->enableSearch = $enableSearch;
        $this->phoneFormat = $phoneFormat;
        $this->phoneAutoDetect = $phoneAutoDetect;
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
        $this->step = $step;
        $this->showGrid = $showGrid;
        $this->numberFormat = $numberFormat;
        $this->visible = $visible;
        $this->confirmationSettings = $confirmationSettings;
        $this->timeSettings = $timeSettings;
        $this->dateSettings = $dateSettings;
        $this->htmlContent = $htmlContent;
        $this->showRatingText = $showRatingText;
        $this->agreementText = $agreementText;
        $this->limitRange = $limitRange;
    }

    /**
     * Get the label.
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get the placeholder.
     * @return string
     */
    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    /**
     * Get the description.
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the required message.
     * @return string
     */
    public function getRequiredMessage(): string
    {
        return $this->requiredMessage;
    }

    /**
     * Get the CSS classes.
     * @return string
     */
    public function getCssClasses(): string
    {
        return $this->cssClasses;
    }

    /**
     * Get hideLabel.
     * @return bool
     */
    public function isHideLabel(): bool
    {
        return $this->hideLabel;
    }

    /**
     * Get readOnly.
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * Get required.
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Get shuffleOptions.
     * @return bool
     */
    public function isShuffleOptions(): bool
    {
        return $this->shuffleOptions;
    }

    /**
     * Get showValues.
     * @return bool
     */
    public function isShowValues(): bool
    {
        return $this->showValues;
    }

    /**
     * Get enableSearch.
     * @return bool
     */
    public function isEnableSearch(): bool
    {
        return $this->enableSearch;
    }

    /**
     * Get visible.
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Accessor for confirmation settings value object.
     * @return ConfirmationGeneralSettings
     */
    public function getConfirmationSettings(): ConfirmationGeneralSettings
    {
        return $this->confirmationSettings;
    }

    /**
     * Accessor for time settings value object.
     * @return TimeFieldGeneralSettings
     */
    public function getTimeSettings(): TimeFieldGeneralSettings
    {
        return $this->timeSettings;
    }

    /**
     * Accessor for date settings value object.
     * @return DateFieldGeneralSettings
     */
    public function getDateSettings(): DateFieldGeneralSettings
    {
        return $this->dateSettings;
    }

    /**
     * Get the phone format.
     *
     * @return string
     */
    public function getPhoneFormat(): string
    {
        return $this->phoneFormat;
    }

    /**
     * Set the phone format.
     *
     * @param string $format
     */
    public function setPhoneFormat(string $format): void
    {
        $this->phoneFormat = $format;
    }

    /**
     * Get the phone auto-detect setting.
     *
     * @return bool
     */
    public function isPhoneAutoDetect(): bool
    {
        return $this->phoneAutoDetect;
    }

    /**
     * Set the phone auto-detect setting.
     *
     * @param bool|null $val
     */
    public function setPhoneAutoDetect(?bool $val): void
    {
        $this->phoneAutoDetect = (bool)$val;
    }
    /**
     * Get the minimum value.
     *
     * @return float|null
     */
    public function getMinValue(): ?float
    {
        return $this->minValue;
    }

    /**
     * Set the minimum value.
     *
     * @param float|null $minValue
     */
    public function setMinValue(?float $minValue): void
    {
        $this->minValue = $minValue;
    }

    /**
     * Get the maximum value.
     *
     * @return float|null
     */
    public function getMaxValue(): ?float
    {
        return $this->maxValue;
    }

    /**
     * Set the maximum value.
     *
     * @param float|null $maxValue
     */
    public function setMaxValue(?float $maxValue): void
    {
        $this->maxValue = $maxValue;
    }

    /**
     * Get the step value.
     *
     * @return float
     */
    public function getStep(): float
    {
        return $this->step;
    }

    /**
     * Set the step value.
     *
     * @param float $step
     */
    public function setStep(float $step): void
    {
        $this->step = $step;
    }

    /**
     * Get the show grid value.
     *
     * @return bool
     */
    public function getShowGrid(): bool
    {
        return $this->showGrid;
    }

    /**
     * Set the show grid value.
     *
     * @param bool $showGrid
     */
    public function setShowGrid(bool $showGrid): void
    {
        $this->showGrid = $showGrid;
    }

    /**
     * Get the number format.
     *
     * @return string
     */
    public function getNumberFormat(): string
    {
        return $this->numberFormat;
    }

    /**
     * Set the number format.
     *
     * @param string $numberFormat
     */
    public function setNumberFormat(string $numberFormat): void
    {
        $this->numberFormat = $numberFormat;
    }

    /**
     * Get the HTML content.
     * @return string
     */
    public function getHtmlContent(): string
    {
        return $this->htmlContent;
    }

    /**
     * Set the HTML content.
     *
     * @param string $htmlContent
     */
    public function setHtmlContent(string $htmlContent): void
    {
        $this->htmlContent = $htmlContent;
    }

    /**
     * Get showRatingText flag.
     * @return bool
     */
    public function isShowRatingText(): bool
    {
        return $this->showRatingText;
    }

    /**
     * Set showRatingText flag.
     *
     * @param bool|null $val
     */
    public function setShowRatingText(?bool $val): void
    {
        $this->showRatingText = (bool)$val;
    }

    /**
     * Get the agreement text.
     * @return string
     */
    public function getAgreementText(): string
    {
        return $this->agreementText;
    }

    /**
     * Set the agreement text.
     *
     * @param string $agreementText
     */
    public function setAgreementText(string $agreementText): void
    {
        $this->agreementText = $agreementText;
    }

    /**
     * Get limitRange.
     * @return bool
     */
    public function isLimitRange(): bool
    {
        return $this->limitRange;
    }

    /**
     * Set limitRange.
     * @param bool $limitRange
     */
    public function setLimitRange(bool $limitRange): void
    {
        $this->limitRange = $limitRange;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(
            [
                'label'           => $this->getLabel(),
                'required'        => $this->isRequired(),
                'placeholder'     => $this->getPlaceholder(),
                'hideLabel'       => $this->isHideLabel(),
                'readOnly'        => $this->isReadOnly(),
                'description'     => $this->getDescription(),
                'requiredMessage' => $this->getRequiredMessage(),
                'cssClasses'      => $this->getCssClasses(),
                'shuffleOptions'  => $this->isShuffleOptions(),
                'showValues'      => $this->isShowValues(),
                'enableSearch'    => $this->isEnableSearch(),
                'phoneFormat'     => $this->getPhoneFormat(),
                'phoneAutoDetect' => $this->isPhoneAutoDetect(),
                'minValue'        => $this->getMinValue(),
                'maxValue'        => $this->getMaxValue(),
                'step'            => $this->getStep(),
                'showGrid'        => $this->getShowGrid(),
                'numberFormat'    => $this->getNumberFormat(),
                'visible'         => $this->isVisible(),
                'showRatingText'  => $this->isShowRatingText(),
                'htmlContent'     => $this->getHtmlContent(),
                'agreementText'   => $this->getAgreementText(),
                'limitRange'      => $this->isLimitRange(),
            ],
            $this->confirmationSettings->toArray(),
            $this->timeSettings->toArray(),
            $this->dateSettings->toArray()
        );
    }
}
