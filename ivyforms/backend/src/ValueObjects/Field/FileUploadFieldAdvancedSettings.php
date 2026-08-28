<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

declare(strict_types=1);

namespace IvyForms\ValueObjects\Field;

/**
 * File-upload-specific advanced field settings.
 */
final class FileUploadFieldAdvancedSettings
{
    /**
     * Max upload size per file (MB). 0 uses server maximum.
     */
    private int $maxFileSizeMb;

    /**
     * Where uploads are stored: ivyforms (uploads dir) or media_library.
     */
    private string $saveUploadsTo;

    /**
     * Allowed extensions without dot (e.g. png, jpg). Empty = all types.
     *
     * @var array<int, string>
     */
    private array $allowedFileExtensions;

    /**
     * @param array<int, string> $allowedFileExtensions
     */
    public function __construct(
        int $maxFileSizeMb = 0,
        string $saveUploadsTo = 'ivyforms',
        array $allowedFileExtensions = []
    ) {
        $this->maxFileSizeMb = $maxFileSizeMb;
        $this->saveUploadsTo = $saveUploadsTo;
        $this->allowedFileExtensions = $allowedFileExtensions;
    }

    public function getMaxFileSizeMb(): int
    {
        return $this->maxFileSizeMb;
    }

    public function setMaxFileSizeMb(int $maxFileSizeMb): void
    {
        $this->maxFileSizeMb = $maxFileSizeMb;
    }

    public function getSaveUploadsTo(): string
    {
        return $this->saveUploadsTo;
    }

    public function setSaveUploadsTo(string $saveUploadsTo): void
    {
        $this->saveUploadsTo = $saveUploadsTo;
    }

    /**
     * @return array<int, string>
     */
    public function getAllowedFileExtensions(): array
    {
        return $this->allowedFileExtensions;
    }

    /**
     * @param array<int, string> $allowedFileExtensions
     */
    public function setAllowedFileExtensions(array $allowedFileExtensions): void
    {
        $this->allowedFileExtensions = $allowedFileExtensions;
    }

    /**
     * @return array<string, int|string|array<int, string>>
     */
    public function toArray(): array
    {
        return [
            'maxFileSizeMb' => $this->getMaxFileSizeMb(),
            'saveUploadsTo' => $this->getSaveUploadsTo(),
            'allowedFileExtensions' => $this->getAllowedFileExtensions(),
        ];
    }
}
