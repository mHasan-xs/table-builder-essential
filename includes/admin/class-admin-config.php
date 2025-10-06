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