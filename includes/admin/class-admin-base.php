<?php

/**
 * Base class for Table Builder Essential admin components.
 *
 * @since 1.0.0
 * @package table-builder-essential
 */

if (!defined('ABSPATH')) exit;

abstract class Table_Builder_Essential_Admin_Base
{
    protected $config;

    public function __construct()
    {
        $this->load_config();
        $this->init_hooks();
    }

    protected function load_config()
    {
        require_once __DIR__ . '/class-admin-config.php';
        
        $this->config = [
            'plugin_choices' => Table_Builder_Essential_Admin_Config::get_plugin_choices(),
            'package_types' => Table_Builder_Essential_Admin_Config::get_package_types(),
        ];
    }

    abstract protected function init_hooks();

    protected function render_plugin_checkboxes($selected = [], $inline = false)
    {
        $plugin_choices = $this->config['plugin_choices'];
        
        foreach ($plugin_choices as $value => $label) {
            $checked = in_array($value, $selected) ? 'checked' : '';
            printf(
                '<label style="%s"><input type="checkbox" name="required_plugins[]" value="%s" %s> %s</label>%s',
                $inline ? 'display: inline-block; margin-right: 15px;' : 'display: block; margin-bottom: 5px;',
                esc_attr($value),
                $checked,
                esc_html($label),
                $inline ? '' : ''
            );
        }
    }

    protected function get_package_badge($package = 'free')
    {
        $package_types = $this->config['package_types'];
        $label = $package_types[$package] ?? $package_types['free'];
        $badge_class = $package === 'pro' ? 'pro-badge' : 'free-badge';
        
        return sprintf(
            '<span class="package-badge %s">%s</span>',
            esc_attr($badge_class),
            esc_html($label)
        );
    }

    protected function sanitize_package_type($package)
    {
        $valid_types = array_keys($this->config['package_types']);
        return in_array($package, $valid_types) ? $package : 'free';
    }

    protected function get_post_type_setting($key = null)
    {
        $settings = $this->config['post_type_settings'];
        return $key ? ($settings[$key] ?? null) : $settings;
    }

    protected function get_taxonomy_setting($key = null)
    {
        $settings = $this->config['taxonomy_settings'];
        return $key ? ($settings[$key] ?? null) : $settings;
    }
}