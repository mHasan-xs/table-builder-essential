<?php
/**
 * REST API Controller for Table Layout Manager
 *
 * Handles REST API endpoints for table-layout-manager post type
 * and its taxonomies, providing data for the template library.
 *
 * @since 1.0.0
 * @package TableBuilderEssential
 */

defined('ABSPATH') || exit;

class Table_Builder_Essential_REST_API {
    
    private const NAMESPACE = 'table-builder/v1';
    private const POST_TYPE = 'table-layout-manager';
    private const TAXONOMY = 'table_pattern_categories';
    private const CACHE_GROUP = 'table_builder_search';
    private const CACHE_DURATION = 300;
    
    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Templates endpoint
        register_rest_route(self::NAMESPACE, '/layout-manager-api/patterns', [
            'methods' => 'GET',
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
        register_rest_route(self::NAMESPACE, '/layout-manager-api/patterns/categories', [
            'methods' => 'GET',
            'callback' => [$this, 'get_categories'],
            'permission_callback' => '__return_true',
        ]);
        
        // Download count endpoint
        register_rest_route(self::NAMESPACE, '/layout-manager-api/download-count/(?P<id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'update_download_count'],
            'permission_callback' => '__return_true',
            'args' => [
                'id' => [
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ],
            ],
        ]);
    }
    
