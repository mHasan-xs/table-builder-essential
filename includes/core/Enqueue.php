<?php

namespace TableBuilderEssential\core;

defined('ABSPATH') || exit;

class Enqueue
{
    use \TableBuilderEssential\traits\Singleton;

    public function __construct()
    {
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_editor_assets'), 10);
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
    }

    public function enqueue_editor_assets()
    {
        if (!$this->is_table_builder_active()) {
            return;
        }

        $this->enqueue_template_library();
    }

    private function enqueue_template_library()
    {
        $asset_file = plugin_dir_path(__FILE__) . '../../build/template-library/template-library.asset.php';

        if (file_exists($asset_file)) {
            $assets = include $asset_file;

            if (isset($assets['dependencies']) && isset($assets['version'])) {
                wp_enqueue_script(
                    'table-builder-essential-template-library',
                    plugin_dir_url(__FILE__) . '../../build/template-library/template-library.js',
                    $assets['dependencies'],
                    $assets['version'],
                    true
                );

                wp_enqueue_style(
                    'table-builder-essential-template-library',
                    plugin_dir_url(__FILE__) . '../../build/template-library/template-library.css',
                    array(),
                    $assets['version']
                );

                wp_localize_script(
                    'table-builder-essential-template-library',
                    'tableBuilderEssential',
                    array(
                        'plugin_url' => plugin_dir_url(__FILE__) . '../../',
                        'api_url' => rest_url('table-builder-essential/v1/'),
                        'nonce' => wp_create_nonce('wp_rest'),
                        'version' => TABLE_BUILDER_ESSENTIAL_VERSION,
                        'template_endpoint' => rest_url('wp/v2/template'),
                    )
                );
            }
        }
    }

    public function admin_scripts($hook)
    {
        if (!$this->is_block_editor_page()) {
            return;
        }

        wp_add_inline_script(
            'wp-block-editor',
            '
            window.tableBuilderEssential = window.tableBuilderEssential || {};
            window.tableBuilderEssential.isActive = true;
            window.tableBuilderEssential.pluginUrl = "' . plugin_dir_url(__FILE__) . '../../";
            ',
            'before'
        );
    }

    private function is_table_builder_active()
    {
        return function_exists('table_builder_block_init') || class_exists('TableBuilder\Core\Enqueue');
    }

    private function is_block_editor_page()
    {
        global $pagenow;

        if (in_array($pagenow, ['post.php', 'post-new.php', 'site-editor.php'])) {
            return true;
        }

        if (isset($_GET['post_type']) && $_GET['post_type'] === 'template') {
            return true;
        }

        return false;
    }
}
