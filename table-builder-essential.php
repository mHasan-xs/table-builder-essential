<?php

/**
 * Plugin Name:       Table Builder Essential
 * Description:       A helper plugin for Table Builder
 * Version:          1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Author:            Wpmet
 * Author URI: https://wpmet.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       table-builder-essential
 *
 * @package           create-block
 */

if (!defined('ABSPATH')) exit;

define('TABLE_BUILDER_ESSENTIAL_VERSION', '1.0.0');
define('TABLE_BUILDER_ESSENTIAL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TABLE_BUILDER_ESSENTIAL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TABLE_BUILDER_ESSENTIAL_PLUGIN_FILE', __FILE__);

require_once plugin_dir_path(__FILE__) . 'includes/autoloaderx.php';
TableBuilderEssential\Autoloader::register();

add_action('init', 'table_builder_essential_security_init');
function table_builder_essential_security_init()
{
	if (!is_admin()) {
		add_action('wp_head', 'table_builder_essential_security_headers', 1);
	}
	remove_action('wp_head', 'wp_generator');
	add_filter('xmlrpc_enabled', '__return_false');
}

function table_builder_essential_security_headers()
{
	header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self' https:;");
	header('X-Frame-Options: SAMEORIGIN');
	header('X-Content-Type-Options: nosniff');
	header('X-XSS-Protection: 1; mode=block');
	header('Referrer-Policy: strict-origin-when-cross-origin');
}

function table_builder_essential_init()
{
	TableBuilderEssential\core\Enqueue::instance();
	if (file_exists(__DIR__ . '/build/template-library/block.json')) {
		register_block_type(__DIR__ . '/build/template-library');
	}
}

add_action('plugins_loaded', 'table_builder_essential_init');

function create_block_table_builder_essential_init()
{
	// Backward compatibility
}

add_action('init', 'create_block_table_builder_essential_init');
require_once plugin_dir_path(__FILE__) . 'includes/cpt.php';
require_once plugin_dir_path(__FILE__) . 'includes/meta-boxes.php';
require_once plugin_dir_path(__FILE__) . 'includes/rest-api.php';

/**
 * Adds 'template' post type to the allowed post types for the popup builder.
 *
 * This function hooks into the 'popup-builder-block/allow_post_type' (popup-builder-block plugin) filter and appends
 * the 'template' post type to the array of allowed post types for the popup builder.
 *
 * @param array $post_types An array of post types that are allowed for the popup builder.
 * @return array The modified array of post types with 'template' added.
 */
function popup_post_type($post_types)
{
	$post_types[] = 'template';
	// $post_types[] = 'page';
	return $post_types;
}

add_filter('popup_builder_block/allow_post_type', 'popup_post_type');

// Filter to modify the custom styles for the popup builder block
add_filter('popup-builder-block/custom_styles', function ($default_style) {
	global $post;
	if (isset($post) && $post->post_type === 'template') {
		$custom_style = '
			body { background: transparent !important; }
			.popup-builder-modal .popup-builder-content-credit { display: none; }
		';

		return $custom_style;
	}
	return $default_style;
});

// Function to add 'template_content' to the REST API response before it is fetched
function add_template_content_to_api_response($data, $post, $request)
{
	if ('template' === $post->post_type) {
		if (!empty($post)) {
			$data->data['post_content'] = $post->post_content;
		} else {
			$data->data['post_content'] = '';
		}
	}
	return $data;
}

// Hook into the REST API response to modify it
add_filter('rest_prepare_template', 'add_template_content_to_api_response', 10, 3);

define('ROXNOR_ESSENTIAL_MAINTENANCE', false);

function table_builder_essential_template_redirect($template)
{
	if (isset($_REQUEST['previewer'])) {
		setcookie('previewer', $_REQUEST['previewer'], time() + (30 * 24 * 60 * 60), COOKIEPATH, COOKIE_DOMAIN);
	}
	if (
		ROXNOR_ESSENTIAL_MAINTENANCE &&
		!is_user_logged_in() &&
		!isset($_COOKIE['previewer']) &&
		!isset($_REQUEST['previewer']) &&
		!isset($_REQUEST['iframe']) &&
		!is_singular('template')
	) {
		// is not url home url
		if (!is_page(3732)) {
			wp_redirect(home_url('/coming-soon'));
			exit();
		}
	}
}

add_action('template_redirect', 'table_builder_essential_template_redirect');


function table_builder_essential_enqueue_scripts()
{
	wp_enqueue_script(
		'table-builder-essential',
		plugins_url('assets/js/table-builder-essential.js', __FILE__),
		array(),
		filemtime(plugin_dir_path(__FILE__) . 'assets/js/table-builder-essential.js'),
		[
			'in_footer' => true,
			'defer'     => true,
		]
	);

	wp_enqueue_style(
		'table-builder-essential',
		plugins_url('assets/css/table-builder-essential.css', __FILE__),
		array(),
		filemtime(plugin_dir_path(__FILE__) . 'assets/css/table-builder-essential.css'),
	);
}

add_action('wp_enqueue_scripts', 'table_builder_essential_enqueue_scripts');

add_action('admin_menu', function () {
	add_submenu_page(
		'themes.php',
		'Reusable Blocks',
		'Reusable Blocks',
		'edit_posts',
		'edit.php?post_type=wp_block'
	);
});

/**
 * Filters the Rank Math JSON-LD schema data to modify the 'author' information for 'Article' schema types.
 *
 * This filter iterates through each schema in the JSON-LD data and, if the schema type is 'Article' and contains an 'author' array,
 * it sets the author's name to 'Wpmet' and the '@id' to the site's home URL.
 *
 * @param array $data   The array of JSON-LD schema data provided by Rank Math.
 * @param array $jsonld The original JSON-LD data.
 * @return array        The modified JSON-LD schema data with updated author information for articles.
 */
add_filter('rank_math/json_ld', function ($data, $jsonld) {
	foreach ($data as $key => $schema) {
		if (isset($schema['@type']) && $schema['@type'] === 'Article' && isset($schema['author'])) {
			if (is_array($schema['author'])) {
				$schema['author']['name'] = 'Wpmet';
				$schema['author']['@id']  = home_url('/');
			}
			$data[$key] = $schema;
		}
	}

	return $data;
}, 20, 2);

/**
 * Filters the displayed author name.
 *
 * This filter overrides the author name for posts that are not of the default 'post' post type,
 * returning 'Wpmet' as the author name. For 'post' post type or if $post is not an object,
 * it returns the original display name.
 *
 * @param string $display_name The original display name of the author.
 * @return string Modified author name based on post type.
 */
add_filter('the_author', function ($display_name) {
	global $post;

	if (! is_object($post)) {
		return $display_name;
	}

	// Only override if not the default 'post' post type
	if (isset($post->post_type) && 'post' !== $post->post_type) {
		return 'Wpmet';
	}

	return $display_name;
});

function table_builder_essential_activate()
{
	if (function_exists('table_builder_essential_register_template_post_type')) {
		table_builder_essential_register_template_post_type();
	}
	if (function_exists('table_builder_essential_register_layout_manager_post_type')) {
		table_builder_essential_register_layout_manager_post_type();
	}
	if (function_exists('table_builder_essential_register_layout_manager_taxonomies')) {
		table_builder_essential_register_layout_manager_taxonomies();
	}
	flush_rewrite_rules();
}

register_activation_hook(__FILE__, 'table_builder_essential_activate');

function table_builder_essential_deactivate()
{
	flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'table_builder_essential_deactivate');
