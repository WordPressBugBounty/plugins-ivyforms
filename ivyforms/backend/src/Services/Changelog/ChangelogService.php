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
            'release_date' => '2026-10-20',
            'features' => [],
            'improvements' => [],
            'bugfixes' => [
                [
                    'text' => __(
                        'Fixed arbitrary file uploads via allowed extensions on public submissions (CVE-2026-109384).',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed status filter counts when the form and date filters are used together.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed Address field City and State subfields swapping order on the frontend.',
                        'ivyforms'
                    ),
                ],
                ['text' => __('Fixed a database error when saving a form with more than 54 fields.', 'ivyforms')],
                [
                    'text' => __(
                        'Fixed deleting a field in one section shrinking half-width fields in other sections.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed large PDF uploads bypassing the file size limit and failing with an "Invalid ID" error.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed two confirmation popups appearing when deleting a permission rule.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed Learn more on the Amelia, Zapier, API, and Google Sheets cards not opening docs.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed required validation being skipped for most field types, which let an empty form submit.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed the Email field Required message being replaced by the valid-email message.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed notification Subject placeholders so they list every field, like Message.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed negative Years of experience on the Software developer job application template.',
                        'ivyforms'
                    ),
                ],
                [
                    'text' => __(
                        'Fixed Product Order Form so Customer name includes First name and Last name.',
                        'ivyforms'
                    ),
                ],
                ['text' => __('Fixed the Product field showing raw JSON in notification placeholders.', 'ivyforms')],
                ['text' => __('Fixed the Explore Collection button on the themes upgrade card.', 'ivyforms')],
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
