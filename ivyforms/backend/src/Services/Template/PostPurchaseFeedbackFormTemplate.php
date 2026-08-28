<?php

namespace IvyForms\Services\Template;

use IvyForms\Services\Translations\BackendStrings;

class PostPurchaseFeedbackFormTemplate
{
    /**
     * Get template metadata (without form_data)
     *
     * @return array<string, mixed>
     */
    public static function getTemplateMeta(): array
    {
        return [
            'id' => 'post_purchase_feedback_form',
            'name' => BackendStrings::getTemplateStrings()['post_purchase_feedback_form'],
            'description' => BackendStrings::getTemplateStrings()['post_purchase_feedback_form_desc'],
            'category' => 'feedback-forms',
            'subcategory' => 'customer-feedback-forms',
            'is_pro' => false,
            'screenshot' => IVYFORMS_TEMPLATES_IMAGES_URL . 'post-purchase-feedback-form.svg',
        ];
    }

    /**
     * Get the full template including form data
     *
     * @return array<string, mixed>
     */
    public static function getTemplate(): array
    {
        return array_merge(
            self::getTemplateMeta(),
            [
            'form_data' => [
                'name' => BackendStrings::getTemplateStrings()['post_purchase_feedback_form'],
                'published' => 1,
                'showTitle' => 1,
                'storeEntries' => 1,
                'integrationSettings' => TemplateDefaults::getDefaultIntegrationSettings(),
                'fields' => array_merge(
                    self::getOrderNumberField(),
                    self::getDeliveryStatusField(),
                    self::getCheckoutRatingField(),
                    self::getDeliveryRatingField(),
                    self::getInfluencedPurchaseCheckboxField(),
                    self::getAdditionalCommentsTextareaField()
                ),
                'settings' => []
            ]
            ]
        );
    }

    /**
     * Order number (optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getOrderNumberField(): array
    {
        return [[
            'id' => 1,
            'fieldIndex' => 1,
            'type' => 'number',
            'label' => BackendStrings::getTemplateStrings()['order_number'],
            'placeholder' => 'e.g. 123456',
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 1,
        ]];
    }

    /**
     * Delivery status (radio, required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDeliveryStatusField(): array
    {
        return [[
            'id' => 5,
            'fieldIndex' => 2,
            'type' => 'radio',
            'label' => BackendStrings::getTemplateStrings()['delivery_status'],
            'required' => true,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 2,
            'fieldOptions' => [
                [
                    'label' => BackendStrings::getTemplateStrings()['delivered_on_time'],
                    'value' => 'delivered_on_time',
                    'position' => 1,
                ],
                [
                    'label' => BackendStrings::getTemplateStrings()['slightly_delayed'],
                    'value' => 'slightly_delayed',
                    'position' => 2,
                ],
                [
                    'label' => BackendStrings::getTemplateStrings()['significantly_delayed'],
                    'value' => 'significantly_delayed',
                    'position' => 3,
                ],
            ]
        ]];
    }

    /**
     * Checkout experience rating (required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getCheckoutRatingField(): array
    {
        return [[
            'id' => 6,
            'fieldIndex' => 3,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['checkout_experience'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 3,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(1),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * Delivery experience rating (required)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getDeliveryRatingField(): array
    {
        return [[
            'id' => 7,
            'fieldIndex' => 4,
            'type' => 'rating',
            'label' => BackendStrings::getTemplateStrings()['delivery_experience'],
            'required' => true,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 4,
            'labelPosition' => 'default',
            'ratingIcon' => 'star',
            'fieldOptions' => self::getStandardRatingOptions(6),
            'showValues' => false,
            'showRatingText' => true,
        ]];
    }

    /**
     * What influenced your purchase experience? (checkbox, optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getInfluencedPurchaseCheckboxField(): array
    {
        return [[
            'id' => 13,
            'fieldIndex' => 5,
            'type' => 'checkbox',
            'label' => BackendStrings::getTemplateStrings()['what_influenced_purchase_experience'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'placeholder' => '',
            'position' => 5,
            'fieldOptions' => [
                [
                    'id' => 8,
                    'label' => BackendStrings::getTemplateStrings()['pricing'],
                    'value' => 'pricing',
                    'isDefault' => false,
                    'position' => 1,
                ],
                [
                    'id' => 9,
                    'label' => BackendStrings::getTemplateStrings()['payment_options'],
                    'value' => 'payment_options',
                    'isDefault' => false,
                    'position' => 2,
                ],
                [
                    'id' => 10,
                    'label' => BackendStrings::getTemplateStrings()['shipping_cost'],
                    'value' => 'shipping_cost',
                    'isDefault' => false,
                    'position' => 3,
                ],
                [
                    'id' => 11,
                    'label' => BackendStrings::getTemplateStrings()['delivery_speed'],
                    'value' => 'delivery_speed',
                    'isDefault' => false,
                    'position' => 4,
                ],
                [
                    'id' => 12,
                    'label' => BackendStrings::getTemplateStrings()['packaging'],
                    'value' => 'packaging',
                    'isDefault' => false,
                    'position' => 5,
                ],
            ]
        ]];
    }

    /**
     * Additional comments (optional)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getAdditionalCommentsTextareaField(): array
    {
        return [[
            'id' => 14,
            'fieldIndex' => 6,
            'type' => 'textarea',
            'label' => BackendStrings::getTemplateStrings()['additional_comments'],
            'placeholder' => BackendStrings::getTemplateStrings()['additional_comments_placeholder'],
            'required' => false,
            'parentId' => null,
            'defaultValue' => '',
            'position' => 6,
            'labelPosition' => 'default',
        ]];
    }

    /**
     * Standard rating options (1-5)
     *
     * @return array<int, array<string, mixed>>
     */
    private static function getStandardRatingOptions(int $startId): array
    {
        return [
            [
                'id' => $startId,
                'label' => BackendStrings::getNewFormStrings()['good'],
                'value' => '1',
                'isDefault' => false,
                'position' => 1,
            ],
            [
                'id' => $startId + 1,
                'label' => BackendStrings::getNewFormStrings()['nice'],
                'value' => '2',
                'isDefault' => false,
                'position' => 2,
            ],
            [
                'id' => $startId + 2,
                'label' => BackendStrings::getNewFormStrings()['very_good'],
                'value' => '3',
                'isDefault' => false,
                'position' => 3,
            ],
            [
                'id' => $startId + 3,
                'label' => BackendStrings::getNewFormStrings()['awesome'],
                'value' => '4',
                'isDefault' => false,
                'position' => 4,
            ],
            [
                'id' => $startId + 4,
                'label' => BackendStrings::getNewFormStrings()['amazing'],
                'value' => '5',
                'isDefault' => false,
                'position' => 5,
            ],
        ];
    }
}
