<?php

namespace IvyForms\Services\Changelog;

class ChangelogService
{
    /**
     * Get changelog data with translated strings.
     *
     * @return array{
     *     version: string,
     *     release_date: string,
     *     features: array<array{text: string}>,
     *     improvements: array<array{text: string}>,
     *     bugfixes: array<array{text: string}>
     * }
     */
    public static function getChangelogData(): array
    {
        $changelogData = [
            'version' => IVYFORMS_VERSION,
            'release_date' => '2026-08-21',
            'features' => [
                ['text' => __('Added AI form generation from a text prompt.', 'ivyforms')],
                [
                    'text' => __(
                        'Added import from WS Form and Fluent Forms, including conditional logic.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Aligned MCP AI assistant capabilities with user permissions '
                        . 'and enabled the toggle by default.',
                        'ivyforms'
                    ),
                ],
                ['text' => __('Added usage tracking for paid users.', 'ivyforms')],
            ],
            'improvements' => [
                ['text' => __('Showed Field ID on the Slider field General tab.', 'ivyforms')],
                ['text' => __('Aligned entry details field order with the form layout.', 'ivyforms')],
                [
                    'text' => __(
                        'Prevented the add-field panel from being cut off when the license-inactive notice is shown.',
                        'ivyforms'
                    ),
                ],
                ['text' => __('Aligned columns in the Settings permissions table.', 'ivyforms')],
            ],
            'bugfixes' => [
                ['text' => __('Fixed IvyEditor HTML component issues.', 'ivyforms')],
                ['text' => __('Fixed padding in the form selection field.', 'ivyforms')],
                [
                    'text' => __(
                        'Fixed template card images not visible in Safari on macOS.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed the payment fields "Show layouts" control showing next to CSS Classes.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Hardened MCP form create, settings, and add-field helpers used with Angie AI.',
                        'ivyforms'
                    ),
                ],
            ],
        ];

        /**
         * Filter changelog data to allow Pro plugin to add its own changelog entries
         *
         * @param array $changelogData The changelog data array
         * @return array Modified changelog data
         */
        return apply_filters('ivyforms/changelog/get_data', $changelogData);
    }
}
