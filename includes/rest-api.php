<?php

/**
 * REST API Controller for Table Layout Manager
 *
 * This class handles REST API endpoints for the table-layout-manager post type
 * and its taxonomies, providing data for the template library.
 *
 * @since 1.0.0
 * @package table-builder-essential
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Table_Builder_Essential_REST_API
{

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes.
     *
     * @since 1.0.0
     */
    public function register_routes()
    {
        // Templates endpoint - matches GutenKit API structure
        register_rest_route('table-builder/v1', '/layout-manager-api/patterns', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'get_templates'],
            'permission_callback' => '__return_true',
            'args' => [
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'default' => 16,
                    'sanitize_callback' => 'absint',
                ],
                'search' => [
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'cat' => [
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'type' => [
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'id' => [
                    'default' => 0,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);

        // Categories endpoint
        register_rest_route('table-builder/v1', '/layout-manager-api/patterns/categories', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'get_categories'],
            'permission_callback' => '__return_true',
        ]);

        // Groups endpoint
        register_rest_route('table-builder/v1', '/layout-manager-api/groups', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'get_groups'],
            'permission_callback' => '__return_true',
        ]);

        // Single template endpoint
        register_rest_route('table-builder/v1', '/layout-manager-api/template/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_single_template'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);

        // Update download count endpoint
        register_rest_route('table-builder/v1', '/layout-manager-api/download-count/(?P<id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'update_download_count'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'validate_callback' => function($param, $request, $key) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);

        // Testing endpoint - Get API status and stats
        register_rest_route('table-builder/v1', '/layout-manager-api/status', [
            'methods' => 'GET',
            'callback' => [$this, 'get_api_status'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Get templates/patterns.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function get_templates($request)
    {
        $page = $request->get_param('page') ?: 1;
        $per_page = $request->get_param('per_page') ?: 16;
        $search = $request->get_param('search') ?: '';
        $category = $request->get_param('cat') ?: '';
        $type = $request->get_param('type') ?: '';
        $template_id = $request->get_param('id') ?: 0;

        $args = [
            'post_type' => 'table-layout-manager',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => [],
            'tax_query' => [],
        ];

        // Handle search
        if (!empty($search)) {
            $args['s'] = $search;
        }

        // Handle specific template ID
        if (!empty($template_id)) {
            $args['p'] = $template_id;
            $args['posts_per_page'] = 1;
        }

        // Handle category filter
        if (!empty($category) && $category !== 'all') {
            $args['tax_query'][] = [
                'taxonomy' => 'table_layout_group_categories',
                'field' => 'slug',
                'terms' => $category,
            ];
        }

        // Handle type filter (free/pro)
        if (!empty($type)) {
            $args['meta_query'][] = [
                'key' => '_package_type',
                'value' => $type,
                'compare' => '=',
            ];
        }

        $query = new WP_Query($args);
        $posts = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // Get author information
                $author_id = get_post_field('post_author', $post_id);
                $author_name = get_the_author_meta('display_name', $author_id);

                // Get meta fields
                $package_type = get_post_meta($post_id, '_package_type', true) ?: 'free';
                $thumbnail = get_post_meta($post_id, '_thumbnail_url', true) ?: '';
                $required_plugins = get_post_meta($post_id, '_required_plugins', true) ?: [];
                $download_count = get_post_meta($post_id, '_download_count', true) ?: 0;

                // Get taxonomies
                $groups = wp_get_post_terms($post_id, 'table_layout_groups');
                $categories = wp_get_post_terms($post_id, 'table_layout_group_categories');

                // Format groups
                $formatted_groups = [];
                foreach ($groups as $group) {
                    $group_package = get_term_meta($group->term_id, '_package_type', true) ?: 'free';
                    $group_thumbnail = get_term_meta($group->term_id, '_thumbnail_url', true) ?: '';
                    
                    $formatted_groups[] = [
                        'id' => $group->term_id,
                        'name' => $group->name,
                        'slug' => $group->slug,
                        'package' => $group_package,
                        'thumbnail' => $group_thumbnail,
                    ];
                }

                // Format categories
                $formatted_categories = [];
                foreach ($categories as $category) {
                    $formatted_categories[] = [
                        'id' => $category->term_id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                }

                $is_pro = ($package_type === 'pro');

                $posts[] = [
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => get_the_excerpt() ?: 'A professional ' . strtolower(str_replace([' Table', ' #'], ['', ''], get_the_title())) . ' designed for modern websites.',
                    'slug' => get_post_field('post_name', $post_id),
                    'date' => get_the_date('Y-m-d H:i:s'),
                    'modified' => get_the_modified_date('Y-m-d H:i:s'),
                    'author' => [
                        'id' => $author_id,
                        'name' => $author_name,
                    ],
                    'thumbnail' => $thumbnail,
                    'featured_image' => $thumbnail, // Alias for compatibility
                    'is_pro' => $is_pro,
                    'pro' => $is_pro, // Alias for compatibility
                    'type' => $package_type,
                    'package' => $package_type,
                    'required_plugins' => $required_plugins,
                    'groups' => $formatted_groups,
                    'categories' => $formatted_categories,
                    'download_count' => (int) $download_count,
                    'meta' => [
                        'download_count' => [(string) $download_count],
                    ],
                ];
            }
            wp_reset_postdata();
        }

        return new WP_REST_Response([
            'posts' => $posts,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
            'per_page' => $per_page,
        ], 200);
    }

    /**
     * Get categories.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function get_categories($request)
    {
        $terms = get_terms([
            'taxonomy' => 'table_layout_group_categories',
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms)) {
            return new WP_REST_Response([], 200);
        }

        $categories = [];
        foreach ($terms as $term) {
            $categories[] = [
                'id' => $term->term_id,
                'title' => $term->name,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'count' => $term->count,
            ];
        }

        return new WP_REST_Response($categories, 200);
    }

    /**
     * Get groups.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function get_groups($request)
    {
        $terms = get_terms([
            'taxonomy' => 'table_layout_groups',
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms)) {
            return new WP_REST_Response([], 200);
        }

        $groups = [];
        foreach ($terms as $term) {
            $package_type = get_term_meta($term->term_id, '_package_type', true) ?: 'free';
            $thumbnail = get_term_meta($term->term_id, '_thumbnail_url', true) ?: '';

            $groups[] = [
                'id' => $term->term_id,
                'title' => $term->name,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'count' => $term->count,
                'package' => $package_type,
                'thumbnail' => $thumbnail,
            ];
        }

        return new WP_REST_Response($groups, 200);
    }

    /**
     * Get single template.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function get_single_template($request)
    {
        $id = $request->get_param('id');
        
        $post = get_post($id);
        if (!$post || $post->post_type !== 'table-layout-manager') {
            return new WP_Error('not_found', 'Template not found', ['status' => 404]);
        }

        // Get author information
        $author_id = get_post_field('post_author', $id);
        $author_name = get_the_author_meta('display_name', $author_id);

        // Get meta fields
        $package_type = get_post_meta($id, '_package_type', true) ?: 'free';
        $thumbnail = get_post_meta($id, '_thumbnail_url', true) ?: '';
        $required_plugins = get_post_meta($id, '_required_plugins', true) ?: [];
        $download_count = get_post_meta($id, '_download_count', true) ?: 0;

        // Get taxonomies
        $groups = wp_get_post_terms($id, 'table_layout_groups');
        $categories = wp_get_post_terms($id, 'table_layout_group_categories');

        $is_pro = ($package_type === 'pro');

        $template = [
            'id' => $id,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'slug' => $post->post_name,
            'date' => $post->post_date,
            'modified' => $post->post_modified,
            'author' => [
                'id' => $author_id,
                'name' => $author_name,
            ],
            'thumbnail' => $thumbnail,
            'is_pro' => $is_pro,
            'type' => $package_type,
            'package' => $package_type,
            'required_plugins' => $required_plugins,
            'groups' => $groups,
            'categories' => $categories,
            'download_count' => (int) $download_count,
        ];

        return new WP_REST_Response($template, 200);
    }

    /**
     * Update download count.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function update_download_count($request)
    {
        $id = $request->get_param('id');
        
        $post = get_post($id);
        if (!$post || $post->post_type !== 'table-layout-manager') {
            return new WP_Error('not_found', 'Template not found', ['status' => 404]);
        }

        $current_count = get_post_meta($id, '_download_count', true) ?: 0;
        $new_count = $current_count + 1;
        
        update_post_meta($id, '_download_count', $new_count);

        return new WP_REST_Response([
            'success' => true,
            'download_count' => $new_count,
        ], 200);
    }

    /**
     * Get API status and statistics.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response
     */
    public function get_api_status($request)
    {
        // Get counts
        $templates_query = new WP_Query([
            'post_type' => 'table-layout-manager',
            'post_status' => 'publish'
        ]);
        
        $categories = get_terms([
            'taxonomy' => 'table_layout_group_categories',
            'hide_empty' => false,
        ]);
        
        $groups = get_terms([
            'taxonomy' => 'table_layout_groups',
            'hide_empty' => false,
        ]);

        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Table Builder Essential REST API is working',
            'version' => '1.0.0',
            'timestamp' => current_time('mysql'),
            'data' => [
                'templates' => $templates_query->found_posts,
                'categories' => is_array($categories) ? count($categories) : 0,
                'groups' => is_array($groups) ? count($groups) : 0,
            ],
            'endpoints' => [
                'patterns' => '/wp-json/table-builder/v1/layout-manager-api/patterns',
                'categories' => '/wp-json/table-builder/v1/layout-manager-api/patterns/categories',
                'groups' => '/wp-json/table-builder/v1/layout-manager-api/groups',
                'single_template' => '/wp-json/table-builder/v1/layout-manager-api/template/{id}',
                'download_count' => '/wp-json/table-builder/v1/layout-manager-api/download-count/{id}',
                'status' => '/wp-json/table-builder/v1/layout-manager-api/status'
            ]
        ], 200);
    }
}

// Initialize the REST API
new Table_Builder_Essential_REST_API();