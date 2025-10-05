<?php

/**
 * Table Builder Essential Admin Manager.
 *
 * This file initializes all admin-related functionality in a modular way.
 *
 * @since 1.0.0
 * @package table-builder-essential
 */

if (!defined('ABSPATH')) exit;

class Table_Builder_Essential_Admin_Manager
{
    private $components = [];

    public function __construct()
    {
        $this->load_dependencies();
        $this->init_components();
    }

    private function load_dependencies()
    {
        $admin_dir = __DIR__ . '/admin/';
        
        $required_files = [
            'class-admin-base.php',
            'class-admin-config.php',
            'class-post-meta-boxes.php',
            'class-admin-columns.php',
            'class-assets-manager.php',
        ];

        foreach ($required_files as $file) {
            $file_path = $admin_dir . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("Table Builder Essential: Missing admin file: {$file}");
                }
            }
        }
    }

    private function init_components()
    {
        $component_classes = [
            'post_meta_boxes' => 'Table_Builder_Essential_Post_Meta_Boxes',
            'admin_columns' => 'Table_Builder_Essential_Admin_Columns',
            'assets_manager' => 'Table_Builder_Essential_Assets_Manager',
        ];

        foreach ($component_classes as $component_key => $class_name) {
            try {
                if (class_exists($class_name)) {
                    $enabled = class_exists('Table_Builder_Essential_Admin_Config') 
                        ? Table_Builder_Essential_Admin_Config::is_feature_enabled($component_key)
                        : true;
                    
                    if ($enabled) {
                        $this->components[$component_key] = new $class_name();
                    }
                }
            } catch (Exception $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("Table Builder Essential: Error initializing {$class_name}: " . $e->getMessage());
                }
            }
        }
    }

    public function get_component($component_name)
    {
        return $this->components[$component_name] ?? null;
    }

    public function get_components()
    {
        return $this->components;
    }
}

if (is_admin()) {
    try {
        if (class_exists('Table_Builder_Essential_Admin_Manager')) {
            $admin_manager = new Table_Builder_Essential_Admin_Manager();
            
            $components = $admin_manager->get_components();
            if (empty($components)) {
                throw new Exception('No admin components loaded');
            }
        } else {
            throw new Exception('Admin Manager class not found');
        }
    } catch (Exception $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Table Builder Essential: Using fallback admin system - ' . $e->getMessage());
        }
    }
}