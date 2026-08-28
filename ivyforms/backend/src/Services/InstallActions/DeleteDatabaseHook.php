<?php

/**
 * Database hook for deletion
 */

namespace IvyForms\Services\InstallActions;

use IvyForms\Common\Exceptions\InvalidArgumentException;
use IvyForms\Services\InstallActions\DB\Confirmation\ConfirmationsTable;
use IvyForms\Services\InstallActions\DB\Entry\EntriesTable;
use IvyForms\Services\InstallActions\DB\EntryField\EntryFieldsTable;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;
use IvyForms\Services\InstallActions\DB\Notification\NotificationsTable;
use IvyForms\Services\InstallActions\DB\FieldOptions\FieldOptionsTable;

/**
 * Class DeleteDatabaseHook
 *
 * @package IvyForms\Services\InstallActions
 */
class DeleteDatabaseHook
{
    /**
     * Delete the plugin tables
     *
     * @throws InvalidArgumentException
     */
    public static function delete(): void
    {
        // Delete in reverse order of creation (respecting foreign keys)
        FieldOptionsTable::delete();
        EntryFieldsTable::delete();
        EntriesTable::delete();
        ConfirmationsTable::delete();
        NotificationsTable::delete();
        FieldsTable::delete();

        /**
         * Fired after lite tables are dropped, before the forms table.
         * Extensions (e.g. Pro) delete their database tables here.
         *
         * @since 0.1.0
         */
        do_action('ivyforms/delete_database/tables');

        FormsTable::delete();

        delete_option('ivyforms_settings');
        delete_option('ivyforms_version');
        delete_option('ivyforms_access_rules');
        delete_option('ivyforms_access_rules_caps_synced_v2');

        /**
         * Fired after lite options are removed.
         * Extensions delete their options, transients, and scheduled events here.
         *
         * @since 0.1.0
         */
        do_action('ivyforms/delete_database/options');
    }
}
