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
            'release_date' => '2026-10-05',
            'features' => [
                ['text' => __('Added AI choice generation for Dropdown, Radio, and Checkbox fields.', 'ivyforms')],
                ['text' => __('Added import from WPForms and Gravity Forms.', 'ivyforms')],
                ['text' => __('Added CC and BCC recipients to email notifications.', 'ivyforms')],
                ['text' => __('Added a Disable styles option so forms can inherit the theme styling.', 'ivyforms')],
            ],
            'improvements' => [
                ['text' => __('Improved compatibility with PHP 8.5.', 'ivyforms')],
            ],
            'bugfixes' => [
                ['text' => __('Fixed moving an added field in the form builder.', 'ivyforms')],
                ['text' => __('Fixed Email and Confirm Email field spacing on the frontend.', 'ivyforms')],
                ['text' => __('Fixed editing HTML code in the Message field.', 'ivyforms')],
                ['text' => __('Fixed console errors when opening a new form in the form builder.', 'ivyforms')],
                ['text' => __('Fixed an error when clicking the Message field in Confirmations.', 'ivyforms')],
                [
                    'text' => __(
                        'Fixed the HEX color input in the Style tab overwriting manually typed values.',
                        'ivyforms'
                    ),
                ],
                ['text' => __('Fixed form export failing when no notifications are configured.', 'ivyforms')],
                ['text' => __('Fixed {{all_data}} in notifications ignoring the reordered field order.', 'ivyforms')],
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
