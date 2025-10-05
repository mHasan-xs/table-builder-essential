<?php

/**
 * Admin Columns handler for Table Builder Essential.
 *
 * @since 1.0.0
 * @package table-builder-essential
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/class-admin-base.php';

class Table_Builder_Essential_Admin_Columns extends Table_Builder_Essential_Admin_Base
{
    protected function init_hooks()
    {
        add_filter('manage_table-layout-manager_posts_columns', [$this, 'add_post_columns']);
        add_action('manage_table-layout-manager_posts_custom_column', [$this, 'render_post_column'], 10, 2);
        add_filter('manage_edit-table_layout_groups_columns', [$this, 'add_groups_columns']);
        add_filter('manage_table_layout_groups_custom_column', [$this, 'render_groups_column'], 10, 3);
        add_filter('manage_edit-table_layout_group_categories_columns', [$this, 'add_group_categories_columns']);
        add_filter('manage_table_layout_group_categories_custom_column', [$this, 'render_group_categories_column'], 10, 3);
    }

    public function add_post_columns($columns)
    {
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['package_type'] = __('Package', 'table-builder-essential');
            }
        }
        $new_columns['download_count'] = __('Downloads', 'table-builder-essential');
        
        return $new_columns;
    }

    public function render_post_column($column, $post_id)
    {
        switch ($column) {
            case 'package_type':
                $package = get_post_meta($post_id, '_package_type', true) ?: 'free';
                echo $this->get_package_badge($package);
                break;

            case 'download_count':
                $count = get_post_meta($post_id, '_download_count', true) ?: 0;
                echo '<span class="download-count">' . number_format($count) . '</span>';
                break;
        }
    }

    public function add_groups_columns($columns)
    {
        $columns['group_package'] = __('Package', 'table-builder-essential');
        $columns['group_categories'] = __('Categories', 'table-builder-essential');
        $columns['group_thumbnail'] = __('Thumbnail', 'table-builder-essential');
        $columns['required_plugins'] = __('Required Plugins', 'table-builder-essential');
        return $columns;
    }

    public function render_groups_column($content, $column_name, $term_id)
    {
        switch ($column_name) {
            case 'group_package':
                $package = get_term_meta($term_id, 'group_package', true) ?: 'free';
                return $this->get_package_badge($package);

            case 'group_categories':
                return $this->render_group_categories_tags($term_id);

            case 'group_thumbnail':
                return $this->render_group_thumbnail($term_id);

            case 'required_plugins':
                return $this->render_required_plugins($term_id);
        }

        return $content;
    }

    public function add_group_categories_columns($columns)
    {
        $columns['layout_count'] = __('Patterns Count', 'table-builder-essential');
        return $columns;
    }

    public function render_group_categories_column($content, $column_name, $term_id)
    {
        if ($column_name === 'layout_count') {
            return $this->render_layout_count($term_id);
        }

        return $content;
    }

    private function render_group_categories_tags($term_id)
    {
        $group_categories = get_term_meta($term_id, 'group_categories', true);
        
        if (!is_array($group_categories) || empty($group_categories)) {
            return '<span class="na">—</span>';
        }

        $category_tags = [];
        foreach ($group_categories as $category_id) {
            $term = get_term($category_id);
            if ($term && !is_wp_error($term)) {
                $category_tags[] = sprintf(
                    '<span class="category-tag">%s</span>',
                    esc_html($term->name)
                );
            }
        }

        return implode(' ', $category_tags);
    }

    /**
     * Render group thumbnail.
     *
     * @param int $term_id Term ID.
     * @return string HTML content.
     */
    private function render_group_thumbnail($term_id)
    {
        $image_id = get_term_meta($term_id, 'group_thumbnail', true);
        
        if (!$image_id) {
            return '<span class="na">—</span>';
        }

        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
        if (!$image_url) {
            return '<span class="na">—</span>';
        }

        return sprintf(
            '<img src="%s" alt="" class="group-thumbnail" style="max-width: 50px; height: auto; border-radius: 4px;" />',
            esc_url($image_url)
        );
    }

    /**
     * Render required plugins.
     *
     * @param int $term_id Term ID.
     * @return string HTML content.
     */
    private function render_required_plugins($term_id)
    {
        $plugins = get_term_meta($term_id, 'required_plugins', true);
        
        if (!is_array($plugins) || empty($plugins)) {
            return '<span class="na">—</span>';
        }

        $plugin_names = [];
        foreach ($plugins as $plugin) {
            $label = $this->plugin_choices[$plugin] ?? ucfirst($plugin);
            $plugin_names[] = esc_html($label);
        }

        return implode(', ', $plugin_names);
    }

    /**
     * Render layout count with link.
     *
     * @param int $term_id Term ID.
     * @return string HTML content.
     */
    private function render_layout_count($term_id)
    {
        $groups = get_terms([
            'taxonomy' => 'table_layout_groups',
            'hide_empty' => false,
            'meta_query' => [
                [
                    'key' => 'group_categories',
                    'value' => $term_id,
                    'compare' => 'LIKE',
                ],
            ],
        ]);

        if (empty($groups)) {
            return '<span class="count">0</span>';
        }

        $count = count($groups);
        $slugs = array_column($groups, 'slug');
        $url = admin_url('edit.php?post_type=table-layout-manager&table_layout_groups=' . implode(',', $slugs));

        return sprintf(
            '<a href="%s" class="count">%d</a>',
            esc_url($url),
            $count
        );
    }
}