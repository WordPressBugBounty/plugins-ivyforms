<?php

namespace IvyForms\Services\Field;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Entity\Field\Field;
use IvyForms\Services\Translations\BackendStrings;

final class FieldType
{
    /**
     * Base allow-list of field types.
     */
    private const BASE_ALLOWED_TYPES = [
        'email',
        'text',
        'textarea',
        'number',
        'slider',
        'phone',
        'website',
        'recaptcha',
        'turnstile',
        'hcaptcha',
        'html',
        'name',
        'radio',
        'checkbox',
        'gdpr',
        'address',
        'select',
        'multi-select',
        'time',
        'date',
        'rating',
        'file-upload',
        'product',
        'quantity',
        'total',
        // Layout / structural fields: carry no submission value but exist in stored
        // form definitions, so they must pass type validation on save/submit/add-entry.
        'section',
        'page',
    ];

    /**
     * Get the list of allowed field types
     *
     * @return array<string>
     */
    public static function getAllowedTypes(): array
    {
        $baseTypes = self::BASE_ALLOWED_TYPES;
        /**
         * Allows modification of the allowed field types.
         * @since 0.1.0
         *
         * @param mixed[] $baseTypes The current list of allowed field types.
         * @return mixed[] The modified list of allowed field types.
         */
        $types = apply_filters('ivyforms/field/filter_allowed_types', $baseTypes);

        // Normalize: ensure array of unique strings
        if (!($types)) {
            return self::BASE_ALLOWED_TYPES;
        }
        $filtered = [];
        foreach ($types as $t) {
            if ($t !== '') {
                $filtered[$t] = true;
            }
        }

        return array_keys($filtered);
    }

    /**
     * Check if a given type is valid
     *
     * @param string $type
     * @return bool
     */
    public static function isValid(string $type): bool
    {
        return in_array($type, self::getAllowedTypes(), true);
    }

    /**
     * @param Field[]|mixed[] $formFields
     * @throws InvalidArgumentException
     */
    public static function validateFields(array $formFields): void
    {
        self::assertFieldsAllowed($formFields, self::getAllowedTypes());
    }

    /**
     * @param Field|array<string, mixed> $field
     */
    private static function resolveFieldType($field): ?string
    {
        return $field instanceof Field ? $field->getType() : ($field['type'] ?? null);
    }

    /**
     * @param Field[]|mixed[] $formFields
     * @param string[] $allowedTypes
     * @throws InvalidArgumentException
     */
    private static function assertFieldsAllowed(array $formFields, array $allowedTypes): void
    {
        foreach ($formFields as $field) {
            $fieldType = self::resolveFieldType($field);

            if ($fieldType === null) {
                throw new InvalidArgumentException(
                    BackendStrings::getExceptionStrings()['invalid_field_type_not_found']
                );
            }

            if (!in_array($fieldType, $allowedTypes, true)) {
                throw new InvalidArgumentException(
                    sprintf(
                        BackendStrings::getExceptionStrings()['invalid_field_type_with_value'],
                        $fieldType
                    )
                );
            }
        }
    }
}
