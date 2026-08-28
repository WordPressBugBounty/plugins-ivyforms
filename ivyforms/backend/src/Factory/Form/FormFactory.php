<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Factory\Form;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Helpers\Helper;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Entity\Form\Form as FormEntity;
use IvyForms\ValueObjects\Form\Form;
use IvyForms\ValueObjects\Form\IntegrationSettings;
use IvyForms\ValueObjects\Form\PaymentSettings;
use IvyForms\ValueObjects\Form\FormMetadata;
use IvyForms\ValueObjects\Form\FormSettings;
use IvyForms\ValueObjects\Form\StyleSettings;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class FormFactory
 *
 * @package IvyForms\Factory\Form
 */
class FormFactory
{
    /**
     * Top-level keys owned by Lite; anything else in $data is extension data (e.g. Pro settings).
     *
     * @var list<string>
     */
    private const KNOWN_FORM_DATA_KEYS = [
        'id',
        'name',
        'author',
        'starred',
        'published',
        'dateCreated',
        'dateEdited',
        'fields',
        'description',
        'formType',
        'showTitle',
        'showDescription',
        'storeEntries',
        'integrationSettings',
        'styleSettings',
        'formActionButtons',
        'pages',
        'progressIndicator',
        'paymentSettings',
        'settings',
        'confirmationId',
    ];

    /**
     * @param array<string, mixed> $data
     *
     * @return  FormEntity
     *
     * @throws ValidationException
     */
    public static function create(array $data): FormEntity
    {
        $settings = isset($data['settings']) ? json_decode($data['settings'], true) : [];
        if (!is_array($settings)) {
            $settings = [];
        }

        /**
         * @param array<string, mixed> $data
         * @param array<string, mixed> $settings
         * @return array<string, mixed>
         */
        $data = apply_filters('ivyforms/form/factory/extract_settings', $data, $settings);

        $integrationSettings = Helper::parseToArray($data['integrationSettings']);
        $pages = $data['pages'] ?? null;
        $progressIndicator = $data['progressIndicator'] ?? null;
        $rawStyleSettings = isset($data['styleSettings']) ? Helper::parseToArray($data['styleSettings']) : null;
        $styleSettings = new StyleSettings(is_array($rawStyleSettings) ? $rawStyleSettings : null);

        if (!empty($settings)) {
            $data['showTitle']       = $settings['showTitle'] ?? true;
            $data['showDescription'] = $settings['showDescription'] ?? false;
            $data['storeEntries']    = !isset($settings['storeEntries']) || (bool)$settings['storeEntries'];
            $data['formActionButtons'] = $settings['formActionButtons'] ?? [
                'submitButtonSettings' => [
                    'label'    => BackendStrings::getCommonStrings()['submit'],
                    'position' => 'default'
                ]
            ];

            // Prefer values from decoded settings, fallback to top-level API payload.
            $pages = array_key_exists('pages', $settings)
                ? $settings['pages']
                : ($data['pages'] ?? null);
            $progressIndicator = array_key_exists('progressIndicator', $settings)
                ? $settings['progressIndicator']
                : ($data['progressIndicator'] ?? null);
        }

        // Process extensible form options via one universal Pro hook.
        $formOptions = [
            'pages' => $pages,
            'progressIndicator' => $progressIndicator,
        ];

        $formOptions = apply_filters('ivyforms/process/form_options', $formOptions, $data['id'] ?? 0);

        $data['pages'] = $formOptions['pages'] ?? null;
        $data['progressIndicator'] = $formOptions['progressIndicator'] ?? null;

        // Process form action buttons via Pro hook if available
        // This runs for both database-loaded and API-submitted formActionButtons
        if (isset($data['formActionButtons'])) {
            $data['formActionButtons'] = apply_filters(
                'ivyforms/process/form_action_buttons',
                $data['formActionButtons'],
                $data['id'] ?? 0
            );
        } else {
            // Set default if not provided
            $data['formActionButtons'] = [
                'submitButtonSettings' => [
                    'label'    => BackendStrings::getCommonStrings()['submit'],
                    'position' => 'default'
                ]
            ];
        }

        if (empty($data['fields']) && isset($data['pages']) && is_array($data['pages'])) {
            $allFields = [];
            foreach ($data['pages'] as $page) {
                if (isset($page['fields']) && is_array($page['fields'])) {
                    foreach ($page['fields'] as $field) {
                        $allFields[] = $field;
                    }
                }
            }
            if (!empty($allFields)) {
                $data['fields'] = $allFields;
            }
        }

        $paymentRaw = null;
        if (isset($data['paymentSettings']) && is_array($data['paymentSettings'])) {
            $paymentRaw = $data['paymentSettings'];
        } elseif (!empty($settings['paymentSettings']) && is_array($settings['paymentSettings'])) {
            $paymentRaw = $settings['paymentSettings'];
        }

        $resolvedFormType = Sanitizer::sanitizeFormType($data['formType'] ?? Sanitizer::FORM_TYPE_CLASSIC);

        $formObject = new Form(
            $data['id'] ?? 0,
            $data['name'] ?? '',
            $data['description'] ?? '',
            new FormMetadata(
                $data['author'] ?? '',
                $data['starred'] ?? false,
                $data['published'] ?? true,
                isset($data['dateCreated']) ? (string)$data['dateCreated'] : null,
                isset($data['dateEdited']) ? (string)$data['dateEdited'] : null
            ),
            new FormSettings(
                $data['showTitle'] ?? true,
                $data['showDescription'] ?? false,
                isset($data['storeEntries']) ? (bool)$data['storeEntries'] : true
            ),
            $data['fields'] ?? [],
            new IntegrationSettings($integrationSettings),
            $data['pages'] ?? null,
            $data['progressIndicator'] ?? null,
            $styleSettings,
            $data['formActionButtons'] ?? null,
            $resolvedFormType,
            new PaymentSettings($paymentRaw)
        );

        $form = new FormEntity($formObject);

        if (isset($data['id'])) {
            $form->setId($data['id']);
        }

        if (isset($data['name'])) {
            $form->setName($data['name']);
        }

        if (isset($data['author'])) {
            $form->setAuthor($data['author']);
        }

        if (isset($data['starred'])) {
            $form->setStarred($data['starred']);
        }

        if (isset($data['published'])) {
            $form->setPublished($data['published']);
        }

        if (isset($data['description'])) {
            $form->setDescription($data['description']);
        }

        if (isset($data['fields'])) {
            $form->setFields($data['fields']);
        }

        if (isset($data['showTitle'])) {
            $form->setTitleVisible($data['showTitle']);
        }

        if (isset($data['showDescription'])) {
            $form->setDescriptionVisible($data['showDescription']);
        }

        if (isset($data['storeEntries'])) {
            $form->setStoreEntries($data['storeEntries']);
        }

        if (isset($data['pages'])) {
            $form->setPages($data['pages']);
        }

        if (isset($data['progressIndicator'])) {
            $form->setProgressIndicator($data['progressIndicator']);
        }

        if (isset($data['formActionButtons'])) {
            $form->setFormActionButtons($data['formActionButtons']);
        }

        $form->setExtensionData(self::extractExtensionData($data));

        return $form;
    }

    /**
     * Keys in $data that are not owned by Lite (e.g. Pro form settings in REST / settings JSON).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function extractExtensionData(array $data): array
    {
        return array_diff_key($data, array_flip(self::KNOWN_FORM_DATA_KEYS));
    }
}
