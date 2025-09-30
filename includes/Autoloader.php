<?php

namespace TableBuilderEssential;

defined('ABSPATH') || exit;

/**
 * Autoloader for Table Builder Essential
 */
class Autoloader
{

    /**
     * Register autoloader
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Autoload classes
     * 
     * @param string $class
     */
    public static function autoload($class)
    {
        // Check if class belongs to our namespace
        if (strpos($class, 'TableBuilderEssential\\') !== 0) {
            return;
        }

        // Remove namespace prefix
        $class = str_replace('TableBuilderEssential\\', '', $class);

        // Convert namespace to file path
        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

        // Build file path
        $file = plugin_dir_path(__FILE__) . $class_path . '.php';

        // Load file if exists
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
