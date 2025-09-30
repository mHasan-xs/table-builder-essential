<?php

/**
 * Custom Post Types for Table Builder Essential
 *
 * This file registers custom post types and taxonomies
 * for the Table Builder Essential plugin.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the 'template' post type for table templates
 */
function table_builder_essential_register_template_post_type()
{
    $args = array(
        'label' => __('Table Templates', 'table-builder-essential'),
        'labels' => array(
            'name' => __('Table Templates', 'table-builder-essential'),
            'singular_name' => __('Table Template', 'table-builder-essential'),
            'menu_name' => __('Templates', 'table-builder-essential'),
            'add_new' => __('Add New', 'table-builder-essential'),
            'add_new_item' => __('Add New Template', 'table-builder-essential'),
            'edit_item' => __('Edit Template', 'table-builder-essential'),
            'new_item' => __('New Template', 'table-builder-essential'),
            'view_item' => __('View Template', 'table-builder-essential'),
            'search_items' => __('Search Templates', 'table-builder-essential'),
            'not_found' => __('No templates found', 'table-builder-essential'),
            'not_found_in_trash' => __('No templates found in trash', 'table-builder-essential'),
        ),
        'description' => __('Custom post type for table templates', 'table-builder-essential'),
        'public' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'show_in_admin_bar' => true,
        'show_in_rest' => true,
        'rest_base' => 'template',
        'menu_position' => 20,
        'menu_icon' => 'dashicons-grid-view',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'author',
            'custom-fields',
            'revisions',
        ),
        'has_archive' => true,
        'rewrite' => array(
            'slug' => 'table-template',
            'with_front' => false,
        ),
        'query_var' => true,
        'can_export' => true,
        'delete_with_user' => false,
    );

    register_post_type('template', $args);
}

add_action('init', 'table_builder_essential_register_template_post_type');

/**
 * Register taxonomies for table templates
 */
function table_builder_essential_register_taxonomies()
{
    // Template Category taxonomy
    $category_args = array(
        'labels' => array(
            'name' => __('Template Categories', 'table-builder-essential'),
            'singular_name' => __('Template Category', 'table-builder-essential'),
            'search_items' => __('Search Categories', 'table-builder-essential'),
            'all_items' => __('All Categories', 'table-builder-essential'),
            'parent_item' => __('Parent Category', 'table-builder-essential'),
            'parent_item_colon' => __('Parent Category:', 'table-builder-essential'),
            'edit_item' => __('Edit Category', 'table-builder-essential'),
            'update_item' => __('Update Category', 'table-builder-essential'),
            'add_new_item' => __('Add New Category', 'table-builder-essential'),
            'new_item_name' => __('New Category Name', 'table-builder-essential'),
            'menu_name' => __('Categories', 'table-builder-essential'),
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
        'show_in_rest' => true,
        'rest_base' => 'template-category',
    );

    register_taxonomy('template-category', array('template'), $category_args);

    // Template Tags taxonomy
    $tags_args = array(
        'labels' => array(
            'name' => __('Template Tags', 'table-builder-essential'),
            'singular_name' => __('Template Tag', 'table-builder-essential'),
            'search_items' => __('Search Tags', 'table-builder-essential'),
            'popular_items' => __('Popular Tags', 'table-builder-essential'),
            'all_items' => __('All Tags', 'table-builder-essential'),
            'edit_item' => __('Edit Tag', 'table-builder-essential'),
            'update_item' => __('Update Tag', 'table-builder-essential'),
            'add_new_item' => __('Add New Tag', 'table-builder-essential'),
            'new_item_name' => __('New Tag Name', 'table-builder-essential'),
            'menu_name' => __('Tags', 'table-builder-essential'),
        ),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_nav_menus' => true,
        'show_tagcloud' => true,
        'show_in_rest' => true,
        'rest_base' => 'template-tags',
    );

    register_taxonomy('template-tags', array('template'), $tags_args);
}

add_action('init', 'table_builder_essential_register_taxonomies');

/**
 * Flush rewrite rules on plugin activation
 */
function table_builder_essential_flush_rewrite_rules()
{
    table_builder_essential_register_template_post_type();
    table_builder_essential_register_taxonomies();
    flush_rewrite_rules();
}

// Activation hook is registered in the main plugin file

/**
 * Add custom columns to the template post type admin list
 */
function table_builder_essential_custom_columns($columns)
{
    $columns['template_category'] = __('Category', 'table-builder-essential');
    $columns['template_tags'] = __('Tags', 'table-builder-essential');
    return $columns;
}

add_filter('manage_template_posts_columns', 'table_builder_essential_custom_columns');

/**
 * Display custom column content
 */
function table_builder_essential_custom_column_content($column_name, $post_id)
{
    switch ($column_name) {
        case 'template_category':
            $categories = get_the_terms($post_id, 'template-category');
            if ($categories && !is_wp_error($categories)) {
                $category_names = array();
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                }
                echo implode(', ', $category_names);
            } else {
                echo __('No categories', 'table-builder-essential');
            }
            break;

        case 'template_tags':
            $tags = get_the_terms($post_id, 'template-tags');
            if ($tags && !is_wp_error($tags)) {
                $tag_names = array();
                foreach ($tags as $tag) {
                    $tag_names[] = $tag->name;
                }
                echo implode(', ', $tag_names);
            } else {
                echo __('No tags', 'table-builder-essential');
            }
            break;
    }
}

add_action('manage_template_posts_custom_column', 'table_builder_essential_custom_column_content', 10, 2);
