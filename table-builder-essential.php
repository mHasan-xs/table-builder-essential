<?php
/**
 * Plugin Name:       Table Builder Essential
 * Description:       A helper plugin for Table Builder
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      7.2
 * Author:            Wpmet
 * Author URI:        https://wpmet.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       table-builder-essential
 *
 * @package TableBuilderEssential
 */

defined('ABSPATH') || exit;

// Define plugin constants
define('TABLE_BUILDER_ESSENTIAL_VERSION', '1.0.0');
define('TABLE_BUILDER_ESSENTIAL_DIR', plugin_dir_path(__FILE__));
define('TABLE_BUILDER_ESSENTIAL_URL', plugin_dir_url(__FILE__));
define('TABLE_BUILDER_ESSENTIAL_FILE', __FILE__);

// Autoloader
require_once TABLE_BUILDER_ESSENTIAL_DIR . 'includes/autoloader.php';
TableBuilderEssential\Autoloader::register();

// Core initialization
add_action('plugins_loaded', function() {
    TableBuilderEssential\Core\Enqueue::instance();
    
    $block_json = TABLE_BUILDER_ESSENTIAL_DIR . 'build/template-library/block.json';
    if (file_exists($block_json)) {
        register_block_type(dirname($block_json));
    }
});

// Load required files
foreach (['cpt', 'meta-boxes', 'rest-api'] as $file) {
    require_once TABLE_BUILDER_ESSENTIAL_DIR . "includes/{$file}.php";
}

// Enqueue frontend assets
add_action('wp_enqueue_scripts', function() {
    $css_file = TABLE_BUILDER_ESSENTIAL_DIR . 'assets/css/table-builder-essential.css';
    
    wp_enqueue_style(
        'table-builder-essential',
        TABLE_BUILDER_ESSENTIAL_URL . 'assets/css/table-builder-essential.css',
        [],
        file_exists($css_file) ? filemtime($css_file) : TABLE_BUILDER_ESSENTIAL_VERSION
    );
});

// Activation hook
register_activation_hook(__FILE__, function() {
    $functions = [
        'table_builder_essential_register_template_post_type',
        'table_builder_essential_register_layout_manager_post_type',
        'table_builder_essential_register_layout_manager_taxonomies'
    ];
    
    foreach ($functions as $func) {
        if (function_exists($func)) {
            $func();
        }
    }
    
    flush_rewrite_rules();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    flush_rewrite_rules();
});