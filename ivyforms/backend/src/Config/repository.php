<?php

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use IvyForms\Repository\FieldOptions\FieldOptionsRepository;
use IvyForms\Repository\FieldOptions\FieldOptionsRepositoryInterface;
use IvyForms\Repository\Form\FormRepository;
use IvyForms\Repository\Form\FormRepositoryInterface;
use IvyForms\Repository\Field\FieldRepository;
use IvyForms\Repository\Field\FieldRepositoryInterface;
use IvyForms\Repository\Notification\NotificationRepository;
use IvyForms\Repository\Notification\NotificationRepositoryInterface;
use IvyForms\Repository\Confirmation\ConfirmationRepository;
use IvyForms\Repository\Confirmation\ConfirmationRepositoryInterface;
use IvyForms\Repository\Entry\EntryRepository;
use IvyForms\Repository\Entry\EntryRepositoryInterface;
use IvyForms\Repository\EntryField\EntryFieldRepository;
use IvyForms\Repository\EntryField\EntryFieldRepositoryInterface;
use IvyForms\Repository\GoogleSheets\GoogleSheetsRepository;
use IvyForms\Repository\GoogleSheets\GoogleSheetsRepositoryInterface;

use IvyForms\Services\InstallActions\DB\FieldOptions\FieldOptionsTable;
use IvyForms\Services\InstallActions\DB\GoogleSheets\GoogleSheetsTable;
use IvyForms\Services\InstallActions\DB\Form\FormsTable;
use IvyForms\Services\InstallActions\DB\Field\FieldsTable;
use IvyForms\Services\InstallActions\DB\Notification\NotificationsTable;
use IvyForms\Services\InstallActions\DB\Confirmation\ConfirmationsTable;
use IvyForms\Services\InstallActions\DB\Entry\EntriesTable;
use IvyForms\Services\InstallActions\DB\EntryField\EntryFieldsTable;

return [

    FormRepositoryInterface::class => function () {
        return new FormRepository(FormsTable::getTableName());
    },

    FieldRepositoryInterface::class => function () {
        return new FieldRepository(FieldsTable::getTableName());
    },

    NotificationRepositoryInterface::class => function () {
        return new NotificationRepository(NotificationsTable::getTableName());
    },

    ConfirmationRepositoryInterface::class => function () {
        return new ConfirmationRepository(ConfirmationsTable::getTableName());
    },

    EntryRepositoryInterface::class => function () {
        return new EntryRepository(EntriesTable::getTableName());
    },

    EntryFieldRepositoryInterface::class => function () {
        return new EntryFieldRepository(EntryFieldsTable::getTableName());
    },

    FieldOptionsRepositoryInterface::class => function () {
        return new FieldOptionsRepository(FieldOptionsTable::getTableName());
    },

    GoogleSheetsRepositoryInterface::class => function () {
        return new GoogleSheetsRepository(GoogleSheetsTable::getTableName());
    },
];
