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
}