<?php

namespace IvyForms\Factory\Field;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Helpers\NameFieldKeyHelper;
use IvyForms\Entity\Field\Field as FieldEntity;
use IvyForms\ValueObjects\Field\Field;
use IvyForms\ValueObjects\Field\FieldGeneralSettings;
use IvyForms\ValueObjects\Field\FieldOptions;
use IvyForms\ValueObjects\Field\FieldAdvancedSettings;
use IvyForms\ValueObjects\Field\FileUploadFieldAdvancedSettings;
use IvyForms\ValueObjects\Field\ConfirmationGeneralSettings;
use IvyForms\ValueObjects\Field\TimeFieldGeneralSettings;
use IvyForms\ValueObjects\Field\DateFieldGeneralSettings;

/**
 * Class FieldFactory
 *
 * @package IvyForms\Factory\Field
 */
class FieldFactory
{
    /**
     * @param array<string, mixed> $data
     *
     * @return FieldEntity
     *
     * @throws ValidationException
     */
    public static function create(array $data): FieldEntity
    {
        $settings = self::parseSettings($data['settings'] ?? null);

        if (!empty($settings)) {
            $data['hideLabel']                 = $settings['hideLabel'] ?? false;
            $data['readOnly']                  = $settings['readOnly'] ?? false;
            $data['description']               = $settings['description'] ?? '';
            $data['requiredMessage']           = $settings['requiredMessage'] ?? '';
            $data['cssClasses']                = $settings['cssClasses'] ?? '';
            $data['limitMaxLength']            = $settings['limitMaxLength'] ?? false;
            $data['limitRange']                = $settings['limitRange'] ?? false;
            $data['maxLength']                 = $settings['maxLength'] ?? 255;
            $data['labelPosition']             = $settings['labelPosition'] ?? 'default';
            $data['noDuplicates']              = $settings['noDuplicates'] ?? false;
            $data['shuffleOptions']            = $settings['shuffleOptions'] ?? false;
            $data['showValues']                = $settings['showValues'] ?? false;
            $data['enableSearch']              = $settings['enableSearch'] ?? false;
            $data['rows']                      = $settings['rows'] ?? 0;
            $data['confirmFieldEnabled']       = $settings['confirmFieldEnabled'] ?? false;
            $data['confirmFieldLabel']         = $settings['confirmFieldLabel'] ?? '';
            $data['confirmFieldPlaceholder']   = $settings['confirmFieldPlaceholder'] ?? '';
            $data['confirmFieldHideLabel']     = $settings['confirmFieldHideLabel'] ?? false;
            $data['phoneFormat']               = $settings['phoneFormat'] ?? '';
            $data['phoneAutoDetect']           = $settings['phoneAutoDetect'] ?? false;
            $data['minValue']                  = $settings['minValue'] ?? null;
            $data['maxValue']                  = $settings['maxValue'] ?? null;
            $data['step']                      = $settings['step'] ?? 1;
            $data['showGrid']                  = $settings['showGrid'] ?? false;
            $data['numberFormat']              = $settings['numberFormat'] ?? '';
            $data['inputPrefix']               = $settings['inputPrefix'] ?? '';
            $data['inputSuffix']               = $settings['inputSuffix'] ?? '';
            $data['timeFieldType']             = $settings['timeFieldType'] ?? '';
            $data['timeFormat']                = $settings['timeFormat'] ?? '';
            $data['minTimeValue']              = $settings['minTimeValue'] ?? '';
            $data['maxTimeValue']              = $settings['maxTimeValue'] ?? '';
            $data['dateFieldType']             = $settings['dateFieldType'] ?? '';
            $data['dateFormat']                = $settings['dateFormat'] ?? '';
            $data['minDateValue']              = $settings['minDateValue'] ?? '';
            $data['maxDateValue']              = $settings['maxDateValue'] ?? '';
            $data['showRatingText']            = $settings['showRatingText'] ?? false;
            $data['ratingIcon']                = $settings['ratingIcon'] ?? 'star';
            $data['htmlContent']               = $settings['htmlContent'] ?? '';
            $data['agreementText']             = $settings['agreementText'] ?? '';
            $data['limitDateRange']            = $settings['limitDateRange'] ?? false;
            $data['limitTimeRange']            = $settings['limitTimeRange'] ?? false;
            $data['maxFileSizeMb'] = isset($settings['maxFileSizeMb']) ? (int) $settings['maxFileSizeMb'] : 0;
            $data['saveUploadsTo'] = isset($settings['saveUploadsTo'])
                ? (string) $settings['saveUploadsTo']
                : 'ivyforms';
            if (!array_key_exists('allowedFileExtensions', $data) && isset($settings['allowedFileExtensions'])) {
                $data['allowedFileExtensions'] = $settings['allowedFileExtensions'];
            }
            // Grid layout: prioritize direct database columns over settings JSON
            // This ensures backward compatibility while preferring the dedicated columns
            $data['rowIndex']                  = $data['rowIndex'] ?? ($settings['rowIndex'] ?? 0);
            $data['columnIndex']               = $data['columnIndex'] ?? ($settings['columnIndex'] ?? 0);
            $data['width']                     = $data['width'] ?? ($settings['width'] ?? 100);
            $data['visible'] = array_key_exists('visible', $settings)
                ? filter_var($settings['visible'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
                : true;
            if ($data['visible'] === null) {
                $data['visible'] = true;
            }
            if (isset($settings['nameFieldType'])) {
                $data['nameFieldType'] = (string)$settings['nameFieldType'];
            }
            if (array_key_exists('subFieldIndex', $settings)) {
                $data['subFieldIndex'] = is_numeric($settings['subFieldIndex'])
                    ? (int)$settings['subFieldIndex']
                    : null;
            }
        }

        if (isset($data['nameFieldType'])) {
            $data['nameFieldType'] = (string)$data['nameFieldType'];
        }
        if (array_key_exists('subFieldIndex', $data)) {
            $data['subFieldIndex'] = is_numeric($data['subFieldIndex'])
                ? (int)$data['subFieldIndex']
                : null;
        }

        if (empty($data['nameFieldType'])) {
            $inferredNameFieldType = NameFieldKeyHelper::inferNameFieldKeyFromLabel($data['label'] ?? '');
            if ($inferredNameFieldType !== null) {
                $data['nameFieldType'] = $inferredNameFieldType;
            }
        }

        /**
         * Allows modification of the settings data before creating the Field entity.
         * This filter runs for both:
         * - Fields loaded from database (settings from JSON string)
         * - Fields from API (settings from direct properties)
         * @since 0.1.0
         *
         * @param mixed[] $data The current field data.
         * @param mixed[] $settings The settings array (from database or empty for API).
         * @return mixed[] The modified field data.
         */
        $data = apply_filters('ivyforms/field/factory/extract_settings', $data, $settings);

        $generalSettings = new FieldGeneralSettings(
            self::validateString($data['label'] ?? '', 255, 'label'),
            self::validateBool($data['required'] ?? false),
            self::validateString($data['placeholder'] ?? '', 255, 'placeholder'),
            self::validateBool($data['hideLabel'] ?? false),
            self::validateBool($data['readOnly'] ?? false),
            self::validateString($data['description'] ?? '', 1000, 'description'),
            self::validateString($data['requiredMessage'] ?? '', 255, 'requiredMessage'),
            self::validateString($data['cssClasses'] ?? '', 255, 'cssClasses'),
            self::validateBool($data['shuffleOptions'] ?? false),
            self::validateBool($data['showValues'] ?? false),
            self::validateBool($data['enableSearch'] ?? false),
            self::validatePhoneFormat($data['phoneFormat'] ?? ''),
            self::validateBool($data['phoneAutoDetect'] ?? false),
            self::validateNumberValue($data['minValue'] ?? null, 'minValue'),
            self::validateNumberValue($data['maxValue'] ?? null, 'maxValue'),
            self::validateStep($data['step'] ?? 1),
            self::validateBool($data['showGrid'] ?? false),
            self::validateNumberFormat($data['numberFormat'] ?? ''),
            new ConfirmationGeneralSettings(
                self::validateBool($data['confirmFieldEnabled'] ?? false),
                self::validateString(
                    $data['confirmFieldLabel'] ?? '',
                    255,
                    'confirmFieldLabel'
                ),
                self::validateString(
                    $data['confirmFieldPlaceholder'] ?? '',
                    255,
                    'confirmFieldPlaceholder'
                ),
                self::validateBool($data['confirmFieldHideLabel'] ?? false)
            ),
            new TimeFieldGeneralSettings(
                self::validateBool($data['limitTimeRange'] ?? false),
                self::validateString($data['timeFieldType'] ?? '', 50, 'timeFieldType'),
                self::validateString($data['timeFormat'] ?? '', 50, 'timeFormat'),
                isset($data['minTimeValue']) && $data['minTimeValue'] !== '' ?
                    self::validateString($data['minTimeValue'], 255, 'minTimeValue') : null,
                isset($data['maxTimeValue']) && $data['maxTimeValue'] !== '' ?
                    self::validateString($data['maxTimeValue'], 255, 'maxTimeValue') : null,
            ),
            new DateFieldGeneralSettings(
                self::validateBool($data['limitDateRange'] ?? false),
                self::validateString($data['dateFieldType'] ?? '', 50, 'dateFieldType'),
                self::validateString($data['dateFormat'] ?? '', 50, 'dateFormat'),
                isset($data['minDateValue']) && $data['minDateValue'] !== '' ?
                    self::validateString($data['minDateValue'], 255, 'minDateValue') : null,
                isset($data['maxDateValue']) && $data['maxDateValue'] !== '' ?
                    self::validateString($data['maxDateValue'], 255, 'maxDateValue') : null,
            ),
            self::validateBool($data['showRatingText'] ?? false),
            self::validateBool($data['visible'] ?? true),
            self::validateString($data['htmlContent'] ?? '', 4294967295, 'htmlContent'),
            self::validateString($data['agreementText'] ?? '', 1000, 'agreementText'),
            self::validateBool($data['limitRange'] ?? false),
        );
        $advancedSettings = new FieldAdvancedSettings(
            self::resolveDefaultValue($data),
            self::validateBool($data['limitMaxLength'] ?? false),
            (int)($data['maxLength'] ?? 255),
            self::validateString($data['labelPosition'] ?? '', 255, 'labelPosition'),
            self::validateBool($data['noDuplicates'] ?? false),
            self::validateString($data['inputPrefix'] ?? '', 255, 'inputPrefix'),
            self::validateString($data['inputSuffix'] ?? '', 255, 'inputSuffix'),
            self::validateString($data['ratingIcon'] ?? 'star', 50, 'ratingIcon'),
            new FileUploadFieldAdvancedSettings(
                (int) ($data['maxFileSizeMb'] ?? 0),
                (string) ($data['saveUploadsTo'] ?? 'ivyforms'),
                is_array($data['allowedFileExtensions'] ?? null)
                    ? $data['allowedFileExtensions']
                    : []
            )
        );
        $fieldOptions = new FieldOptions($data['options'] ?? []);

        $fieldObject = new Field(
            $data['id'] ?? 0,
            $data['formId'] ?? 0,
            $data['fieldIndex'] ?? 0,
            $data['type'] ?? '',
            $data['position'] ?? 0,
            $generalSettings,
            $fieldOptions,
            $advancedSettings,
            $data['parentId'] ?? null,
            $data['rows'] ?? 0,
            $data['rowIndex'] ?? 0,
            $data['columnIndex'] ?? 0,
            $data['width'] ?? 100,
            $data['pageId'] ?? null
        );

        /**
         * Allows setting of additional properties for the Field value object.
         * @since 0.1.0
         *
         * @param Field $fieldObject The current field data.
         * @param mixed[] $data The settings array from the database.
         * @return mixed[] The modified field data.
         */
        $fieldObject = apply_filters('ivyforms/field/value_object/set_properties', $fieldObject, $data);

        $field = new FieldEntity($fieldObject);

        if (isset($data['id'])) {
            $field->setId($data['id']);
        }

        if (isset($data['formId'])) {
            $field->setFormId($data['formId']);
        }

        if (isset($data['fieldIndex'])) {
            $field->setIndex($data['fieldIndex']);
        }

        if (isset($data['type'])) {
            $field->setType($data['type']);
        }

        if (isset($data['position'])) {
            $field->setPosition($data['position']);
        }

        if (isset($data['parentId'])) {
            $field->setParentId($data['parentId']);
        }

        if (isset($data['pageId'])) {
            $field->setPageId($data['pageId']);
        }

        if (isset($data['rows'])) {
            $field->setRows((int)$data['rows']);
        }

        /**
         * Allows setting of additional properties for the Field entity.
         * @since 0.1.0
         *
         * @param FieldEntity $field The current field data.
         * @param mixed[] $data The settings array from the database.
         * @return mixed[] The modified field data.
         */
        $field = apply_filters('ivyforms/field/entity/set_properties', $field, $data);

        if (!empty($data['nameFieldType'])) {
            $field->setAdditionalProperty('nameFieldType', (string)$data['nameFieldType']);
        }
        if (array_key_exists('subFieldIndex', $data) && $data['subFieldIndex'] !== null) {
            $field->setAdditionalProperty('subFieldIndex', (int)$data['subFieldIndex']);
        }

        return $field;
    }

    /**
     * @param mixed $rawSettings
     * @return array<string, mixed>
     */
    private static function parseSettings($rawSettings): array
    {
        if (is_array($rawSettings)) {
            return $rawSettings;
        }

        if (!is_string($rawSettings) || $rawSettings === '') {
            return [];
        }

        $decoded = json_decode($rawSettings, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Validate and convert a value to boolean.
     *
     * @param mixed $value The value to validate.
     *
     * @return bool The validated boolean value.
     */
    private static function validateBool($value): bool
    {
        return (bool)$value;
    }

    /**
     * Resolve and validate field default value (Pro may override for rich_text, etc.).
     *
     * @param array<string, mixed> $data
     *
     * @return string
     *
     * @throws ValidationException
     */
    private static function resolveDefaultValue(array $data): string
    {
        $filtered = apply_filters(
            'ivyforms/field/validate_default_value',
            null,
            $data['defaultValue'] ?? '',
            $data
        );

        if (is_string($filtered)) {
            return $filtered;
        }

        return self::validateString($data['defaultValue'] ?? '', 255, 'defaultValue');
    }

    /**
     * Validate a string's length.
     *
     * @param mixed  $value     The value to validate.
     * @param int    $maxLength The maximum allowed length.
     * @param string $fieldName The name of the field (for error messages).
     *
     * @return string The validated string.
     *
     * @throws ValidationException If the string exceeds the maximum length.
     */
    private static function validateString($value, int $maxLength, string $fieldName): string
    {
        $value = (string)$value;
        if (strlen($value) > $maxLength) {
            throw new ValidationException(
                sprintf(
                    esc_html__('%1$s must be at most %2$d characters.', 'ivyforms'),
                    esc_html($fieldName),
                    $maxLength
                )
            );
        }
        return $value;
    }
    /**
     * Validate and coerce numeric range values (minValue / maxValue).
     * If value is null/empty/non-numeric => returns 0.0.
     * Performs cross-check when the counterpart is already set.
     *
     * @param mixed  $value
     * @param string $fieldName 'minValue' | 'maxValue'
     * @return float | null
     * @throws ValidationException
     */
    private static function validateNumberValue($value, string $fieldName): ?float
    {
        if ($value === '' || $value === null) {
            return null;
        }
        if (!is_numeric($value)) {
            throw new ValidationException(
                sprintf(
                    esc_html__('%1$s must be a number.', 'ivyforms'),
                    esc_html($fieldName)
                )
            );
        }
        return (float)$value;
    }

    /**
     * Validate step (positive number >0)
     * @param float $value
     * @return float
     * @throws ValidationException
     */
    private static function validateStep(float $value): float
    {
        if ($value <= 0) {
            throw new ValidationException(esc_html__('step must be a positive number (>0).', 'ivyforms'));
        }
        return (float)$value;
    }

    /**
     * Validates the number format.
     *
     * @param string $value
     *
     * @return string
     *
     */
    private static function validateNumberFormat(string $value): string
    {
        $allowed = ['', 'us_decimal', 'us_int', 'eu_decimal', 'eu_int'];
        if (!in_array($value, $allowed, true)) {
            return '';
        }
        return $value;
    }

    private static function validatePhoneFormat(string $format): string
    {
        $allowed = ['international','national','e164'];
        $lower = strtolower($format);
        if (!in_array($lower, $allowed, true)) {
            return 'international';
        }
        return $lower;
    }
}
