<?php

namespace IvyForms\Services\Preview;

use IvyForms\Common\Exceptions\ForbiddenException;
use IvyForms\Common\Sanitizer\Sanitizer;
use IvyForms\Services\API\IvyFormsAPI;
use IvyForms\Services\Permissions\IvyFormsAccess;
use stdClass;
use WP_Post;

/**
 * Class PreviewService
 *
 * @package IvyForms\Services\Preview
 *
 * This class handles the preview functionality of forms in the IvyForms plugin.
 * It sets up a fake post to display the form preview and manages the template redirection.
 */
class PreviewService
{
    protected int $formId = 0;

    /**
     * Get the form ID
     *
     * @return int Form ID
     */
    public function getFormId(): int
    {
        return $this->formId;
    }

    /**
     * Set the form ID
     *
     * @param int $formId Form ID
     */
    public function setFormId(int $formId): void
    {
        $this->formId = $formId;
    }

    /**
     * @throws ForbiddenException
     */
    public function __construct()
    {
        if ($this->isPreview()) {
            $this->init();
        }
    }

    /**
     * Initialize the preview service
     *
     * This function sets up the necessary hooks and filters for the preview functionality.
     * @throws ForbiddenException
     */
    public function init(): void
    {
        $this->checkFormId();
        remove_all_filters('the_content');
        remove_all_filters('get_the_excerpt');

        add_action('template_redirect', array($this, 'templateRedirect'));
        add_filter('template_include', array($this, 'templateInclude'), 1000);

        add_filter('post_thumbnail_html', function () {
            return '';
        });
    }

    /**
     * Check if the current request is for a form preview
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return bool
     */
    public function isPreview(): bool
    {
        return isset($_GET['ivyforms_preview']);
    }

    /**
     * Check if the form ID is valid and the user has permission to view it
     *
     * @SuppressWarnings(PHPMD)
     * @throws ForbiddenException
     */
    public function checkFormId(): bool
    {
        // Verify the nonce
        Sanitizer::verifyNonce($_GET['_wpNonce'] ?? '');

        // Use WordPress functions to safely get and sanitize input
        $formId = absint(sanitize_text_field(wp_unslash($_GET['ivyforms_preview'])));
        if ($formId === 0) {
            wp_die(
                esc_html__('Sorry, the form ID for this preview does not exist.', 'ivyforms'),
                esc_html__('You need to check that the right form ID exists.', 'ivyforms'),
                403
            );
        }

        if (!IvyFormsAccess::canAccessFormPreview()) {
            wp_die(
                esc_html__('Sorry, you are not allowed to visit this preview form as this user.', 'ivyforms'),
                esc_html__('You need a higher level of permission.', 'ivyforms'),
                403
            );
        }

        $this->setFormId($formId);

        if (IvyFormsAPI::formExists($this->getFormId()) === false) {
            wp_die(
                esc_html__('Sorry, the form ID for this preview does not exist.', 'ivyforms'),
                esc_html__('You need to check that the right form ID exists.', 'ivyforms'),
                403
            );
        }
        return true;
    }

    /**
     * Redirect to the template for form preview
     *
     * This function sets up a fake post and updates the
     * main query to display the form preview.
     *
     * @SuppressWarnings(PHPMD)
     */
    public function templateRedirect(): void
    {
        global $wp, $wp_query;

        // Set post ID
        $postId = 0;

        // Post constructor
        $post = new stdClass();
        $post->ID = $postId;
        $post->post_author = 1;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql', 1);
        $post->post_title = __('Preview form', 'ivyforms');
        $post->post_content = sprintf(
            '<a href="%s" class="ivyforms-edit-form-link">Edit form</a>%s',
            esc_url(admin_url('admin.php?page=ivyforms-builder#/manage/' . $this->getFormId())),
            do_shortcode(
                sprintf(
                    '[%s id="%d"]',
                    'ivyforms',
                    $this->getFormId()
                )
            )
        );
        $post->post_status = 'publish';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->post_name = 'ivyforms-post-preview';
        $post->post_type = 'page';
        $post->filter = 'raw';

        // Create fake post
        $wpPost = new WP_Post($post);

        // Add post to cache
        wp_cache_add($postId, $wpPost, 'posts');

        // Update the main query
        $wp_query->post = $wpPost;
        $wp_query->posts = array($wpPost);
        $wp_query->posts_per_page = 1;
        $wp_query->queried_object = $wpPost;
        $wp_query->queried_object_id = $postId;
        $wp_query->found_posts = 1;
        $wp_query->post_count = 1;
        $wp_query->max_num_pages = 1;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
        $wp_query->is_single = false;
        $wp_query->is_attachment = false;
        $wp_query->is_archive = false;
        $wp_query->is_category = false;
        $wp_query->is_tag = false;
        $wp_query->is_tax = false;
        $wp_query->is_author = false;
        $wp_query->is_date = false;
        $wp_query->is_year = false;
        $wp_query->is_month = false;
        $wp_query->is_day = false;
        $wp_query->is_time = false;
        $wp_query->is_search = false;
        $wp_query->is_feed = false;
        $wp_query->is_comment_feed = false;
        $wp_query->is_trackback = false;
        $wp_query->is_home = false;
        $wp_query->is_embed = false;
        $wp_query->is_404 = false;
        $wp_query->is_paged = false;
        $wp_query->is_admin = false;
        $wp_query->is_preview = false;
        $wp_query->is_robots = false;
        $wp_query->is_posts_page = false;
        $wp_query->is_post_type_archive = false;

        // Update globals using proper WordPress functions
        $GLOBALS['wp_query'] = $wp_query;
        $wp->register_globals();
    }

    public function templateInclude(): string
    {
        return IVYFORMS_PATH . '/view/backend/preview.php';
    }
}
