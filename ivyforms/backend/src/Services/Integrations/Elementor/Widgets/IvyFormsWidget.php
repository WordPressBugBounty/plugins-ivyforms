<?php

/**
 * IvyForms Elementor Widget
 *
 * @copyright © Melograno Venture Studio. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace IvyForms\Services\Integrations\Elementor\Widgets;

// phpcs:disable PSR1.Files.SideEffects
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

use Elementor\Plugin;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use IvyForms\Common\Helpers\FormPreviewHelper;
use IvyForms\Common\Sanitizer\HtmlSanitizer;
use IvyForms\Services\API\IvyFormsAPI;
use IvyForms\Services\Shortcode\ShortcodeService;

/**
 * Class IvyFormsWidget
 *
 * Elementor widget for displaying IvyForms forms
 * Following wpDataTables widget pattern with GutenbergBlockRenderer rendering approach
 *
 * @package IvyForms\Services\Integrations\Elementor\Widgets
 */
// phpcs:disable PSR1.Methods.CamelCapsMethodName
class IvyFormsWidget extends Widget_Base
{
    /**
     * Get widget name
     *
     * @return string Widget name
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function get_name(): string
    {
        return 'ivyforms';
    }

    /**
     * Get widget title
     *
     * @return string Widget title
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function get_title(): string
    {
        return __('IvyForms', 'ivyforms');
    }

    /**
     * Get widget icon
     *
     * @return string Widget icon
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function get_icon(): string
    {
        return 'ivyforms-elementor-icon';
    }

    /**
     * Get widget categories
     *
     * @return array<int, string> Widget categories
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function get_categories(): array
    {
        return ['ivyforms-elementor'];
    }

    /**
     * Get widget keywords
     *
     * @return array<int, string> Widget keywords for search
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function get_keywords(): array
    {
        return ['form', 'contact', 'ivyforms', 'ivy', 'forms'];
    }

    /**
     * Get all forms for the dropdown
     *
     * @return array<int, string> Forms array for select control
     */
    private function getFormsOptions(): array
    {
        $options = [
            0 => __('— Select Form —', 'ivyforms'),
        ];

        $forms = IvyFormsAPI::getForms();

        if (is_wp_error($forms) || empty($forms)) {
            return $options;
        }

        foreach ($forms as $form) {
            $options[$form->getId()] = $form->getName();
        }

        return $options;
    }

    /**
     * Register widget controls
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function register_controls(): void
    {
        // Content Section
        $this->start_controls_section(
            'ivyforms_content_section',
            [
                'label' => __('Form Settings', 'ivyforms'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $formSelectDescription = __('Choose the form you want to ' .
            'display.', 'ivyforms');
        $titleVisibilityDescription = __('Override the form title visibility ' .
            'setting.', 'ivyforms');
        $descriptionVisibilityDescription = __('Override the form description visibility ' .
            'setting.', 'ivyforms');
        $cssClassDescription = __('Add custom CSS classes separated by ' .
            'spaces.', 'ivyforms');

        $this->add_control(
            'form_id',
            [
                'label' => __('Select Form', 'ivyforms'),
                'type' => Controls_Manager::SELECT2,
                'options' => $this->getFormsOptions(),
                'default' => 0,
                'description' => $formSelectDescription,
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'ivyforms'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'ivyforms'),
                'label_off' => __('No', 'ivyforms'),
                'return_value' => 'yes',
                'default' => 'yes',
                'description' => $titleVisibilityDescription,
            ]
        );

        $this->add_control(
            'show_description',
            [
                'label' => __('Show Description', 'ivyforms'),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'ivyforms'),
                'label_off' => __('No', 'ivyforms'),
                'return_value' => 'yes',
                'default' => '',
                'description' => $descriptionVisibilityDescription,
            ]
        );

        $this->add_control(
            'css_class_separator',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control(
            'css_class',
            [
                'label' => __('Additional CSS Class(es)', 'ivyforms'),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'placeholder' => '',
                'description' => $cssClassDescription,
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Check if we're in Elementor editor preview mode
     *
     * @return bool
     */
    private function isEditorPreview(): bool
    {
        $plugin = Plugin::instance();
        if (!is_object($plugin) || !isset($plugin->editor, $plugin->preview)) {
            return false;
        }

        return $plugin->editor->is_edit_mode() || $plugin->preview->is_preview_mode();
    }

