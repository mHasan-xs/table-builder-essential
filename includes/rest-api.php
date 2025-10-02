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
            'methods' => ['GET'],
            'callback' => [$this, 'get_templates'],
            'permission_callback' => [$this, 'check_read_permission'],
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
                    'validate_callback' => [$this, 'validate_search_param'],
                ],
                'cat' => [
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [$this, 'validate_category_param'],
                ],
                'type' => [
                    'default' => '',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [$this, 'validate_type_param'],
                ],
                'id' => [
                    'default' => 0,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => [$this, 'validate_id_param'],
                ],
                'sort' => [
                    'default' => 'recent',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => [$this, 'validate_sort_param'],
                ],
            ],
        ]);

        // Categories endpoint
        register_rest_route('table-builder/v1', '/layout-manager-api/patterns/categories', [
            'methods' => ['GET'],
            'callback' => [$this, 'get_categories'],
            'permission_callback' => [$this, 'check_read_permission'],
        ]);



        // Single template endpoint
        register_rest_route('table-builder/v1', '/layout-manager-api/template/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_single_template'],
            'permission_callback' => [$this, 'check_read_permission'],
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
            'permission_callback' => [$this, 'check_write_permission'],
            'args' => [
                'id' => [
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);

        // Testing endpoint - Get API status and stats
        register_rest_route('table-builder/v1', '/layout-manager-api/status', [
            'methods' => 'GET',
            'callback' => [$this, 'get_api_status'],
            'permission_callback' => [$this, 'check_read_permission'],
        ]);
    }

    /**
     * Check read permission for API endpoints.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full data about the request.
     * @return bool True if user can read, false otherwise.
     */
    public function check_read_permission($request)
    {
        // Allow read access for all users (public data)
        // You can add more restrictive checks here if needed
        return true;
    }

    /**
     * Check write permission for API endpoints.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full data about the request.
     * @return bool True if user can write, false otherwise.
     */
    public function check_write_permission($request)
    {
        // For write operations, require user to be logged in
        if (!is_user_logged_in()) {
            return false;
        }

        // Check for rate limiting
        if (!$this->check_rate_limit($request)) {
            return new WP_Error('too_many_requests', 'Too many requests. Please try again later.', ['status' => 429]);
        }

        return true;
    }

    /**
     * Simple rate limiting for write operations.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Full data about the request.
     * @return bool True if within rate limit, false otherwise.
     */
    private function check_rate_limit($request)
    {
        $user_id = get_current_user_id();
        $ip_address = $this->get_client_ip();
        $key = 'table_builder_rate_limit_' . md5($user_id . $ip_address);

        $requests = get_transient($key);
        if ($requests === false) {
            $requests = 1;
            set_transient($key, $requests, 60); // 1 minute window
            return true;
        }

        if ($requests >= 10) { // Max 10 requests per minute
            return false;
        }

        set_transient($key, $requests + 1, 60);
        return true;
    }

    /**
     * Get client IP address.
     *
     * @since 1.0.0
     * @return string Client IP address.
     */
    private function get_client_ip()
    {
        $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }

    /**
     * Validate search parameter.
     *
     * @since 1.0.0
     * @param mixed $param The parameter value.
     * @param WP_REST_Request $request Full data about the request.
     * @param string $key The parameter key.
     * @return bool True if valid, false otherwise.
     */
    public function validate_search_param($param, $request, $key)
    {
        if (empty($param) || $param === '') {
            return true;
        }

        // Prevent XSS and injection attacks
        if (strlen($param) > 100) {
            return false;
        }

        // Allow more characters for search (letters, numbers, spaces, common punctuation)
        // More permissive pattern to allow broader search terms
        if (!preg_match('/^[\p{L}\p{N}\s\-_\.\#\@\+\(\)\,\'\"\!\?\&\%\$\*\=\[\]\{\}\|\;\:]+$/u', $param)) {
            return false;
        }

        return true;
    }
    /**
     * Validate category parameter.
     *
     * @since 1.0.0
     * @param mixed $param The parameter value.
     * @param WP_REST_Request $request Full data about the request.
     * @param string $key The parameter key.
     * @return bool True if valid, false otherwise.
     */
    public function validate_category_param($param, $request, $key)
    {
        if (empty($param) || $param === '' || $param === 'all') {
            return true;
        }

        // Only allow alphanumeric, hyphens, and underscores (valid slug format)
        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $param)) {
            return false;
        }

        return true;
    }
    /**
     * Validate type parameter.
     *
     * @since 1.0.0
     * @param mixed $param The parameter value.
     * @param WP_REST_Request $request Full data about the request.
     * @param string $key The parameter key.
     * @return bool True if valid, false otherwise.
     */
    public function validate_type_param($param, $request, $key)
    {
        if (empty($param) || $param === '') {
            return true;
        }

        // Only allow specific values
        $allowed_types = ['all', 'free', 'pro'];
        return in_array($param, $allowed_types, true);
    }

    /**
     * Validate sort parameter.
     *
     * @since 1.0.0
     * @param mixed $param The parameter value.
     * @param WP_REST_Request $request Full data about the request.
     * @param string $key The parameter key.
     * @return bool True if valid, false otherwise.
     */
    public function validate_sort_param($param, $request, $key)
    {
        if (empty($param) || $param === '') {
            return true;
        }

        // Only allow specific values (including default)
        $allowed_sorts = ['recent', 'popular'];
        return in_array($param, $allowed_sorts, true);
    }

    /**
     * Validate ID parameter.
     *
     * @since 1.0.0
     * @param mixed $param The parameter value.
     * @param WP_REST_Request $request Full data about the request.
     * @param string $key The parameter key.
     * @return bool True if valid, false otherwise.
     */
    public function validate_id_param($param, $request, $key)
    {
        // Allow 0 (default) or positive integers
        return is_numeric($param) && $param >= 0;
    }

    /**
     * Sanitize and escape output data.
     *
     * @since 1.0.0
     * @param mixed $data The data to sanitize.
     * @return mixed Sanitized data.
     */
    private function sanitize_output($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize_output'], $data);
        }

        if (is_string($data)) {
            // Escape HTML entities to prevent XSS
            return wp_kses_post($data);
        }

        return $data;
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
        // Sanitize and validate all input parameters
        $page = max(1, absint($request->get_param('page') ?: 1));
        $per_page = min(100, max(1, absint($request->get_param('per_page') ?: 16))); // Limit to 100 per page
        $search = sanitize_text_field($request->get_param('search') ?: '');
        $category = sanitize_text_field($request->get_param('cat') ?: '');
        $type = sanitize_text_field($request->get_param('type') ?: '');
        $template_id = absint($request->get_param('id') ?: 0);
        $sort = sanitize_text_field($request->get_param('sort') ?: 'recent');

        // Additional security checks
        if (strlen($search) > 100) {
            return new WP_Error('invalid_search', 'Search query too long.', ['status' => 400]);
        }

        $args = [
            'post_type' => 'table-layout-manager',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => [],
            'tax_query' => [],
        ];

        // Handle sorting
        if ($sort === 'popular') {
            $args['meta_key'] = '_download_count';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
        } else {
            // Default to recent (by date)
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
        }

        // Handle search - ALGOLIA-STYLE EFFICIENT SEARCH
        if (!empty($search) && strlen(trim($search)) > 0) {
            global $wpdb;
            $clean_search = trim($search);
            
            // Cache key for search results (5-minute cache)
            $cache_key = 'tb_search_' . md5($clean_search . '_' . $page . '_' . $per_page);
            $cached_results = wp_cache_get($cache_key, 'table_builder_search');
            
            if ($cached_results !== false) {
                // Return cached results for instant search
                $title_matches = $cached_results;
            } else {
                // Build advanced search query with multiple relevance factors
                $search_words = explode(' ', $clean_search);
                $search_conditions = [];
                $order_cases = [];
                $bind_params = [];
                
                // Create search conditions for each word
                foreach ($search_words as $index => $word) {
                    if (strlen(trim($word)) > 0) {
                        $word = trim($word);
                        $word_param = '%' . $wpdb->esc_like($word) . '%';
                        $search_conditions[] = "LOWER(post_title) LIKE LOWER(%s)";
                        $bind_params[] = $word_param;
                    }
                }
                
                if (!empty($search_conditions)) {
                    // Advanced relevance scoring
                    $full_search_term = '%' . $wpdb->esc_like($clean_search) . '%';
                    $starts_with_term = $wpdb->esc_like($clean_search) . '%';
                    
                    $sql = "
                        SELECT ID, post_title,
                            (CASE 
                                WHEN LOWER(post_title) = LOWER(%s) THEN 100                    -- Exact match
                                WHEN LOWER(post_title) LIKE LOWER(%s) THEN 90                  -- Starts with exact phrase
                                WHEN LOWER(post_title) LIKE LOWER(%s) THEN 80                  -- Contains exact phrase
                                WHEN (" . implode(' AND ', $search_conditions) . ") THEN 70    -- Contains all words
                                ELSE 50                                                         -- Partial match
                            END) as relevance_score
                        FROM {$wpdb->posts} 
                        WHERE post_type = 'table-layout-manager' 
                        AND post_status = 'publish'
                        AND (
                            LOWER(post_title) = LOWER(%s) OR
                            LOWER(post_title) LIKE LOWER(%s) OR
                            LOWER(post_title) LIKE LOWER(%s) OR
                            (" . implode(' AND ', $search_conditions) . ")
                        )
                        ORDER BY 
                            relevance_score DESC,
                            CHAR_LENGTH(post_title) ASC,  -- Prefer shorter titles for same relevance
                            post_title ASC
                        LIMIT 50
                    ";
                    
                    // Prepare parameters in correct order
                    $all_params = [
                        $clean_search,           // Exact match
                        $starts_with_term,       // Starts with
                        $full_search_term,       // Contains phrase
                        ...$bind_params,         // All words conditions
                        $clean_search,           // WHERE exact match
                        $starts_with_term,       // WHERE starts with
                        $full_search_term,       // WHERE contains phrase
                        ...$bind_params          // WHERE all words conditions
                    ];
                    
                    $results = $wpdb->get_results($wpdb->prepare($sql, ...$all_params));
                    $title_matches = array_column($results, 'ID');
                    
                    // Cache results for 5 minutes
                    wp_cache_set($cache_key, $title_matches, 'table_builder_search', 300);
                } else {
                    $title_matches = [];
                }
            }
            
            if (!empty($title_matches)) {
                // Use our optimized search results
                $args['post__in'] = $title_matches;
                $args['orderby'] = 'post__in'; // Maintain our relevance order
                
                // Remove conflicting parameters
                unset($args['s'], $args['search']);
            } else {
                // No matches found - return empty results efficiently
                $args['post__in'] = [0];
                unset($args['s'], $args['search']);
            }
            
            // Optimize query performance
            $args['no_found_rows'] = true; // Skip counting for faster queries
            $args['suppress_filters'] = false;
        }

        // Handle specific template ID
        if (!empty($template_id)) {
            $args['p'] = $template_id;
            $args['posts_per_page'] = 1;
        }

        // Handle category filter - BUT NOT when we have a search term (search takes priority)
        if (!empty($category) && $category !== 'all' && empty($search)) {
            $args['tax_query'][] = [
                'taxonomy' => 'table_pattern_categories',
                'field' => 'slug',
                'terms' => $category,
            ];
        }

        // Handle type filter (free/pro) - BUT NOT when we have a search term (search takes priority)
        if (!empty($type) && $type !== 'all' && empty($search)) {
            if ($type === 'free') {
                $args['meta_query'][] = [
                    'relation' => 'OR',
                    [
                        'key' => '_package_type',
                        'value' => 'free',
                        'compare' => '=',
                    ],
                    [
                        'key' => '_package_type',
                        'compare' => 'NOT EXISTS',
                    ]
                ];
            } else {
                $args['meta_query'][] = [
                    'key' => '_package_type',
                    'value' => $type,
                    'compare' => '=',
                ];
            }
        }

        // Disable WordPress default search completely when we have a custom search
        if (!empty($search)) {
            add_filter('posts_search', '__return_empty_string', 999);
            add_filter('posts_search_orderby', '__return_empty_string', 999);
        }
        
        $query = new WP_Query($args);
        
        // Remove filters after query
        if (!empty($search)) {
            remove_filter('posts_search', '__return_empty_string', 999);
            remove_filter('posts_search_orderby', '__return_empty_string', 999);
        }
        
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
                $thumbnail = get_the_post_thumbnail_url($post_id, 'medium') ?: '';
                $required_plugins = get_post_meta($post_id, '_required_plugins', true) ?: [];
                $download_count = get_post_meta($post_id, '_download_count', true) ?: 0;

                // Get pattern categories
                $categories = wp_get_post_terms($post_id, 'table_pattern_categories');

                // Format categories
                $formatted_categories = [];
                foreach ($categories as $category) {
                    $formatted_categories[] = [
                        'id' => (int) $category->term_id,
                        'name' => sanitize_text_field($category->name),
                        'slug' => sanitize_title($category->slug),
                    ];
                }

                $is_pro = ($package_type === 'pro');

                $posts[] = $this->sanitize_output([
                    'id' => (int) $post_id,
                    'title' => sanitize_text_field(get_the_title()),
                    'content' => wp_kses_post(get_the_content()),
                    'excerpt' => sanitize_text_field(get_the_excerpt() ?: 'A professional ' . strtolower(str_replace([' Table', ' #'], ['', ''], get_the_title())) . ' designed for modern websites.'),
                    'slug' => sanitize_title(get_post_field('post_name', $post_id)),
                    'date' => sanitize_text_field(get_the_date('Y-m-d H:i:s')),
                    'modified' => sanitize_text_field(get_the_modified_date('Y-m-d H:i:s')),
                    'author' => [
                        'id' => (int) $author_id,
                        'name' => sanitize_text_field($author_name),
                    ],
                    'thumbnail' => esc_url_raw($thumbnail),
                    'featured_image' => esc_url_raw($thumbnail), // Alias for compatibility
                    'is_pro' => (bool) $is_pro,
                    'pro' => (bool) $is_pro, // Alias for compatibility
                    'type' => sanitize_text_field($package_type),
                    'package' => sanitize_text_field($package_type),
                    'required_plugins' => array_map('sanitize_text_field', (array) $required_plugins),
                    'categories' => $formatted_categories,
                    'download_count' => (int) $download_count,
                    'meta' => [
                        'download_count' => [(string) $download_count],
                    ],
                ]);
            }
            wp_reset_postdata();
        }

        // Prepare response with better empty state handling
        $response_data = [
            'posts' => $posts,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
            'per_page' => $per_page,
            'search_term' => $search,
            'has_results' => !empty($posts),
        ];
        
        // Add search context for debugging
        if (!empty($search) && empty($posts)) {
            $response_data['message'] = 'No patterns found matching your search criteria.';
            $response_data['suggestions'] = [
                'Try different keywords',
                'Check spelling',
                'Use fewer or more general terms',
                'Browse categories instead'
            ];
        }
        
        return new WP_REST_Response($response_data, 200);
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
            'taxonomy' => 'table_pattern_categories',
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms)) {
            return new WP_REST_Response([], 200);
        }

        $categories = [];
        foreach ($terms as $term) {
            // Get accurate count of published posts for this category
            $post_count = new WP_Query([
                'post_type' => 'table-layout-manager',
                'post_status' => 'publish',
                'tax_query' => [
                    [
                        'taxonomy' => 'table_pattern_categories',
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                    ],
                ],
                'fields' => 'ids',
                'nopaging' => true,
            ]);
            
            $categories[] = [
                'id' => $term->term_id,
                'title' => $term->name,
                'name' => $term->name,
                'slug' => $term->slug,
                'description' => $term->description,
                'count' => $post_count->found_posts,
            ];
        }

        return new WP_REST_Response($categories, 200);
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
        $thumbnail = get_the_post_thumbnail_url($id, 'large') ?: '';
        $required_plugins = get_post_meta($id, '_required_plugins', true) ?: [];
        $download_count = get_post_meta($id, '_download_count', true) ?: 0;

        // Get pattern categories
        $categories = wp_get_post_terms($id, 'table_pattern_categories');

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
            'taxonomy' => 'table_pattern_categories',
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
            ],
            'endpoints' => [
                'patterns' => '/wp-json/table-builder/v1/layout-manager-api/patterns',
                'categories' => '/wp-json/table-builder/v1/layout-manager-api/patterns/categories',
                'single_template' => '/wp-json/table-builder/v1/layout-manager-api/template/{id}',
                'download_count' => '/wp-json/table-builder/v1/layout-manager-api/download-count/{id}',
                'status' => '/wp-json/table-builder/v1/layout-manager-api/status'
            ]
        ], 200);
    }


}

// Initialize the REST API
new Table_Builder_Essential_REST_API();
