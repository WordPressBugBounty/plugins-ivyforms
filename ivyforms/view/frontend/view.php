<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly

use IvyForms\Services\Field\FieldType;
use IvyForms\Services\Shortcode\ShortcodeService;

/** @var int $counter */
$form = ShortcodeService::$formList[$counter];
$formId = $form['id'] ?? 0;
$customCssClass = '';
if (!empty($form['form']['styleSettings']['customCssClass'])) {
    $customCssClass = ' ' . esc_attr($form['form']['styleSettings']['customCssClass']);
}
?>

<div id="ivyforms-app-<?php echo esc_attr($counter) ?>"
     class="ivyforms ivyforms-frontend ivyforms-form-<?php echo esc_attr($formId) ?> ivyforms-form-wrapper-<?php echo esc_attr($formId) ?><?php echo $customCssClass ?>"
     data-counter="<?php echo esc_attr($counter) ?>"
     data-post-id="<?php echo esc_attr(get_the_ID()); ?>"
     data-referer-url="<?php echo esc_attr(wp_get_referer() ?: ''); ?>"
>
    <form-render id="ivyforms-form-<?php echo esc_attr($counter) ?>"></form-render>
</div>
<!-- Wrapper for skeleton -->
<div class="ivyforms-skeleton ivyforms-skeleton-<?php echo esc_attr($counter)?>" >
    <div class="ivyforms-skeleton-title ivyforms-skeleton-animate"></div>

    <?php for ($i = 0; $i < count($form['fields']); $i++): ?>
        <?php $type = FieldType::isValid($form['fields'][$i]['type']) ? $form['fields'][$i]['type'] : '' ?>
        <div class="ivyforms-skeleton-field">
            <div class="ivyforms-skeleton-label ivyforms-skeleton-animate"></div>
            <div class="ivyforms-skeleton-input ivyforms-skeleton-<?php echo esc_attr($type) ?> ivyforms-skeleton-animate"></div>
        </div>
    <?php endfor; ?>

    <div class="ivyforms-skeleton-button ivyforms-skeleton-animate"></div>
</div>