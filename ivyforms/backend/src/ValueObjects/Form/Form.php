<?php

namespace IvyForms\ValueObjects\Form;

use IvyForms\Common\Exceptions\ValidationException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Services\Template\TemplateDefaults;
use IvyForms\Services\Translations\BackendStrings;

/**
 * Class Form - Core form value object with composed metadata and settings
 *
 * @package IvyForms\ValueObjects\Form
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
final class Form
{
    /**
     * @var int
     */
    public int $id;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $description;

    /**
     * @var FormMetadata
     */
    public FormMetadata $metadata;

    /**
     * @var FormSettings
     */
    public FormSettings $settings;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    public ?array $fields;

    /**
     * @var IntegrationSettings|null
     */
    public ?IntegrationSettings $integrationSettings;
    /**
     * @var StyleSettings|null
     */
    public ?StyleSettings $styleSettings;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    public ?array $pages;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $progressIndicator;

    /**
     * @var array<string, mixed>
     */
    public array $formActionButtons;

    /**
     * Lowercase presentation type (classic, conversational, or plugin-defined via
     * ivyforms/form/allowed_form_types). Always matches Sanitizer::sanitizeFormType().
     *
     * @var string
     */
    public string $formType;

    /**
     * @var PaymentSettings
     */
    public PaymentSettings $paymentSettings;

    /**
     * Form constructor.
     *
     * @param int $id
     * @param string $name
     * @param string $description
     * @param FormMetadata $metadata
     * @param FormSettings $settings
     * @param array<int, array<string, mixed>> $fields
     * @param IntegrationSettings|null $integrationSettings
     * @param array<int, array<string, mixed>>|null $pages
     * @param array<string, mixed>|null $progressIndicator
     * @param StyleSettings|null $styleSettings
     * @param array<string, mixed>|null $formActionButtons
     * @param string $formType presentation type (sanitized to the allowed allowlist)
     * @param PaymentSettings|array<string, mixed>|null $paymentSettings
     * @SuppressWarnings("ExcessiveParameterList")
     * @throws ValidationException
     */
    public function __construct(
        int $id,
        string $name,
        string $description,
        FormMetadata $metadata,
        FormSettings $settings,
        array $fields = [],
        ?IntegrationSettings $integrationSettings = null,
        ?array $pages = null,
        ?array $progressIndicator = null,
        ?StyleSettings $styleSettings = null,
        ?array $formActionButtons = null,
        string $formType = Sanitizer::FORM_TYPE_CLASSIC,
        $paymentSettings = null
    ) {
        $this->id = $this->validateId($id);
        $this->name = $this->validateString($name, 255, 'name');
        $this->description = $this->validateString($description, 1000, 'description');
        $this->metadata = $metadata;
        $this->settings = $settings;
        $this->fields = $fields;
        $this->integrationSettings = $integrationSettings;
        $this->pages = $pages;
        $this->progressIndicator = $progressIndicator;
        $this->formActionButtons = $formActionButtons ?? [
            'submitButtonSettings' => [
                'label' => BackendStrings::getCommonStrings()['submit'],
                'position' => 'default',
            ],
        ];
        $this->styleSettings = $styleSettings;
        $this->formType = $this->normalizeStoredFormType($formType);

        $this->paymentSettings = $paymentSettings instanceof PaymentSettings
            ? $paymentSettings
            : new PaymentSettings(is_array($paymentSettings) ? $paymentSettings : null);
    }

    /**
     * @param string $formType
     * @return string
     */
    private function normalizeStoredFormType(string $formType): string
    {
        return Sanitizer::sanitizeFormType($formType);
    }

    /**
     * Validates the ID.
     *
     * @param int $id
     *
     * @return int
     * @throws ValidationException
     */
    private function validateId(int $id): int
    {
        if ($id < 0) {
            throw new ValidationException(BackendStrings::getExceptionStrings()['id_positive_integer']);
        }
        return $id;
    }

    /**
     * Validates a string value.
     *
     * @param string $value
     * @param int    $maxLength
     * @param string $fieldName
     *
     * @return string
     *
     * @throws ValidationException
     */
    private function validateString(string $value, int $maxLength, string $fieldName): string
    {
        if (strlen($value) > $maxLength) {
            throw new ValidationException(
                sprintf(
                /* translators: 1: String value, 2: String max length. */
                    esc_html__('%1$s must be at most %2$d characters.', 'ivyforms'),
                    esc_html($fieldName),
                    $maxLength
                )
            );
        }
        return $value;
    }


    /**
     * Get the form ID.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the form name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFormType(): string
    {
        return $this->formType;
    }

    /**
     * @param string $formType
     */
    public function setFormType(string $formType): void
    {
        $this->formType = $this->normalizeStoredFormType($formType);
    }

    /**
     * Get the form description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the form fields.
     *
     * @return array<int, array<string, mixed>>|null
     */
    public function getFields(): ?array
    {
        return $this->fields;
    }

    /**
     * Get the integration settings.
     * @return IntegrationSettings|null
     */
    public function getIntegrationSettings(): ?IntegrationSettings
    {
        return $this->integrationSettings;
    }

    /**
     * Set integration settings.
     * @param IntegrationSettings $settings
     */
    public function setIntegrationSettings(IntegrationSettings $settings): void
    {
        $this->integrationSettings = $settings;
    }

    public function getPaymentSettings(): PaymentSettings
    {
        return $this->paymentSettings;
    }

    public function setPaymentSettings(PaymentSettings $paymentSettings): void
    {
        $this->paymentSettings = $paymentSettings;
    }

    /**
     * Get style settings value object.
     *
     * @return StyleSettings|null
     */
    public function getStyleSettings(): ?StyleSettings
    {
        return $this->styleSettings;
    }

    /**
     * Set style settings.
     *
     * @param StyleSettings|null $settings Style settings or null.
     */
    public function setStyleSettings(?StyleSettings $settings): void
    {
        $this->styleSettings = $settings;
    }

    /**
     * Convert the form to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $integrationPayload = $this->integrationSettings !== null
            ? $this->integrationSettings->toArray()
            : ['integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings()];

        $stylePayload = $this->styleSettings !== null
            ? $this->styleSettings->toArray()
            : ['styleSettings' => TemplateDefaults::getDefaultFormStylesSafe()];

        return array_merge(
            [
                'id'                => $this->getId(),
                'name'              => $this->getName(),
                'description'       => $this->getDescription(),
                'formType'          => $this->getFormType(),
                'fields'            => $this->getFields(),
                'pages'             => $this->pages,
                'progressIndicator' => $this->progressIndicator,
                'formActionButtons' => $this->formActionButtons,
            ],
            $this->metadata->toArray(),
            $this->settings->toArray(),
            $integrationPayload,
            $stylePayload,
            $this->getPaymentSettings()->toArray()
        );
    }
}
