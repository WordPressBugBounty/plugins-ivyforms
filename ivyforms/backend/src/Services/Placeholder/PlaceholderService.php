<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See COPYING.md for license details.
 */

namespace IvyForms\Services\Placeholder;

use IvyForms\Common\Helpers\EntryHelper;
use IvyForms\Services\Security\Common\IpDetectionService;
use IvyForms\Entity\Notification\Notification;

class PlaceholderService
{
    /**
     * Replace placeholders in a template string with actual values.
     *
     * @param string $template
     * @param array<string, string|array<string, string>> $fieldData
     * @param array<string, string|int> $generalData
     * @param array<string, string> $fieldLabels
     * @param Notification|null $notificationContext Notification context for empty field filtering
     * @return string
     */
    public static function replacePlaceholders(
        string $template,
        array $fieldData = [],
        array $generalData = [],
        array $fieldLabels = [],
        ?Notification $notificationContext = null
    ): string {
        $placeholders = self::buildFieldPlaceholders(
            $fieldData,
            $fieldLabels,
            $notificationContext !== null ? $notificationContext->isShowEmptyFields() : false
        );
        $placeholders = self::buildGeneralPlaceholders($placeholders, $generalData);

        // Replace all placeholders
        $result = strtr($template, $placeholders);

        // Remove any unreplaced placeholders
        $result = preg_replace('/{{[^}]+}}/', '', $result);

        return $result;
    }

    /**
     * Build placeholders for fields and {{all_data}}.
     *
     * @param array<string, string|array<string, string>> $fieldData
     * @param array<string, string> $fieldLabels
     * @param bool $showEmptyFields
     * @return array<string, string>
     */
    private static function buildFieldPlaceholders(
        array $fieldData,
        array $fieldLabels,
        bool $showEmptyFields
    ): array {
        $placeholders = [];
        $allData = [];
        $addedLabels = [];

        foreach ($fieldData as $key => $value) {
            $valueString = self::normalizeFieldValue($value);
            $placeholders['{{' . $key . '}}'] = $valueString;

            if (!isset($fieldLabels[$key]) || in_array($fieldLabels[$key], $addedLabels, true)) {
                continue;
            }

            $label = $fieldLabels[$key];
            if (!$showEmptyFields && trim((string) $valueString) === '') {
                $addedLabels[] = $label;
                continue;
            }

            $allData[] = $label . ': ' . $valueString;
            $addedLabels[] = $label;
        }

        if (!empty($fieldData)) {
            $placeholders['{{all_data}}'] = implode('<br>', $allData);
        }

        return $placeholders;
    }

    /**
     * Build placeholders for general data.
     *
     * @param array<string, string> $placeholders
     * @param array<string, string|int> $generalData
     * @return array<string, string>
     */
    private static function buildGeneralPlaceholders(array $placeholders, array $generalData): array
    {
        foreach ($generalData as $key => $value) {
            $placeholders['{{wp.' . $key . '}}'] = (string) $value;
        }

        return $placeholders;
    }

    /**
     * Normalize field value for placeholders.
     *
     * @param string|array<string, string> $value
     * @return string
     */
    private static function normalizeFieldValue($value): string
    {
        if (is_array($value)) {
            return implode(' ', array_filter(array_map(function ($val) {
                return $val;
            }, $value)));
        }

        return (string) $value;
    }

    /**
     * Build field data for placeholders from form fields and submission data.
     *
     * @param array<int, object> $formFields
     * @param array<string, string> $submissionData
     * @return array<string, string>
     */
    public static function buildFieldData(
        array $formFields,
        array $submissionData
    ): array {
        $fieldData = [];
        foreach ($formFields as $field) {
            $typeIndex = $field->getType() . '_' . $field->getIndex();
            $id = $field->getId();
            if (isset($submissionData[$id])) {
                $value = $submissionData[$id];

                /**
                 * Filter to convert fields values to HTML representation
                 *
                 * @since 1.0.0
                 *
                 * @param string $value The field value
                 * @param object $field The field object
                 * @param array  $formFields All form fields (for parent-child lookups)
                 * @return string The converted value
                 */
                $value = apply_filters('ivyforms/placeholder/filter_field_value', $value, $field, $formFields);

                $fieldData[$typeIndex] = $value;
                $fieldData[$id] = $value;
            }
        }
        return $fieldData;
    }

    /**
     * Build general data for placeholders from environment and submission data.
     *
     * @param int $entryId
     * @param array<string, mixed> $submissionData
     * @return array<string, string|int>
     */
    public static function buildGeneralData(int $entryId, array $submissionData = []): array
    {
        $generalData = [
            'site_url' => get_bloginfo('url'),
            'site_title' => get_bloginfo('name'),
            'admin_email' => get_bloginfo('admin_email'),
            'date' => date(get_option('date_format')),
            'user_ip' => IpDetectionService::getUserIpAddress(),
            'user_agent' => EntryHelper::getUserAgent(),
            'referer_url' => $submissionData['referer'] ?? '',
        ];

        if (!empty($submissionData['postId'])) {
            $post = get_post($submissionData['postId']);
            if ($post) {
                $generalData['post_id'] = $post->ID;
                $generalData['post_title'] = $post->post_title;
                $generalData['post_permalink'] = get_permalink($post->ID);
            }
        }

        if (is_user_logged_in()) {
            $currentUser = wp_get_current_user();
            $generalData['user_id'] = $currentUser->ID;
            $generalData['user_name'] = $currentUser->user_login;
            $generalData['user_email'] = $currentUser->user_email;
            $generalData['user_first_name'] = $currentUser->user_firstname;
            $generalData['user_last_name'] = $currentUser->user_lastname;
        }
        if ($entryId) {
            $generalData['entry_id'] = $entryId;
        }
        return $generalData;
    }

    /**
     * Build field labels mapping for placeholders from form fields.
     *
     * @param array<int, object> $formFields
     * @return array<string, string>
     */
    public static function buildFieldLabels(array $formFields): array
    {
        $fieldLabels = [];
        foreach ($formFields as $field) {
            $typeIndex = $field->getType() . '_' . $field->getIndex();
            $fieldLabels[$typeIndex] = $field->getFieldGeneralSettings()->getLabel();
            $fieldLabels[$field->getId()] = $field->getFieldGeneralSettings()->getLabel();
        }
        return $fieldLabels;
    }
}
