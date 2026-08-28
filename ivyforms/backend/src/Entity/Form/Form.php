<?php

/**
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Entity\Form;

use IvyForms\ValueObjects\Form\Form as FormValueObject;
use IvyForms\ValueObjects\Form\IntegrationSettings;
use IvyForms\ValueObjects\Form\PaymentSettings;
use IvyForms\ValueObjects\Form\StyleSettings;
use IvyForms\Services\Template\TemplateDefaults;

/**
 * Class Form
 *
 * @package IvyForms\Entity\Form
 */
class Form
{
    /**
     * @var FormValueObject
     */
    private FormValueObject $form;

    /**
     * Top-level extension keys (e.g. Pro settings) for the request lifecycle.
     *
     * @var array<string, mixed>
     */
    private array $extensionData = [];

    /**
     * Form constructor.
     *
     * @param FormValueObject $form The form object.
     */
    public function __construct(FormValueObject $form)
    {
        $this->form = $form;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setExtensionData(array $data): void
    {
        $this->extensionData = $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getExtensionData(): array
    {
        return $this->extensionData;
    }

    /**
     * Get the ID of the form.
     *
     * @return int The form ID.
     */
    public function getId(): int
    {
        return $this->form->getId();
    }

    /**
     * Set the ID of the form.
     *
     * @param int $formId The form ID to set.
     */
    public function setId(int $formId): void
    {
        $this->form->id = $formId;
    }

    /**
     * Get the name of the form.
     *
     * @return string The form name.
     */
    public function getName(): string
    {
        return $this->form->getName();
    }

    /**
     * Set the name of the form.
     *
     * @param string $name The form name to set.
     */
    public function setName(string $name): void
    {
        $this->form->name = $name;
    }

    /**
     * @return string classic|conversational
     */
    public function getFormType(): string
    {
        return $this->form->getFormType();
    }

    /**
     * @param string $formType
     */
    public function setFormType(string $formType): void
    {
        $this->form->setFormType($formType);
    }

    /**
     * Get the author of the form.
     *
     * @return string The form author.
     */
    public function getAuthor(): string
    {
        return $this->form->metadata->getAuthor();
    }

    /**
     * Set the author of the form.
     *
     * @param string $author The form author to set.
     */
    public function setAuthor(string $author): void
    {
        $this->form->metadata->author = $author;
    }

    /**
     * Get the starred status of the form.
     *
     * @return bool The form starred status.
     */
    public function isStarred(): bool
    {
        return $this->form->metadata->isStarred();
    }

    /**
     * Set the starred status of the form.
     *
     * @param bool $starred The form starred status to set.
     */
    public function setStarred(bool $starred): void
    {
        $this->form->metadata->starred = $starred;
    }

    /**
     * Get the published status of the form.
     *
     * @return bool The form published status.
     */
    public function isPublished(): bool
    {
        return $this->form->metadata->isPublished();
    }

    /**
     * Set the published status of the form.
     *
     * @param bool $published The form published status to set.
     */
    public function setPublished(bool $published): void
    {
        $this->form->metadata->published = $published;
    }

    /**
     * Get the creation date of the form.
     *
     * @return string The form of date creation.
     */
    public function getDateCreated(): string
    {
        return $this->form->metadata->getDateCreated();
    }
    /**
     * Get the date edited of the form.
     *
     * @return string The form date edited.
     */
    public function getDateEdited(): string
    {
        return $this->form->metadata->getDateEdited();
    }

    /**
     * Get the form fields.
     *
     * @return mixed[] The form fields.
     */
    public function getFields(): array
    {
        return $this->form->getFields();
    }

    /**
     * Set the form fields.
     *
     * @param mixed[] $fields The form fields to set.
     */
    public function setFields(array $fields): void
    {
        $this->form->fields = $fields;
    }

    /**
     * Get the form description.
     *
     * @return string The form description.
     */
    public function getDescription(): string
    {
        return $this->form->getDescription();
    }

    /**
     * Set the form description.
     *
     * @param string $description The form description to set.
     */
    public function setDescription(string $description): void
    {
        $this->form->description = $description;
    }

    /**
     * Get the show title of the form.
     *
     * @return bool The form show title status.
     */
    public function isTitleVisible(): bool
    {
        return $this->form->settings->isShowTitle();
    }

    /**
     * Set the show title of the form.
     *
     * @param bool $showTitle The form show title to set.
     */
    public function setTitleVisible(bool $showTitle): void
    {
        $this->form->settings->showTitle = $showTitle;
    }
    /**
     * Get the show description of the form.
     *
     * @return bool The form show description status.
     */
    public function isDescriptionVisible(): bool
    {
        return $this->form->settings->isShowDescription();
    }
    /**
     * Set the show description of the form.
     *
     * @param bool $description The form show title to set.
     */
    public function setDescriptionVisible(bool $description): void
    {
        $this->form->settings->showDescription = $description;
    }

    /**
     * Set whether to store entries or not.
     *
     * @param bool $storeEntries
     * @return void
     */
    public function setStoreEntries(bool $storeEntries): void
    {
        $this->form->settings->storeEntries = $storeEntries;
    }
    /**
     *
     * Get whether to store entries or not.
     *
     * @return bool
     */
    public function isStoreEntries(): bool
    {
        return $this->form->settings->isStoreEntries();
    }

    /**
     * Get integration settings.
     * @return IntegrationSettings|null The integration settings.
     */
    public function getIntegrationSettings(): ?IntegrationSettings
    {
        return $this->form->integrationSettings;
    }
    /**
     * Set integration settings.
     * @param IntegrationSettings|null $settings The integration settings to set.
     */
    public function setIntegrationSettings(?IntegrationSettings $settings): void
    {
        $this->form->integrationSettings = $settings;
    }

    /**
     * Get pages.
     * @return array<int, array<string, mixed>> The pages array.
     */
    public function getPages(): array
    {
        return $this->form->pages ?? [];
    }

    /**
     * Set pages.
     * @param array<int, array<string, mixed>> $pages The pages array to set.
     */
    public function setPages(array $pages): void
    {
        $this->form->pages = $pages;
    }

    /**
     * Get progress indicator settings.
     * @return array<string, mixed> The progress indicator settings.
     */
    public function getProgressIndicator(): array
    {
        return $this->form->progressIndicator ?? [];
    }

    /**
     * Set progress indicator settings.
     * @param array<string, mixed> $progressIndicator The progress indicator settings to set.
     */
    public function setProgressIndicator(array $progressIndicator): void
    {
        $this->form->progressIndicator = $progressIndicator;
    }

    /**
     * Get form action buttons settings.
     *
     * @return array<string, mixed> The form action buttons settings.
     */
    public function getFormActionButtons(): array
    {
        return $this->form->formActionButtons;
    }

    /**
     * Set form action buttons settings.
     *
     * @param array<string, mixed> $formActionButtons The form action buttons settings to set.
     * @return void
     */
    public function setFormActionButtons(array $formActionButtons): void
    {
        $this->form->formActionButtons = $formActionButtons;
    }

    /**
     * Get payment-related form settings (e.g., currency).
     */
    public function getPaymentSettings(): PaymentSettings
    {
        return $this->form->getPaymentSettings();
    }

    /**
     * Set payment-related form settings.
     */
    public function setPaymentSettings(PaymentSettings $paymentSettings): void
    {
        $this->form->setPaymentSettings($paymentSettings);
    }

    /**
     * Get style settings as array
     *
     * @return array<string, mixed>
     */
    public function getStyleSettings(): array
    {
        $settings = $this->form->getStyleSettings();
        if (null === $settings) {
            return TemplateDefaults::getDefaultFormStylesSafe();
        }
        return $settings->getAll();
    }

    /**
     * Set style settings from array (wraps {@see StyleSettings} like integration settings).
     *
     * @param array<string, mixed>|null $settings The style settings to set.
     */
    public function setStyleSettings(?array $settings): void
    {
        if (null === $settings) {
            $this->form->setStyleSettings(null);
            return;
        }
        $this->form->setStyleSettings(new StyleSettings($settings));
    }

    /**
     * Convert the form entity to an array.
     *
     * @return array<string, mixed> The form entity as an array.
     */
    public function toArray(): array
    {
        $integrationSettings = $this->getIntegrationSettings();
        $integrationPayload = $integrationSettings !== null
            ? $integrationSettings->toArray()
            : ['integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings()];

        $data = array_merge(
            [
                'id'                    => $this->getId(),
                'name'                  => $this->getName(),
                'author'                => $this->getAuthor(),
                'starred'               => $this->isStarred(),
                'published'             => $this->isPublished(),
                'dateCreated'           => $this->getDateCreated(),
                'dateEdited'            => $this->getDateEdited(),
                'fields'                => $this->getFields(),
                'description'           => $this->getDescription(),
                'formType'              => $this->getFormType(),
                'showTitle'             => $this->isTitleVisible(),
                'showDescription'       => $this->isDescriptionVisible(),
                'storeEntries'          => $this->isStoreEntries(),
                'pages'                 => $this->getPages(),
                'progressIndicator'     => $this->getProgressIndicator(),
                'styleSettings'         => $this->getStyleSettings(),
                'formActionButtons'     => $this->getFormActionButtons(),
            ],
            $this->form->metadata->toArray(),
            $this->form->settings->toArray(),
            $integrationPayload,
            $this->extensionData,
            $this->getPaymentSettings()->toArray()
        );

        return $data;
    }
}
