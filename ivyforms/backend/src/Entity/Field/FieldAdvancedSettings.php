<?php

namespace IvyForms\Entity\Field;

use IvyForms\ValueObjects\Field\FieldAdvancedSettings as FieldAdvancedSettingsVO;
use IvyForms\ValueObjects\Field\FileUploadFieldAdvancedSettings;

/**
 * Class fieldAdvancedSettings
 *
 * Entity wrapper for fieldAdvancedSettings ValueObject.
 */
class FieldAdvancedSettings
{
    private FieldAdvancedSettingsVO $fieldAdvancedSettings;

    /**
     * @param FieldAdvancedSettingsVO $fieldAdvancedSettings
     */
    public function __construct(FieldAdvancedSettingsVO $fieldAdvancedSettings)
    {
        $this->fieldAdvancedSettings = $fieldAdvancedSettings;
    }

    /**
     * Check if maximum length limit is enabled.
     *
     * @return bool
     */
    public function isLimitMaxLength(): bool
    {
        return $this->fieldAdvancedSettings->isLimitMaxLength();
    }

    /**
     * Set the limit maximum length setting.
     *
     * @param bool $limitMaxLength
     */
    public function setLimitMaxLength(bool $limitMaxLength): void
    {
        $this->fieldAdvancedSettings->setLimitMaxLength($limitMaxLength);
    }

    /**
     * Get the maximum length.
     *
     * @return int
     */
    public function getMaxLength(): int
    {
        return $this->fieldAdvancedSettings->getMaxLength();
    }

    /**
     * Set the maximum length setting.
     *
     * @param int $maxLength
     */
    public function setMaxLength(int $maxLength): void
    {
        $this->fieldAdvancedSettings->setMaxLength($maxLength);
    }

    /**
     * Get the label position.
     *
     * @return string
     */
    public function getLabelPosition(): string
    {
        return $this->fieldAdvancedSettings->getLabelPosition();
    }

    /**
     * Set the label position setting.
     *
     * @param string $labelPosition
     */
    public function setLabelPosition(string $labelPosition): void
    {
        $this->fieldAdvancedSettings->setLabelPosition($labelPosition);
    }

    /**
     * Check if no duplicates is enabled.
     *
     * @return bool
     */
    public function isNoDuplicates(): bool
    {
        return $this->fieldAdvancedSettings->isNoDuplicates();
    }

    /**
     * Set the no duplicates setting.
     *
     * @param bool $noDuplicates
     */
    public function setNoDuplicates(bool $noDuplicates): void
    {
        $this->fieldAdvancedSettings->setNoDuplicates($noDuplicates);
    }

    /**
     * Get the default value of the form field.
     *
     * @return string The default value of the form field.
     */
    public function getDefaultValue(): string
    {
        return $this->fieldAdvancedSettings->getDefaultValue();
    }

    /**
     * Set the default value of the form field.
     *
     * @param string $defaultValue The default value to set.
     */
    public function setDefaultValue(string $defaultValue): void
    {
        $this->fieldAdvancedSettings->setDefaultValue($defaultValue);
    }

    /**
     * Get the field prefix.
     *
     * @return string
     */
    public function getInputPrefix(): string
    {
        return $this->fieldAdvancedSettings->getInputPrefix();
    }

    /**
     * Set the field prefix.
     *
     * @param string $prefix
     */
    public function setInputPrefix(string $prefix): void
    {
        $this->fieldAdvancedSettings->setInputPrefix($prefix);
    }

    /**
     * Get the field suffix.
     *
     * @return string
     */
    public function getInputSuffix(): string
    {
        return $this->fieldAdvancedSettings->getInputSuffix();
    }

    /**
     * Set the field suffix.
     *
     * @param string $suffix
     */
    public function setInputSuffix(string $suffix): void
    {
        $this->fieldAdvancedSettings->setInputSuffix($suffix);
    }

    public function getFileUploadSettings(): FileUploadFieldAdvancedSettings
    {
        return $this->fieldAdvancedSettings->getFileUploadSettings();
    }

    public function getMaxFileSizeMb(): int
    {
        return $this->fieldAdvancedSettings->getMaxFileSizeMb();
    }

    public function setMaxFileSizeMb(int $maxFileSizeMb): void
    {
        $this->fieldAdvancedSettings->setMaxFileSizeMb($maxFileSizeMb);
    }

    public function getSaveUploadsTo(): string
    {
        return $this->fieldAdvancedSettings->getSaveUploadsTo();
    }

    public function setSaveUploadsTo(string $saveUploadsTo): void
    {
        $this->fieldAdvancedSettings->setSaveUploadsTo($saveUploadsTo);
    }

    /**
     * @return array<int, string>
     */
    public function getAllowedFileExtensions(): array
    {
        return $this->fieldAdvancedSettings->getAllowedFileExtensions();
    }

    /**
     * @param array<int, string> $allowedFileExtensions
     */
    public function setAllowedFileExtensions(array $allowedFileExtensions): void
    {
        $this->fieldAdvancedSettings->setAllowedFileExtensions($allowedFileExtensions);
    }

    /**
     * @return array<string, bool|string>
     */
    public function toArray(): array
    {
        return $this->fieldAdvancedSettings->toArray();
    }
}
