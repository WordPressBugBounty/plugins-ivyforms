<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div class="ivyforms-preview">
        <header class="ivyforms-preview-header">
            <h1><?php the_title(); ?></h1>
        </header>
        <?php
        if(have_posts()) {
            while(have_posts()) {
                the_post();
                ?>
                <section class="ivyforms-preview-content">
                    <?php the_content(); ?>
                </section>
                <?php
            }
        }
        ?>
    </div>
    <?php wp_footer(); ?>
</body>
</html>