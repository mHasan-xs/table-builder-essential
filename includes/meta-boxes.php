<?php

/**
 * The Table Builder Essential Custom Meta Box class.
 *
 * This class handles custom fields for Table Layout Manager taxonomies.
 *
 * @since 1.0.0
 * @package table-builder-essential
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Table_Builder_Essential_Meta_Box
{

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        // Add custom fields to table_layout_groups taxonomy
        add_action('table_layout_groups_add_form_fields', [$this, 'add_group_fields']);
        add_action('created_table_layout_groups', [$this, 'save_created_group'], 10, 3);
        add_action('table_layout_groups_edit_form_fields', [$this, 'edit_group_fields']);
        add_action('edited_table_layout_groups', [$this, 'save_edited_group']);

        // Add custom columns to table layout manager post type
        add_filter('manage_table-layout-manager_posts_columns', [$this, 'set_custom_post_columns']);
        add_filter('manage_table-layout-manager_posts_custom_column', [$this, 'get_custom_post_column'], 10, 3);

        // Add custom columns to table_layout_groups taxonomy
        add_filter('manage_edit-table_layout_groups_columns', [$this, 'display_groups_columns']);
        add_filter('manage_table_layout_groups_custom_column', [$this, 'display_groups_column'], 10, 3);

        // Add custom columns to table_layout_group_categories taxonomy
        add_filter('manage_edit-table_layout_group_categories_columns', [$this, 'display_group_categories_columns']);
        add_filter('manage_table_layout_group_categories_custom_column', [$this, 'display_group_categories_column'], 10, 3);

        // Add meta boxes for table-layout-manager post type
        add_action('add_meta_boxes', [$this, 'add_post_meta_boxes']);
        add_action('save_post', [$this, 'save_post_meta']);

        // Add bulk actions for package type
        add_filter('bulk_actions-edit-table-layout-manager', [$this, 'add_bulk_actions']);

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Add custom fields to table_layout_groups taxonomy add form.
     *
     * @since 1.0.0
     * @param string $taxonomy Current taxonomy slug.
     */
    public function add_group_fields($taxonomy)
    {
?>
        <div class="form-field">
            <label for="group_package">Package</label>
            <select name="group_package" id="group_package" class="postform">
                <option value="free" selected="selected">Free</option>
                <option value="pro">Pro</option>
            </select>
            <p class="description">Select package type for this group.</p>
        </div>

        <div class="form-field">
            <label for="group_categories">Group Categories</label>
            <select name="group_categories[]" id="group_categories" class="postform" multiple>
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'table_layout_group_categories',
                    'hide_empty' => false,
                ));
                if (!empty($categories)) {
                    foreach ($categories as $category) {
                        echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
                    }
                }
                ?>
            </select>
            <p class="description">Select group categories (hold Ctrl/Cmd to select multiple).</p>
        </div>

        <div class="form-field">
            <label for="group_thumbnail">Group Thumbnail</label>
            <input type="button" class="button button-secondary" value="Upload Image" id="group_thumbnail_button">
            <input type="hidden" id="group_thumbnail" name="group_thumbnail" value="">
            <div id="group_thumbnail_preview"></div>
            <p class="description">Select an image for this group.</p>
        </div>

        <div class="form-field">
            <label for="required_plugins"><b>Required Plugins:</b></label>
            <?php $this->render_plugin_checkboxes([], true); ?>
            <p class="description">Select the plugins required for this group.</p>
        </div>
    <?php
    }

    /**
     * Save custom fields when group term is created.
     *
     * @since 1.0.0
     * @param int    $term_id  Term ID.
     * @param int    $tt_id    Term taxonomy ID.
     * @param string $taxonomy Taxonomy slug.
     */
    public function save_created_group($term_id, $tt_id, $taxonomy)
    {
        if (isset($_POST['group_package'])) {
            update_term_meta($term_id, 'group_package', sanitize_text_field($_POST['group_package']));
        }

        if (isset($_POST['group_categories'])) {
            $categories = array_map('intval', $_POST['group_categories']);
            update_term_meta($term_id, 'group_categories', $categories);
        }

        if (isset($_POST['group_thumbnail'])) {
            update_term_meta($term_id, 'group_thumbnail', intval($_POST['group_thumbnail']));
        }

        if (isset($_POST['required_plugins']) && is_array($_POST['required_plugins'])) {
            $plugins = array_map('sanitize_text_field', $_POST['required_plugins']);
            update_term_meta($term_id, 'required_plugins', $plugins);
        }
    }

    /**
     * Add custom fields to table_layout_groups taxonomy edit form.
     *
     * @since 1.0.0
     * @param WP_Term $term Current term object.
     */
    public function edit_group_fields($term)
    {
        $package = get_term_meta($term->term_id, 'group_package', true) ?: 'free';
        $group_categories = get_term_meta($term->term_id, 'group_categories', true) ?: [];
        $thumbnail = get_term_meta($term->term_id, 'group_thumbnail', true);
        $required_plugins = get_term_meta($term->term_id, 'required_plugins', true) ?: [];
    ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="group_package">Package</label>
            </th>
            <td>
                <select name="group_package" id="group_package" class="postform">
                    <option value="free" <?php selected($package, 'free'); ?>>Free</option>
                    <option value="pro" <?php selected($package, 'pro'); ?>>Pro</option>
                </select>
                <p class="description">Select package type for this group.</p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="group_categories">Group Categories</label>
            </th>
            <td>
                <select name="group_categories[]" id="group_categories" class="postform" multiple>
                    <?php
                    $categories = get_terms(array(
                        'taxonomy' => 'table_layout_group_categories',
                        'hide_empty' => false,
                    ));
                    if (!empty($categories)) {
                        foreach ($categories as $category) {
                            $selected = is_array($group_categories) && in_array($category->term_id, $group_categories) ? 'selected' : '';
                            echo '<option value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
                        }
                    }
                    ?>
                </select>
                <p class="description">Select group categories (hold Ctrl/Cmd to select multiple).</p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="group_thumbnail">Group Thumbnail</label>
            </th>
            <td>
                <input type="button" class="button button-secondary" value="Upload Image" id="group_thumbnail_button">
                <input type="hidden" id="group_thumbnail" name="group_thumbnail" value="<?php echo esc_attr($thumbnail); ?>">
                <div id="group_thumbnail_preview">
                    <?php if ($thumbnail) : ?>
                        <img src="<?php echo esc_url(wp_get_attachment_image_url($thumbnail, 'thumbnail')); ?>" style="max-width:100px;" />
                    <?php endif; ?>
                </div>
                <p class="description">Select an image for this group.</p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="required_plugins">Required Plugins</label>
            </th>
            <td>
                <?php $this->render_plugin_checkboxes($required_plugins); ?>
                <p class="description">Select the plugins required for this group.</p>
            </td>
        </tr>
    <?php
    }

    /**
     * Save custom fields when group term is edited.
     *
     * @since 1.0.0
     * @param int $term_id Term ID.
     */
    public function save_edited_group($term_id)
    {
        if (isset($_POST['group_package'])) {
            update_term_meta($term_id, 'group_package', sanitize_text_field($_POST['group_package']));
        }

        if (isset($_POST['group_categories'])) {
            $categories = array_map('intval', $_POST['group_categories']);
            update_term_meta($term_id, 'group_categories', $categories);
        } else {
            delete_term_meta($term_id, 'group_categories');
        }

        if (isset($_POST['group_thumbnail'])) {
            update_term_meta($term_id, 'group_thumbnail', intval($_POST['group_thumbnail']));
        }

        if (isset($_POST['required_plugins']) && is_array($_POST['required_plugins'])) {
            $plugins = array_map('sanitize_text_field', $_POST['required_plugins']);
            update_term_meta($term_id, 'required_plugins', $plugins);
        } else {
            delete_term_meta($term_id, 'required_plugins');
        }
    }

    /**
     * Render plugin checkboxes.
     *
     * @since 1.0.0
     * @param array $selected Selected plugins.
     * @param bool  $space    Add spacing between checkboxes.
     */
    private function render_plugin_checkboxes($selected = [], $space = false)
    {
        $plugin_choices = [
            'table-builder' => 'Table Builder',
            'woocommerce' => 'WooCommerce'
        ];

        foreach ($plugin_choices as $value => $label) {
            $checked = in_array($value, $selected) ? 'checked' : '';
            printf(
                '<label><input type="checkbox" name="required_plugins[]" value="%s" %s> %s</label> %s',
                esc_attr($value),
                $checked,
                esc_html($label),
                $space ? '' : '<br>'
            );
        }
    }

    /**
     * Add custom columns to groups taxonomy.
     *
     * @since 1.0.0
     * @param array $columns Existing columns.
     * @return array Modified columns.
     */
    public function display_groups_columns($columns)
    {
        $columns['group_package'] = 'Package';
        $columns['group_categories'] = 'Group Categories';
        $columns['group_thumbnail'] = 'Thumbnail';
        $columns['required_plugins'] = 'Required Plugins';
        return $columns;
    }

    /**
     * Display content for custom group columns.
     *
     * @since 1.0.0
     * @param string $content    Column content.
     * @param string $column_name Column name.
     * @param int    $term_id    Term ID.
     * @return string Column content.
     */
    public function display_groups_column($content, $column_name, $term_id)
    {
        switch ($column_name) {
            case 'group_package':
                $package = get_term_meta($term_id, 'group_package', true);
                $content = !empty($package) ? ucfirst($package) : 'Free';
                break;

            case 'group_categories':
                $group_categories = get_term_meta($term_id, 'group_categories', true);
                if (is_array($group_categories) && !empty($group_categories)) {
                    $category_names = [];
                    foreach ($group_categories as $category_id) {
                        $term = get_term($category_id);
                        if ($term && !is_wp_error($term)) {
                            $category_names[] = '<span style="background: #e1e1e1; padding: 2px 8px; margin: 0 2px; border-radius: 3px; font-size: 11px;">' . $term->name . '</span>';
                        }
                    }
                    $content = implode(' ', $category_names);
                }
                break;

            case 'group_thumbnail':
                $image_id = get_term_meta($term_id, 'group_thumbnail', true);
                if ($image_id) {
                    $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                    if ($image_url) {
                        $content = '<img src="' . esc_url($image_url) . '" style="max-width: 50px; height: auto;" />';
                    }
                }
                break;

            case 'required_plugins':
                $plugins = get_term_meta($term_id, 'required_plugins', true);
                if (is_array($plugins) && !empty($plugins)) {
                    $content = implode(', ', array_map('esc_html', $plugins));
                } else {
                    $content = __('None', 'table-builder-essential');
                }
                break;
        }

        return $content;
    }

    /**
     * Add custom columns to group categories taxonomy.
     *
     * @since 1.0.0
     * @param array $columns Existing columns.
     * @return array Modified columns.
     */
    public function display_group_categories_columns($columns)
    {
        $columns['layout_count'] = 'Layout Count';
        return $columns;
    }

    /**
     * Display content for custom group categories columns.
     *
     * @since 1.0.0
     * @param string $content     Column content.
     * @param string $column_name Column name.
     * @param int    $term_id     Term ID.
     * @return string Column content.
     */
    public function display_group_categories_column($content, $column_name, $term_id)
    {
        if ($column_name === 'layout_count') {
            $groups = get_terms(array(
                'taxonomy' => 'table_layout_groups',
                'hide_empty' => false,
                'meta_query' => array(
                    array(
                        'key' => 'group_categories',
                        'value' => $term_id,
                        'compare' => 'LIKE',
                    ),
                ),
            ));

            if (!empty($groups)) {
                $slugs = array_column($groups, 'slug');
                $content = sprintf(
                    '<a href="%s">%d</a>',
                    admin_url('edit.php?post_type=table-layout-manager&table_layout_groups=' . implode(',', $slugs)),
                    count($groups)
                );
            } else {
                $content = '0';
            }
        }

        return $content;
    }

    /**
     * Add custom columns to table layout manager post type.
     *
     * @since 1.0.0
     * @param array $columns Existing columns.
     * @return array Modified columns.
     */
    public function set_custom_post_columns($columns)
    {
        $columns['package_type'] = __('Package', 'table-builder-essential');
        $columns['download_count'] = __('Download Count', 'table-builder-essential');
        return $columns;
    }

    /**
     * Display content for custom post columns.
     *
     * @since 1.0.0
     * @param string $column  Column name.
     * @param int    $post_id Post ID.
     */
    public function get_custom_post_column($column, $post_id)
    {
        if ($column === 'package_type') {
            $package = get_post_meta($post_id, '_package_type', true) ?: 'free';
            $badge_class = $package === 'pro' ? 'pro-badge' : 'free-badge';
            echo '<span class="package-badge ' . $badge_class . '">' . ucfirst($package) . '</span>';
        } elseif ($column === 'download_count') {
            $count = get_post_meta($post_id, 'download_count', true);
            echo intval($count ?: 0);
        }
    }

    /**
     * Add meta boxes for table-layout-manager post type.
     *
     * @since 1.0.0
     */
    public function add_post_meta_boxes()
    {
        add_meta_box(
            'table_layout_package_settings',
            __('Package Settings', 'table-builder-essential'),
            [$this, 'render_package_meta_box'],
            'table-layout-manager',
            'side',
            'high'
        );
    }

    /**
     * Render package meta box content.
     *
     * @since 1.0.0
     * @param WP_Post $post Current post object.
     */
    public function render_package_meta_box($post)
    {
        // Add nonce for security
        wp_nonce_field('table_builder_package_meta_nonce', 'table_builder_package_meta_nonce');

        $package_type = get_post_meta($post->ID, '_package_type', true) ?: 'free';
        $required_plugins = get_post_meta($post->ID, '_required_plugins', true) ?: [];

    ?>
        <div class="table-builder-meta-box">
            <p class="form-field" style="display: flex; align-items: center; gap: 10px;">
                <label for="package_type"><strong><?php _e('Package Type:', 'table-builder-essential'); ?></strong></label>
                <select name="package_type" id="package_type" class="postform">
                    <option value="free" <?php selected($package_type, 'free'); ?>><?php _e('Free', 'table-builder-essential'); ?></option>
                    <option value="pro" <?php selected($package_type, 'pro'); ?>><?php _e('Pro', 'table-builder-essential'); ?></option>
                </select>
            </p>

            <p class="form-field">
                <label style="display: block; margin-bottom: 8px;"><strong><?php _e('Required Plugins:', 'table-builder-essential'); ?></strong></label>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <?php $this->render_plugin_checkboxes($required_plugins, true); ?>
            </div>
            <small class="description" style="display: block; margin-top: 8px;"><?php _e('Select plugins required for this pattern.', 'table-builder-essential'); ?></small>
            </p>
        </div>
    <?php
    }

    /**
     * Save post meta data.
     *
     * @since 1.0.0
     * @param int $post_id Post ID.
     */
    public function save_post_meta($post_id)
    {
        // Verify nonce
        if (
            !isset($_POST['table_builder_package_meta_nonce']) ||
            !wp_verify_nonce($_POST['table_builder_package_meta_nonce'], 'table_builder_package_meta_nonce')
        ) {
            return;
        }

        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check post type
        if (get_post_type($post_id) !== 'table-layout-manager') {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save package type
        if (isset($_POST['package_type'])) {
            update_post_meta($post_id, '_package_type', sanitize_text_field($_POST['package_type']));
        }

        // Save required plugins
        if (isset($_POST['required_plugins']) && is_array($_POST['required_plugins'])) {
            $plugins = array_map('sanitize_text_field', $_POST['required_plugins']);
            update_post_meta($post_id, '_required_plugins', $plugins);
        } else {
            delete_post_meta($post_id, '_required_plugins');
        }
    }

    /**
     * Add custom bulk actions.
     *
     * @since 1.0.0
     * @param array $actions Existing bulk actions.
     * @return array Modified bulk actions.
     */
    public function add_bulk_actions($actions)
    {
        $actions['set_free'] = __('Set as Free', 'table-builder-essential');
        $actions['set_pro'] = __('Set as Pro', 'table-builder-essential');
        return $actions;
    }



    /**
     * Enqueue scripts and styles.
     */
    public function enqueue_scripts($hook)
    {
        // Load media uploader for groups taxonomy
        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == 'table_layout_groups') {
            wp_enqueue_media();
            wp_enqueue_script(
                'table-builder-meta-box',
                TABLE_BUILDER_ESSENTIAL_PLUGIN_URL . 'assets/js/meta-box.js',
                ['jquery'],
                TABLE_BUILDER_ESSENTIAL_VERSION,
                true
            );
        }
    }
    }

if (class_exists('Table_Builder_Essential_Meta_Box') && is_admin()) {
    new Table_Builder_Essential_Meta_Box();
}
