<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\ValueObjects\Field;

/**
 * Class FieldAdvancedSettings
 *
 * @package IvyForms\ValueObjects\Field
 */
final class FieldAdvancedSettings
{
    /**
     * @var string
     */
    public string $defaultValue;

    /**
     * @var bool
     */
    public bool $limitMaxLength;

    /**
     * @var int
     */
    public int $maxLength;

    /**
     * @var string
     */
    public string $labelPosition;

    /**
     * @var bool
     */
    public bool $noDuplicates;

    /**
     * @var string field prefix (e.g., https://)
     */
    private string $inputPrefix;

    /**
     * @var string field suffix (e.g., .com)
     */
    private string $inputSuffix;

    /**
     * @var string Type of rating icon (star, heart, etc.)
     */
    private string $ratingIcon;

    /**
     * @var FileUploadFieldAdvancedSettings
     */
    private FileUploadFieldAdvancedSettings $fileUploadSettings;

    /**
     * FieldAdvancedSettings constructor.
     *
     * @param string $defaultValue
     * @param bool $limitMaxLength
     * @param int $maxLength
     * @param string $labelPosition
     * @param bool $noDuplicates
     * @param string $inputPrefix
     * @param string $inputSuffix
     * @param string $ratingIcon
     * @param FileUploadFieldAdvancedSettings $fileUploadSettings
     */
    public function __construct(
        string $defaultValue,
        bool $limitMaxLength,
        int $maxLength,
        string $labelPosition,
        bool $noDuplicates,
        string $inputPrefix,
        string $inputSuffix,
        string $ratingIcon,
        FileUploadFieldAdvancedSettings $fileUploadSettings
    ) {
        $this->defaultValue = $defaultValue;
        $this->limitMaxLength = $limitMaxLength;
        $this->maxLength = $maxLength;
        $this->labelPosition = $labelPosition;
        $this->noDuplicates = $noDuplicates;
        $this->inputPrefix = $inputPrefix;
        $this->inputSuffix = $inputSuffix;
        $this->ratingIcon = $ratingIcon;
        $this->fileUploadSettings = $fileUploadSettings;
    }

    /**
     * Get the default value.
     *
     * @return string
     */
    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    /**
     * Set the default value.
     *
     * @param string $defaultValue
     */
    public function setDefaultValue(string $defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }

    /**
     * Check if maximum length limit is enabled.
     *
     * @return bool
     */
    public function isLimitMaxLength(): bool
    {
        return $this->limitMaxLength;
    }

    /**
     * Set the limit maximum length setting.
     *
     * @param bool $limitMaxLength
     */
    public function setLimitMaxLength(bool $limitMaxLength): void
    {
        $this->limitMaxLength = $limitMaxLength;
    }

    /**
     * Get the maximum length.
     *
     * @return int
     */
    public function getMaxLength(): int
    {
        return $this->maxLength;
    }

    /**
     * Set the maximum length setting.
     *
     * @param int $maxLength
     */
    public function setMaxLength(int $maxLength): void
    {
        $this->maxLength = $maxLength;
    }

    /**
     * Get the label position.
     *
     * @return string
     */
    public function getLabelPosition(): string
    {
        return $this->labelPosition;
    }

    /**
     * Set the label position setting.
     *
     * @param string $labelPosition
     */
    public function setLabelPosition(string $labelPosition): void
    {
        $this->labelPosition = $labelPosition;
    }

    /**
     * Check if duplicates are not allowed.
     *
     * @return bool
     */
    public function isNoDuplicates(): bool
    {
        return $this->noDuplicates;
    }

    /**
     * Set the noDuplicates setting.
     *
     * @param bool $noDuplicates
     */
    public function setNoDuplicates(bool $noDuplicates): void
    {
        $this->noDuplicates = $noDuplicates;
    }

    /**
     * Get the field prefix.
     *
     * @return string
     */
    public function getInputPrefix(): string
    {
        return $this->inputPrefix;
    }

    /**
     * Set the field prefix.
     *
     * @param string $prefix
     */
    public function setInputPrefix(string $prefix): void
    {
        $this->inputPrefix = $prefix;
    }

    /**
     * Get the field suffix.
     *
     * @return string
     */
    public function getInputSuffix(): string
    {
        return $this->inputSuffix;
    }

    /**
     * Set the field suffix.
     *
     * @param string $suffix
     */
    public function setInputSuffix(string $suffix): void
    {
        $this->inputSuffix = $suffix;
    }

    /**
     * Get the rating icon type.
     *
     * @return string
     */
    public function getRatingIcon(): string
    {
        return $this->ratingIcon;
    }

    /**
     * Set the rating icon type.
     *
     * @param string $ratingIcon
     */
    public function setRatingIcon(string $ratingIcon): void
    {
        $this->ratingIcon = $ratingIcon;
    }

    public function getFileUploadSettings(): FileUploadFieldAdvancedSettings
    {
        return $this->fileUploadSettings;
    }

    /**
     * Maximum file size in MB for file-upload fields (0 = server default).
     */
    public function getMaxFileSizeMb(): int
    {
        return $this->fileUploadSettings->getMaxFileSizeMb();
    }

    public function setMaxFileSizeMb(int $maxFileSizeMb): void
    {
        $this->fileUploadSettings->setMaxFileSizeMb($maxFileSizeMb);
    }

    /**
     * Target storage for uploads: ivyforms or media_library.
     */
    public function getSaveUploadsTo(): string
    {
        return $this->fileUploadSettings->getSaveUploadsTo();
    }

    public function setSaveUploadsTo(string $saveUploadsTo): void
    {
        $this->fileUploadSettings->setSaveUploadsTo($saveUploadsTo);
    }

    /**
     * @return array<int, string>
     */
    public function getAllowedFileExtensions(): array
    {
        return $this->fileUploadSettings->getAllowedFileExtensions();
    }

    /**
     * @param array<int, string> $allowedFileExtensions
     */
    public function setAllowedFileExtensions(array $allowedFileExtensions): void
    {
        $this->fileUploadSettings->setAllowedFileExtensions($allowedFileExtensions);
    }

    /**
     * Convert the advanced settings to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(
            [
                'defaultValue'    => $this->getDefaultValue(),
                'limitMaxLength'  => $this->isLimitMaxLength(),
                'maxLength'       => $this->getMaxLength(),
                'labelPosition'   => $this->getLabelPosition(),
                'noDuplicates'    => $this->isNoDuplicates(),
                'inputPrefix'     => $this->getInputPrefix(),
                'inputSuffix'     => $this->getInputSuffix(),
                'ratingIcon'      => $this->getRatingIcon(),
            ],
            $this->fileUploadSettings->toArray()
        );
    }
}