    /**
     * Render widget output on the frontend
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function render(): void
    {
        $settings = $this->get_settings_for_display();
        $formId = isset($settings['form_id']) ? (int) $settings['form_id'] : 0;

        if ($formId === 0) {
            $this->renderNoFormSelected();
            return;
        }

        $atts = $this->buildShortcodeAttributes($settings, $formId);
        $cssClasses = $this->buildCssClasses($settings);

        try {
            $this->renderFormOutput($formId, $atts, $cssClasses);
        } catch (\Exception $e) {
            echo '<div class="ivyforms-error"><p>' .
                 esc_html(__('Error rendering form', 'ivyforms')) .
                 '</p></div>';
        }
    }

    /**
     * Render no form selected message
     *
     * @return void
     */
    private function renderNoFormSelected(): void
    {
        echo '<div class="ivyforms-no-form-selected"><p>' .
             esc_html(__('Select a form from the widget settings on the left.', 'ivyforms')) .
             '</p></div>';
    }

    /**
     * Build shortcode attributes from settings
     *
     * @param array<string, mixed> $settings Widget settings
     * @param int $formId Form ID
     * @return array<string, string> Shortcode attributes
     */
    private function buildShortcodeAttributes(array $settings, int $formId): array
    {
        $atts = ['id' => (string) $formId];

        $showTitleEnabled = !empty($settings['show_title']) && $settings['show_title'] === 'yes';
        $atts['show_title'] = $showTitleEnabled ? 'true' : 'false';

        $showDescriptionEnabled = !empty($settings['show_description']) && $settings['show_description'] === 'yes';
        $atts['show_description'] = $showDescriptionEnabled ? 'true' : 'false';

        return $atts;
    }

    /**
     * Build CSS classes for the widget
     *
     * @param array<string, mixed> $settings Widget settings
     * @return array<int, string> CSS classes
     */
    private function buildCssClasses(array $settings): array
    {
        $cssClasses = ['ivyforms-elementor-block'];

        if (!empty($settings['css_class'])) {
            $sanitizedClasses = HtmlSanitizer::sanitizeCssClasses($settings['css_class']);
            if ($sanitizedClasses !== '') {
                $cssClasses = array_merge($cssClasses, explode(' ', $sanitizedClasses));
            }
        }

        return $cssClasses;
    }

    /**
     * Render form output
     *
     * @param int $formId Form ID
     * @param array<string, string> $atts Shortcode attributes
     * @param array<int, string> $cssClasses CSS classes
     * @return void
     */
    private function renderFormOutput(int $formId, array $atts, array $cssClasses): void
    {
        $formOutput = ShortcodeService::shortcodeHandler($atts);

        if ($formOutput === '') {
            echo '<div class="ivyforms-form-not-found"><p>' .
                 esc_html(__('Form not found or not published', 'ivyforms')) .
                 '</p></div>';
            return;
        }

        $classAttribute = implode(' ', $cssClasses);

        if ($this->isEditorPreview()) {
            $this->renderEditorPreview($formId, $formOutput, $classAttribute);
            return;
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<div class="' . esc_attr($classAttribute) . '">'
            . $formOutput
            . '</div>';
    }

    /**
     * Render editor preview with embedded form data
     *
     * @param int $formId Form ID
     * @param string $formOutput Form HTML output
     * @param string $classAttribute CSS class attribute
     * @return void
     */
    private function renderEditorPreview(int $formId, string $formOutput, string $classAttribute): void
    {
        $formDataJson = FormPreviewHelper::generateFormDataJson($formId);
        $embeddedScripts = FormPreviewHelper::generateJsonScripts($formId);

        $dataAttr = '';
        if ($formDataJson !== '') {
            $dataAttr = ' data-ivyforms-data="' . esc_attr($formDataJson) . '"';
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<div class="' . esc_attr($classAttribute)
            . ' ivyforms-elementor-editor-preview"'
            . $dataAttr
            . '>'
            . $formOutput
            . '</div>'
            . $embeddedScripts;
    }

    /**
     * Render widget output in the editor (content template)
     *
     * Leave empty to use server-side rendering
     *
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function content_template(): void
    {
        // Empty - forces Elementor to use server-side render() method
    }
}
// phpcs:enable PSR1.Methods.CamelCapsMethodName
