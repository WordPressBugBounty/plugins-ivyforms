<?php

namespace IvyForms\Services\Media;

use IvyForms\Services\Translations\BackendStrings;

/**
 * Image Service
 *
 * Handles image processing and upload operations.
 * Images are processed when forms/confirmations/notifications are saved.
 */
class ImageService
{
    /**
     * Base upload directory
     */
    private const BASE_UPLOAD_DIR = 'ivyforms';

    /**
     * Process and upload base64 images found in content
     *
     * @param string $content Content that may contain base64 images
     * @param string $folder Subfolder name (confirmation, notification, etc.). Defaults to 'temp' if not specified.
     * @return string Content with base64 images replaced by uploaded URLs
     */
    public function processImagesInContent(string $content, string $folder = 'temp'): string
    {
        if (empty($content)) {
            return $content;
        }

        // Find all base64 images in img src attributes
        $pattern = '/<img[^>]+src=["\']data:image\/([^;]+);base64,([^"\']+)["\']/i';

        $content = preg_replace_callback(
            $pattern,
            function ($matches) use ($folder) {
                // IGNORE $extension from <img> tag
                $base64Data = $matches[2];

                // Upload the image (extension is always determined by MIME)
                $result = $this->uploadBase64Image($base64Data, $folder);

                if ($result['success'] && isset($result['url'])) {
                    // Replace base64 with uploaded URL
                    // Replace only the data:image/...;base64,... part
                    $original = $matches[0];
                    $replace = preg_replace(
                        '/src=["\']data:image\/[^;]+;base64,[^"\']+["\']/',
                        'src="' . $result['url'] . '"',
                        $original
                    );
                    return $replace;
                }

                // If upload failed, keep original
                return $matches[0];
            },
            $content
        );

        return $content;
    }

    /**
     * Upload base64 image to WordPress uploads directory
     *
     * @param string $base64Data Base64 encoded image data (without data: prefix)
     * @param string $folder Subfolder name (confirmation, notification, etc.)
     * @param string $extension (IGNORED, always determined by MIME)
     * @param string $fileName Optional custom filename without extension
     * @return array<string, bool|string> Array with 'success' bool and 'url' or 'error' keys
     */
    public function uploadBase64Image(
        string $base64Data,
        string $folder,
        string $extension = 'png', // Ignored
        string $fileName = ''
    ): array {
        // Validate base64 data
        $decodedData = base64_decode($base64Data, true);
        if ($decodedData === false) {
            return [
                'success' => false,
                'error' => BackendStrings::getExceptionStrings()['invalid_base64_data'],
            ];
        }

        // Verify and validate image
        $validationResult = $this->validateImage($decodedData);
        if (!$validationResult['success']) {
            return $validationResult;
        }

        // Use extension derived from actual MIME type ONLY
        $extension = $validationResult['extension'];

        // Create upload directory if it doesn't exist
        $uploadDir = $this->getUploadDirectory($folder);
        if (!wp_mkdir_p($uploadDir)) {
            return [
                'success' => false,
                'error' => BackendStrings::getExceptionStrings()['failed_create_upload_dir'],
            ];
        }

        // Process and prepare filename
        $fileName = $this->prepareFileName($fileName, $extension);

        // Full file path and handle conflicts
        $filePath = $uploadDir . '/' . $fileName;
        $fileName = $this->resolveFileConflict($filePath, $fileName);
        $filePath = $uploadDir . '/' . $fileName;

        // Write file
        if (!$this->writeImageFile($filePath, $decodedData)) {
            return [
                'success' => false,
                'error' => BackendStrings::getExceptionStrings()['failed_write_file'],
            ];
        }

        // Get full URL
        $fullUrl = $this->getFullUrl($folder, $fileName);

        return [
            'success' => true,
            'url' => $fullUrl,
            'fileName' => $fileName,
            'folder' => $folder,
        ];
    }

    /**
     * Validate image data
     *
     * @param string $decodedData
     * @return array<string, bool|string>
     */
    private function validateImage(string $decodedData): array
    {
        $imageInfo = getimagesizefromstring($decodedData);
        if ($imageInfo === false) {
            return [
                'success' => false,
                'error' => BackendStrings::getExceptionStrings()['invalid_image_data'],
            ];
        }

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        $detectedMimeType = $imageInfo['mime'];
        if (!array_key_exists($detectedMimeType, $allowedMimeTypes)) {
            return [
                'success' => false,
                'error' => BackendStrings::getExceptionStrings()['unsupported_image_type'] . $detectedMimeType,
            ];
        }

        return ['success' => true, 'extension' => $allowedMimeTypes[$detectedMimeType]];
    }

    /**
     * Prepare and sanitize filename
     *
     * @param string $fileName
     * @param string $extension
     * @return string
     */
    private function prepareFileName(string $fileName, string $extension): string
    {
        if (empty($fileName)) {
            $fileName = 'image-' . time() . '-' . uniqid();
        }

        $fileName = sanitize_file_name($fileName);
        if (empty($fileName)) {
            $fileName = 'image-' . time() . '-' . uniqid();
        }

        if (strpos($fileName, '.') === false) {
            $fileName .= '.' . $extension;
        }

        return $fileName;
    }

    /**
     * Resolve file name conflicts by appending counter
     *
     * @param string $filePath
     * @param string $fileName
     * @return string
     */
    private function resolveFileConflict(string $filePath, string $fileName): string
    {
        if (!file_exists($filePath)) {
            return $fileName;
        }

        $counter = 1;
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $uploadDir = dirname($filePath);

        while (file_exists($uploadDir . '/' . $fileName)) {
            $fileName = $baseName . '-' . $counter . '.' . $ext;
            $counter++;
        }

        return $fileName;
    }

    /**
     * Write image file to disk with proper permissions
     *
     * @param string $filePath
     * @param string $decodedData
     * @return bool
     */
    private function writeImageFile(string $filePath, string $decodedData): bool
    {
        $bytesWritten = file_put_contents($filePath, $decodedData);
        return $bytesWritten !== false;
    }

    /**
     * Get the full upload directory path for a specific folder
     *
     * @param string $folder Subfolder name
     * @return string Full directory path
     */
    private function getUploadDirectory(string $folder): string
    {
        $wpUploadDir = wp_upload_dir();
        $baseDir = $wpUploadDir['basedir'];

        // Sanitize folder name
        $folder = sanitize_file_name($folder);

        return $baseDir . '/' . self::BASE_UPLOAD_DIR . '/' . $folder;
    }

    /**
     * Get the full URL to an uploaded image
     *
     * @param string $folder Subfolder name
     * @param string $fileName Filename
     * @return string Full URL
     */
    public function getFullUrl(string $folder, string $fileName): string
    {
        $wpUploadDir = wp_upload_dir();
        $baseUrl = $wpUploadDir['baseurl'];

        return $baseUrl . '/' . self::BASE_UPLOAD_DIR . '/' . $folder . '/' . $fileName;
    }

    /**
     * Delete uploaded image
     *
     * @param string $folder Subfolder name
     * @param string $fileName Filename
     * @return bool True if deleted, false otherwise
     */
    public function deleteImage(string $folder, string $fileName): bool
    {
        $uploadDir = $this->getUploadDirectory($folder);
        $filePath = $uploadDir . '/' . $fileName;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}
