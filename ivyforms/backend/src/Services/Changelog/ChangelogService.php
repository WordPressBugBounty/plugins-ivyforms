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
            'release_date' => '2026-09-09',
            'features' => [],
            'improvements' => [
                [
                    'text' => __(
                        'Replaced locked Pro style themes with a single upgrade card for Lite users.',
                        'ivyforms'
                    ),
                ],
            ],
            'bugfixes' => [
                [
                    'text' => __(
                        'Fixed automatic language detection for the Phone field on the frontend.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed Number field placeholder alignment on the frontend.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed custom required messages not being saved for First Name and Last Name subfields.',
                        'ivyforms'
                    ),
                ],
                ['text' => __('Restored PHP 7.4 compatibility when saving field settings.', 'ivyforms')],
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
