<?php

namespace TableBuilderEssential;

defined('ABSPATH') || exit;

class Autoloader
{
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    public static function autoload($class)
    {
        if (strpos($class, 'TableBuilderEssential\\') !== 0) {
            return;
        }

        $class = str_replace('TableBuilderEssential\\', '', $class);
        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $file = plugin_dir_path(__FILE__) . $class_path . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}
