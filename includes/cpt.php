<?php

/**
 * Custom Post Types for Table Builder Essential
 *
 * This file registers custom post types and taxonomies
 * for the Table Builder Essential plugin.
 */

if (!defined('ABSPATH')) exit;

function table_builder_essential_register_template_post_type()
{
    $labels = array(
        'name'                  => _x('Templates', 'Post Type General Name', 'table-builder-essential'),
        'singular_name'         => _x('Template', 'Post Type Singular Name', 'table-builder-essential'),
        'menu_name'             => _x('Templates', 'Admin Menu text', 'table-builder-essential'),
        'name_admin_bar'        => _x('Template', 'Add New on Toolbar', 'table-builder-essential'),
        'archives'              => __('Template Archives', 'table-builder-essential'),
        'attributes'            => __('Template Attributes', 'table-builder-essential'),
        'parent_item_colon'     => __('Parent Template:', 'table-builder-essential'),
        'all_items'             => __('All Templates', 'table-builder-essential'),
        'add_new_item'          => __('Add New Template', 'table-builder-essential'),
        'add_new'               => __('Add New', 'table-builder-essential'),
        'new_item'              => __('New Template', 'table-builder-essential'),
        'edit_item'             => __('Edit Template', 'table-builder-essential'),
        'update_item'           => __('Update Template', 'table-builder-essential'),
        'view_item'             => __('View Template', 'table-builder-essential'),
        'view_items'            => __('View Templates', 'table-builder-essential'),
        'search_items'          => __('Search Template', 'table-builder-essential'),
        'not_found'             => __('Not found', 'table-builder-essential'),
        'not_found_in_trash'    => __('Not found in Trash', 'table-builder-essential'),
        'featured_image'        => __('Featured Image', 'table-builder-essential'),
        'set_featured_image'    => __('Set featured image', 'table-builder-essential'),
        'remove_featured_image' => __('Remove featured image', 'table-builder-essential'),
        'use_featured_image'    => __('Use as featured image', 'table-builder-essential'),
        'insert_into_item'      => __('Insert into Template', 'table-builder-essential'),
        'uploaded_to_this_item' => __('Uploaded to this Template', 'table-builder-essential'),
        'items_list'            => __('Templates list', 'table-builder-essential'),
        'items_list_navigation' => __('Templates list navigation', 'table-builder-essential'),
        'filter_items_list'     => __('Filter Templates list', 'table-builder-essential'),
    );

    $args = array(
        'label'                 => __('Template', 'table-builder-essential'),
        'description'           => __('Table Builder Essential Templates', 'table-builder-essential'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'taxonomies'            => array(),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => false,
        'menu_position'         => 5,
        'show_in_admin_bar'     => false,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'table-builder-templates',
    );

    register_post_type('table-builder-template', $args);
}

add_action('init', 'table_builder_essential_register_template_post_type');

function table_builder_essential_register_layout_manager_post_type()
{
    $labels = array(
        'name'                  => _x('Table Layout Manager', 'Post Type General Name', 'table-builder-essential'),
        'singular_name'         => _x('Table Layout Manager', 'Post Type Singular Name', 'table-builder-essential'),
        'menu_name'             => _x('Table Layout Manager', 'Admin Menu text', 'table-builder-essential'),
        'name_admin_bar'        => _x('Table Layout Manager', 'Add New on Toolbar', 'table-builder-essential'),
        'archives'              => __('Table Layout Manager Archives', 'table-builder-essential'),
        'attributes'            => __('Table Layout Manager Attributes', 'table-builder-essential'),
        'parent_item_colon'     => __('Parent Table Layout Manager:', 'table-builder-essential'),
        'all_items'             => __('All Layouts', 'table-builder-essential'),
        'add_new_item'          => __('Add New Layout', 'table-builder-essential'),
        'add_new'               => __('Add New', 'table-builder-essential'),
        'new_item'              => __('New Table Layout Manager', 'table-builder-essential'),
        'edit_item'             => __('Edit Table Layout Manager', 'table-builder-essential'),
        'update_item'           => __('Update Table Layout Manager', 'table-builder-essential'),
        'view_item'             => __('View Table Layout Manager', 'table-builder-essential'),
        'view_items'            => __('View Table Layout Manager', 'table-builder-essential'),
        'search_items'          => __('Search Table Layout Manager', 'table-builder-essential'),
        'not_found'             => __('Not found', 'table-builder-essential'),
        'not_found_in_trash'    => __('Not found in Trash', 'table-builder-essential'),
        'featured_image'        => __('Featured Image', 'table-builder-essential'),
        'set_featured_image'    => __('Set featured image', 'table-builder-essential'),
        'remove_featured_image' => __('Remove featured image', 'table-builder-essential'),
        'use_featured_image'    => __('Use as featured image', 'table-builder-essential'),
        'insert_into_item'      => __('Insert into Table Layout Manager', 'table-builder-essential'),
        'uploaded_to_this_item' => __('Uploaded to this Table Layout Manager', 'table-builder-essential'),
        'items_list'            => __('Table Layout Manager list', 'table-builder-essential'),
        'items_list_navigation' => __('Table Layout Manager list navigation', 'table-builder-essential'),
        'filter_items_list'     => __('Filter Table Layout Manager list', 'table-builder-essential'),
    );

    $args = array(
        'label'                 => __('Table Layout Manager', 'table-builder-essential'),
        'description'           => __('Manage table layouts and configurations', 'table-builder-essential'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'custom-fields', 'author'),
        'taxonomies'            => array('table_layout_groups', 'table_layout_group_categories'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-grid-view',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'table-layout-manager',
    );

    register_post_type('table-layout-manager', $args);
}

add_action('init', 'table_builder_essential_register_layout_manager_post_type');

/**
 * Register taxonomies for table layout manager
 */
function table_builder_essential_register_layout_manager_taxonomies()
{
    // Register Pattern Categories taxonomy
    $pattern_category_labels = array(
        'name'              => _x('Pattern Categories', 'taxonomy general name', 'table-builder-essential'),
        'singular_name'     => _x('Pattern Category', 'taxonomy singular name', 'table-builder-essential'),
        'search_items'      => __('Search Pattern Categories', 'table-builder-essential'),
        'all_items'         => __('All Pattern Categories', 'table-builder-essential'),
        'parent_item'       => __('Parent Pattern Category', 'table-builder-essential'),
        'parent_item_colon' => __('Parent Pattern Category:', 'table-builder-essential'),
        'edit_item'         => __('Edit Pattern Category', 'table-builder-essential'),
        'update_item'       => __('Update Pattern Category', 'table-builder-essential'),
        'add_new_item'      => __('Add New Pattern Category', 'table-builder-essential'),
        'new_item_name'     => __('New Pattern Category Name', 'table-builder-essential'),
        'menu_name'         => __('Pattern Categories', 'table-builder-essential'),
    );

    $pattern_category_args = array(
        'labels'             => $pattern_category_labels,
        'description'        => __('Categories for table layout patterns', 'table-builder-essential'),
        'hierarchical'       => true,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_nav_menus'  => false,
        'show_tagcloud'      => true,
        'show_in_quick_edit' => true,
        'show_admin_column'  => true,
        'show_in_rest'       => true,
        'rest_base'          => 'table-pattern-categories',
    );
    register_taxonomy('table_pattern_categories', array('table-layout-manager'), $pattern_category_args);
}

add_action('init', 'table_builder_essential_register_layout_manager_taxonomies');

/**
 * Flush rewrite rules on plugin activation
 */
function table_builder_essential_flush_rewrite_rules()
{
    table_builder_essential_register_template_post_type();
    table_builder_essential_register_layout_manager_post_type();
    table_builder_essential_register_layout_manager_taxonomies();
    flush_rewrite_rules();
}

register_activation_hook(TABLE_BUILDER_ESSENTIAL_FILE, 'table_builder_essential_flush_rewrite_rules');