    /**
     * Validate search parameter
     */
    public function validate_search_param($param, $request, $key) {
        if (empty($param) || $param === '') {
            return true;
        }
        
        if (strlen($param) > 100) {
            return false;
        }
        
        if (!preg_match('/^[\p{L}\p{N}\s\-_\.\#\@\+\(\)\,\'\"\!\?\&\%\$\*\=\[\]\{\}\|\;\:]+$/u', $param)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate category parameter
     */
    public function validate_category_param($param, $request, $key) {
        if (empty($param) || $param === '' || $param === 'all') {
            return true;
        }
        
        if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $param)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate type parameter
     */
    public function validate_type_param($param, $request, $key) {
        if (empty($param) || $param === '') {
            return true;
        }
        
        $allowed_types = ['all', 'free', 'pro'];
        return in_array($param, $allowed_types, true);
    }
    
    /**
     * Validate sort parameter
     */
    public function validate_sort_param($param, $request, $key) {
        if (empty($param) || $param === '') {
            return true;
        }
        
        $allowed_sorts = ['recent', 'popular'];
        return in_array($param, $allowed_sorts, true);
    }
    
    /**
     * Validate ID parameter
     */
    public function validate_id_param($param, $request, $key) {
        return is_numeric($param) && $param >= 0;
    }
    
    /**
     * Sanitize output data
     */
    private function sanitize_output($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize_output'], $data);
        }
        
        if (is_string($data)) {
            return wp_kses_post($data);
        }
        
        return $data;
    }
    
    /**
     * Get templates/patterns
     */
    public function get_templates($request) {
        $page = max(1, absint($request->get_param('page') ?: 1));
        $per_page = min(100, max(1, absint($request->get_param('per_page') ?: 16)));
        $search = sanitize_text_field($request->get_param('search') ?: '');
        $category = sanitize_text_field($request->get_param('cat') ?: '');
        $type = sanitize_text_field($request->get_param('type') ?: '');
        $template_id = absint($request->get_param('id') ?: 0);
        $sort = sanitize_text_field($request->get_param('sort') ?: 'recent');
        
        if (strlen($search) > 100) {
            return new WP_Error('invalid_search', 'Search query too long.', ['status' => 400]);
        }
        
        $args = [
            'post_type' => self::POST_TYPE,
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
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
        }
        
        // Handle search
        if (!empty($search) && strlen(trim($search)) > 0) {
            global $wpdb;
            $clean_search = trim($search);
            
            $search_cache_key = 'tb_search_ids_' . md5($clean_search);
            $cached_search_ids = wp_cache_get($search_cache_key, self::CACHE_GROUP);
            
            if ($cached_search_ids !== false) {
                $title_matches = $cached_search_ids;
            } else {
                $search_words = explode(' ', $clean_search);
                $search_conditions = [];
                $bind_params = [];
                
                foreach ($search_words as $word) {
                    if (strlen(trim($word)) > 0) {
                        $word = trim($word);
                        $word_param = '%' . $wpdb->esc_like($word) . '%';
                        $search_conditions[] = "LOWER(post_title) LIKE LOWER(%s)";
                        $bind_params[] = $word_param;
                    }
                }
                
                if (!empty($search_conditions)) {
                    $full_search_term = '%' . $wpdb->esc_like($clean_search) . '%';
                    $starts_with_term = $wpdb->esc_like($clean_search) . '%';
                    
                    $sql = "
                        SELECT ID, post_title,
                            (CASE 
                                WHEN LOWER(post_title) = LOWER(%s) THEN 100
                                WHEN LOWER(post_title) LIKE LOWER(%s) THEN 90
                                WHEN LOWER(post_title) LIKE LOWER(%s) THEN 80
                                WHEN (" . implode(' AND ', $search_conditions) . ") THEN 70
                                ELSE 50
                            END) as relevance_score
                        FROM {$wpdb->posts} 
                        WHERE post_type = %s
                        AND post_status = 'publish'
                        AND (
                            LOWER(post_title) = LOWER(%s) OR
                            LOWER(post_title) LIKE LOWER(%s) OR
                            LOWER(post_title) LIKE LOWER(%s) OR
                            (" . implode(' AND ', $search_conditions) . ")
                        )
                        ORDER BY 
                            relevance_score DESC,
                            CHAR_LENGTH(post_title) ASC,
                            post_title ASC
                        LIMIT 50
                    ";
                    
                    $all_params = [
                        $clean_search,
                        $starts_with_term,
                        $full_search_term,
                        ...$bind_params,
                        self::POST_TYPE,
                        $clean_search,
                        $starts_with_term,
                        $full_search_term,
                        ...$bind_params
                    ];
                    
                    $results = $wpdb->get_results($wpdb->prepare($sql, ...$all_params));
                    $title_matches = array_column($results, 'ID');
                    
                    wp_cache_set($search_cache_key, $title_matches, self::CACHE_GROUP, self::CACHE_DURATION);
                } else {
                    $title_matches = [];
                }
            }
            
            if (!empty($title_matches)) {
                $args['post__in'] = $title_matches;
                
                if ($sort === 'popular') {
                    $args['meta_key'] = '_download_count';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                } elseif ($sort === 'recent') {
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                } else {
                    $args['orderby'] = 'post__in';
                }
                
                unset($args['s'], $args['search']);
            } else {
                $args['post__in'] = [0];
                unset($args['s'], $args['search']);
            }
            
            $args['no_found_rows'] = true;
            $args['suppress_filters'] = false;
        }
        
        // Handle specific template ID
        if (!empty($template_id)) {
            $args['p'] = $template_id;
            $args['posts_per_page'] = 1;
        }
        
        // Handle category filter
        if (!empty($category) && $category !== 'all' && empty($search)) {
            $args['tax_query'][] = [
                'taxonomy' => self::TAXONOMY,
                'field' => 'slug',
                'terms' => $category,
            ];
        }
        
        // Handle type filter
        if (!empty($type) && $type !== 'all') {
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
        
        // Disable WordPress default search
        if (!empty($search)) {
            add_filter('posts_search', '__return_empty_string', 999);
            add_filter('posts_search_orderby', '__return_empty_string', 999);
        }
        
        $query = new WP_Query($args);
        
        // Remove filters
        if (!empty($search)) {
            remove_filter('posts_search', '__return_empty_string', 999);
            remove_filter('posts_search_orderby', '__return_empty_string', 999);
        }
        
        $posts = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                $author_id = get_post_field('post_author', $post_id);
                $author_name = get_the_author_meta('display_name', $author_id);
                
                $package_type = get_post_meta($post_id, '_package_type', true) ?: 'free';
                $thumbnail = get_the_post_thumbnail_url($post_id, 'medium') ?: '';
                $required_plugins = get_post_meta($post_id, '_required_plugins', true) ?: [];
                $live_preview_url = get_post_meta($post_id, '_live_preview_url', true) ?: '';
                $download_count = get_post_meta($post_id, '_download_count', true) ?: 0;
                
                $categories = wp_get_post_terms($post_id, self::TAXONOMY);
                
                $formatted_categories = [];
                foreach ($categories as $cat) {
                    $formatted_categories[] = [
                        'id' => (int) $cat->term_id,
                        'name' => sanitize_text_field($cat->name),
                        'slug' => sanitize_title($cat->slug),
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
                    'featured_image' => esc_url_raw($thumbnail),
                    'is_pro' => (bool) $is_pro,
                    'pro' => (bool) $is_pro,
                    'type' => sanitize_text_field($package_type),
                    'package' => sanitize_text_field($package_type),
                    'required_plugins' => array_map('sanitize_text_field', (array) $required_plugins),
                    'live_preview_url' => esc_url_raw($live_preview_url),
                    'categories' => $formatted_categories,
                    'download_count' => (int) $download_count,
                    'meta' => [
                        'download_count' => [(string) $download_count],
                    ],
                ]);
            }
            wp_reset_postdata();
        }
        
        $response_data = [
            'posts' => $posts,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page,
            'per_page' => $per_page,
            'search_term' => $search,
            'has_results' => !empty($posts),
        ];
        
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
     * Get categories
     */
    public function get_categories($request) {
        $terms = get_terms([
            'taxonomy' => self::TAXONOMY,
            'hide_empty' => false,
        ]);
        
        if (is_wp_error($terms)) {
            return new WP_REST_Response([], 200);
        }
        
        $categories = [];
        foreach ($terms as $term) {
            $post_count = new WP_Query([
                'post_type' => self::POST_TYPE,
                'post_status' => 'publish',
                'tax_query' => [
                    [
                        'taxonomy' => self::TAXONOMY,
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
     * Update download count
     */
    public function update_download_count($request) {
        $id = $request->get_param('id');
        
        $post = get_post($id);
        if (!$post || $post->post_type !== self::POST_TYPE) {
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
}

new Table_Builder_Essential_REST_API();