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

require_once plugin_dir_path(__FILE__) . 'includes/autoloader.php';
TableBuilderEssential\Autoloader::register();


function table_builder_essential_init()
{
	TableBuilderEssential\core\Enqueue::instance();
	if (file_exists(__DIR__ . '/build/template-library/block.json')) {
		register_block_type(__DIR__ . '/build/template-library');
	}
}
add_action('plugins_loaded', 'table_builder_essential_init');

require_once plugin_dir_path(__FILE__) . 'includes/cpt.php';
require_once plugin_dir_path(__FILE__) . 'includes/meta-boxes.php';
require_once plugin_dir_path(__FILE__) . 'includes/rest-api.php';

// Hook into the REST API response to modify it
add_filter('rest_prepare_template', 'add_template_content_to_api_response', 10, 3);


function table_builder_essential_enqueue_scripts()
{
	wp_enqueue_style(
		'table-builder-essential',
		plugins_url('assets/css/table-builder-essential.css', __FILE__),
		array(),
		filemtime(plugin_dir_path(__FILE__) . 'assets/css/table-builder-essential.css'),
	);
}

add_action('wp_enqueue_scripts', 'table_builder_essential_enqueue_scripts');


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
