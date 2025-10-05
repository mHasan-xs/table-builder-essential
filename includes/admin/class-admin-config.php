<?php

/**
 * Configuration for Table Builder Essential Admin Components.
 *
 * @since 1.0.0
 * @package table-builder-essential
 */

if (!defined('ABSPATH')) exit;

class Table_Builder_Essential_Admin_Config
{
    public static function get_plugin_choices()
    {
        return apply_filters('table_builder_essential_plugin_choices', [
            'table-builder' => __('Table Builder', 'table-builder-essential'),
            'woocommerce' => __('WooCommerce', 'table-builder-essential')
        ]);
    }

    public static function get_package_types()
    {
        return apply_filters('table_builder_essential_package_types', [
            'free' => __('Free', 'table-builder-essential'),
            'pro' => __('Pro', 'table-builder-essential'),
        ]);
    }

    public static function get_post_type_settings()
    {
        return apply_filters('table_builder_essential_post_type_settings', [
            'post_type' => 'table-layout-manager',
            'meta_key_package' => '_package_type',
            'meta_key_plugins' => '_required_plugins',
            'meta_key_downloads' => '_download_count',
        ]);
    }

    public static function get_taxonomy_settings()
    {
        return apply_filters('table_builder_essential_taxonomy_settings', [
            'groups_taxonomy' => 'table_layout_groups',
            'categories_taxonomy' => 'table_layout_group_categories',
        ]);
    }

    public static function get_column_settings()
    {
        return apply_filters('table_builder_essential_column_settings', [
            'package_column_width' => '90px',
            'downloads_column_width' => '80px',
            'thumbnail_column_width' => '80px',
            'show_package_badges' => true,
            'show_download_counts' => true,
        ]);
    }

    public static function get_styling_settings()
    {
        return apply_filters('table_builder_essential_styling_settings', [
            'free_badge_color' => '#0d7c3b',
            'free_badge_bg' => '#d1f2eb',
            'free_badge_border' => '#a7e5d4',
            'pro_badge_color' => '#b7791f',
            'pro_badge_bg' => '#fef7e3',
            'pro_badge_border' => '#f4d03f',
        ]);
    }

    public static function is_feature_enabled($feature)
    {
        $enabled_features = apply_filters('table_builder_essential_enabled_features', [
            'post_meta_boxes' => true,
            'taxonomy_fields' => true,
            'admin_columns' => true,
            'bulk_actions' => true,
            'assets_manager' => true,
        ]);

        return $enabled_features[$feature] ?? false;
    }
}