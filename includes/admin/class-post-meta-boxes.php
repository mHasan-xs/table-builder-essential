<?php

/**
 * Post Meta Boxes handler for Table Builder Essential.
 *
 * @since 1.0.0
 * @package table-builder-essential
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/class-admin-base.php';

class Table_Builder_Essential_Post_Meta_Boxes extends Table_Builder_Essential_Admin_Base
{
    protected function init_hooks()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post', [$this, 'save_meta_data']);
    }

    public function add_meta_boxes()
    {
        add_meta_box(
            'table_layout_package_settings',
            __('Pattern Settings', 'table-builder-essential'),
            [$this, 'render_package_meta_box'],
            'table-layout-manager',
            'side',
            'high'
        );
    }

    public function render_package_meta_box($post)
    {
        wp_nonce_field('table_builder_package_meta_nonce', 'table_builder_package_meta_nonce');

        $package_type = get_post_meta($post->ID, '_package_type', true) ?: 'free';
        $required_plugins = get_post_meta($post->ID, '_required_plugins', true) ?: [];
        $live_preview_url = get_post_meta($post->ID, '_live_preview_url', true) ?: '';

?>

        <div class="table-builder-meta-box">
            <div class="form-field table-live-url-field" style="margin-bottom: 15px;">
                <label for="table_live_url">
                    <strong><?php _e('Live Preview URL:', 'table-builder-essential'); ?></strong>
                </label>
                <input type="url" id="table_live_url" name="table_live_url" class="widefat" value="<?php echo esc_url($live_preview_url); ?>" placeholder="https://example.com/demo" />
            </div>


            <div class="form-field package-type-field" >
                <label for="package_type">
                    <strong><?php _e('Package Type:', 'table-builder-essential'); ?></strong>
                </label>
                <select name="package_type" id="package_type" class="postform">
                    <option value="free" <?php selected($package_type, 'free'); ?>>
                        <?php _e('Free', 'table-builder-essential'); ?>
                    </option>
                    <option value="pro" <?php selected($package_type, 'pro'); ?>>
                        <?php _e('Pro', 'table-builder-essential'); ?>
                    </option>
                </select>

            </div>

            <div class="form-field required-plugins-field">
                <label><strong><?php _e('Required Plugins:', 'table-builder-essential'); ?></strong></label>
                <div class="plugins-list">
                    <?php $this->render_plugin_checkboxes($required_plugins); ?>
                </div>
                <p class="description">
                    <?php _e('Select plugins required for this pattern.', 'table-builder-essential'); ?>
                </p>
            </div>
        </div>



<?php
    }

    public function save_meta_data($post_id)
    {
        if (!$this->should_save_meta($post_id)) {
            return;
        }

        if (isset($_POST['package_type'])) {
            $package_type = $this->sanitize_package_type($_POST['package_type']);
            update_post_meta($post_id, '_package_type', $package_type);
        }
        if (isset($_POST['required_plugins']) && is_array($_POST['required_plugins'])) {
            $plugins = array_map('sanitize_text_field', $_POST['required_plugins']);
            update_post_meta($post_id, '_required_plugins', $plugins);
        } else {
            delete_post_meta($post_id, '_required_plugins');
        }
        if (isset($_POST['table_live_url'])) {
            $live_preview_url = esc_url_raw($_POST['table_live_url']);
            update_post_meta($post_id, '_live_preview_url', $live_preview_url);
        }
    }

    private function should_save_meta($post_id)
    {
        if (
            !isset($_POST['table_builder_package_meta_nonce']) ||
            !wp_verify_nonce($_POST['table_builder_package_meta_nonce'], 'table_builder_package_meta_nonce')
        ) {
            return false;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (get_post_type($post_id) !== 'table-layout-manager') {
            return false;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return false;
        }

        return true;
    }
}
