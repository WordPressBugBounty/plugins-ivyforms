<?php

namespace IvyForms\Factory\Confirmation;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Entity\Confirmation\Confirmation as ConfirmationEntity;
use IvyForms\Services\Translations\BackendStrings;
use IvyForms\ValueObjects\Confirmation\Confirmation;

/**
 * Class ConfirmationFactory
 *
 * @package IvyForms\Factory\Confirmation
 */
class ConfirmationFactory
{
    /**
     * Create a ConfirmationEntity from an array of data.
     *
     * @param array<string, mixed> $data
     *
     * @return ConfirmationEntity
     * @throws InvalidArgumentException
     * @throws ValidationException
     */
    public static function create(array $data): ConfirmationEntity
    {
        $smartLogic = self::normalizeSmartLogic($data['smartLogic'] ?? false);

        $confirmationValueObject = new Confirmation(
            $data['id'] ?? 0,
            $data['formId'] ?? 0,
            $data['name'] ?? BackendStrings::getSettingsFormBuilderStrings()['default_confirmation_name'],
            $data['type'] ?? '',
            $data['enabled'] ?? true,
            $data['showForm'] ?? false,
            $data['message'] ?? '',
            $data['url'] ?? '',
            $data['page'] ?? '',
            (int) ($data['position'] ?? 0),
            (bool) ($data['isDefault'] ?? false),
            $smartLogic
        );

        $confirmationEntity = new ConfirmationEntity($confirmationValueObject);

        if (isset($data['id'])) {
            $confirmationEntity->setId($data['id']);
        }

        if (isset($data['formId'])) {
            $confirmationEntity->setFormId($data['formId']);
        }

        if (isset($data['type'])) {
            $confirmationEntity->setType($data['type']);
        }

        if (isset($data['enabled'])) {
            $confirmationEntity->setEnabled($data['enabled']);
        }

        if (isset($data['showForm'])) {
            $confirmationEntity->setShowForm($data['showForm']);
        }

        if (isset($data['message'])) {
            $confirmationEntity->setMessage(wp_unslash($data['message']));
        }

        if (isset($data['url'])) {
            $confirmationEntity->setUrl($data['url']);
        }

        if (isset($data['page'])) {
            $confirmationEntity->setPage($data['page']);
        }

        if (isset($data['name'])) {
            $confirmationEntity->setName($data['name']);
        }

        if (isset($data['position'])) {
            $confirmationEntity->setPosition((int) $data['position']);
        }

        if (isset($data['isDefault'])) {
            $confirmationEntity->setIsDefault((bool) $data['isDefault']);
        }

        if (isset($data['smartLogic'])) {
            $confirmationEntity->setSmartLogic($smartLogic);
        }

        return $confirmationEntity;
    }

    /**
     * Normalize smartLogic from DB/API payload.
     *
     * @param mixed $smartLogic
     * @return array<string, mixed>
     */
    private static function normalizeSmartLogic($smartLogic): array
    {
        if (is_array($smartLogic)) {
            $match = $smartLogic['match'] ?? 'any';

            return [
                'enabled' => (bool)($smartLogic['enabled'] ?? false),
                'match' => in_array($match, ['any', 'all'], true) ? $match : 'any',
                'rules' => is_array($smartLogic['rules'] ?? null) ? $smartLogic['rules'] : [],
            ];
        }

        if (is_string($smartLogic)) {
            $decoded = json_decode($smartLogic, true);
            if (is_array($decoded)) {
                return self::normalizeSmartLogic($decoded);
            }

            return [
                'enabled' => in_array(strtolower($smartLogic), ['1', 'true'], true),
                'match' => 'any',
                'rules' => [],
            ];
        }

        return [
            'enabled' => (bool)$smartLogic,
            'match' => 'any',
            'rules' => [],
        ];
    }
}
