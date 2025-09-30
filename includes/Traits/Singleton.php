<?php

namespace TableBuilderEssential\Traits;

defined('ABSPATH') || exit;

/**
 * Singleton trait for creating singleton instances
 */
trait Singleton
{

    /**
     * Store the singleton instance
     */
    private static $instance = null;

    /**
     * Get the singleton instance
     * 
     * @return static
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
