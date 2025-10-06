<?php

/**
 * Assets Manager for Table Builder Essential admin.
 *
 * @since 1.0.0
 * @package table-builder-essential
 */

if (!defined('ABSPATH')) exit;

class Table_Builder_Essential_Assets_Manager
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function enqueue_admin_assets($hook)
    {
        $this->enqueue_taxonomy_assets($hook);
        $this->enqueue_admin_styles($hook);
    }

    private function enqueue_taxonomy_assets($hook)
    {
        if (!isset($_GET['taxonomy']) || $_GET['taxonomy'] !== 'table_layout_groups') {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'table-builder-meta-box',
            TABLE_BUILDER_ESSENTIAL_URL . 'assets/js/meta-box.js',
            ['jquery'],
            TABLE_BUILDER_ESSENTIAL_VERSION,
            true
        );
    }

    private function enqueue_admin_styles($hook)
    {
        if (!$this->is_table_layout_manager_page($hook)) {
            return;
        }

        wp_add_inline_style('wp-admin', $this->get_admin_css());
    }

    private function is_table_layout_manager_page($hook)
    {
        if ($hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'table-layout-manager') {
            return true;
        }

        if (in_array($hook, ['post.php', 'post-new.php'])) {
            global $post;
            return $post && $post->post_type === 'table-layout-manager';
        }

        return false;
    }

    private function get_admin_css()
    {
        return '
            /* Package Badge Styles */
            .package-badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                line-height: 1;
            }
            .free-badge {
                background-color: #d1f2eb;
                color: #0d7c3b;
                border: 1px solid #a7e5d4;
            }
            .pro-badge {
                background-color: #fef7e3;
                color: #b7791f;
                border: 1px solid #f4d03f;
            }
        ';
    }
}