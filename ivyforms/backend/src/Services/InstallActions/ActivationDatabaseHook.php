<?php

/**
 * Database hook for activation
 */

namespace IvyForms\Services\InstallActions;

use IvyForms\Services\InstallActions\DB\Confirmation\ConfirmationsTable;
use IvyForms\Services\InstallActions\DB\Entry\EntriesTable;
use IvyForms\Services\InstallActions\DB\EntryField\EntryFieldsTable;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;
use IvyForms\Services\InstallActions\DB\Notification\NotificationsTable;
use IvyForms\Services\InstallActions\DB\FieldOptions\FieldOptionsTable;
use IvyForms\Services\InstallActions\DB\GoogleSheets\GoogleSheetsTable;
use IvyForms\Services\InstallActions\Migrations\AddRowColumnLayoutToFields;
use IvyForms\Services\InstallActions\Migrations\SeedRowIndexForFields;
use IvyForms\Services\InstallActions\Migrations\AddReplyToToNotifications;
use IvyForms\Services\InstallActions\Migrations\AddStyleSettingsToForms;
use IvyForms\Services\InstallActions\Migrations\AddShowEmptyFieldsToNotifications;
use IvyForms\Services\InstallActions\Migrations\AddFormTypeToForms;
use IvyForms\Services\InstallActions\Migrations\ConvertNotificationSmartLogicToText;
use IvyForms\Services\InstallActions\Migrations\AddPageIdToFields;
use IvyForms\Services\InstallActions\Migrations\AddNamePositionToConfirmations;
use IvyForms\Services\InstallActions\Migrations\AddCcBccToNotifications;
use IvyForms\Services\InstallActions\Migrations\ExpandReceiverColumnInNotifications;

/**
 * Class ActivationHook
 *
 * @package IvyForms\Services\InstallActions
 */
class ActivationDatabaseHook
{
    /**
     * Initialize the plugin
     */
    public static function init(): void
    {
        FormsTable::init();
        FieldsTable::init();
        NotificationsTable::init();
        ConfirmationsTable::init();
        EntriesTable::init();
        EntryFieldsTable::init();
        FieldOptionsTable::init();
        GoogleSheetsTable::init();

        // Run migrations
        AddRowColumnLayoutToFields::run();
        SeedRowIndexForFields::run();
        AddReplyToToNotifications::run();
        AddStyleSettingsToForms::run();
        AddShowEmptyFieldsToNotifications::run();
        AddFormTypeToForms::run();
        ConvertNotificationSmartLogicToText::run();
        AddPageIdToFields::run();
        AddNamePositionToConfirmations::run();
        AddCcBccToNotifications::run();
        ExpandReceiverColumnInNotifications::run();
    }
}
