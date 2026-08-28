<?php

namespace IvyForms\Factory\FieldOptions;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\FieldOptions\FieldOptions as FieldOptionsEntity;
use IvyForms\ValueObjects\FieldOptions\FieldOptions;

class FieldOptionsFactory
{
    /**
     * @param array<string, mixed> $data
     *
     * @return  FieldOptionsEntity
     * @throws ValidationException
     */
    public static function create(array $data): FieldOptionsEntity
    {
        $fieldOptionsObject = new FieldOptions(
            $data['id'] ?? 0,
            $data['fieldId'],
            $data['label'],
            $data['value'],
            $data['isDefault'] ?? false,
            $data['position'] ?? 1
        );

        $fieldOptions = new FieldOptionsEntity($fieldOptionsObject);

        if (isset($data['id'])) {
            $fieldOptions->setId($data['id']);
        }
        if (isset($data['fieldId'])) {
            $fieldOptions->setFieldId($data['fieldId']);
        }
        if (isset($data['label'])) {
            $fieldOptions->setLabel($data['label']);
        }
        if (isset($data['value'])) {
            $fieldOptions->setValue($data['value']);
        }
        if (isset($data['isDefault'])) {
            $fieldOptions->setDefault($data['isDefault']);
        }
        if (isset($data['position'])) {
            $fieldOptions->setPosition($data['position']);
        }

        return $fieldOptions;
    }
}
