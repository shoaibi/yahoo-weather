<?php
namespace App\Services;

class ServiceLocator
{
    static protected $container;

    public static function setContainer(\Pimple\Container $container)
    {
        static::$container = $container;
    }

    public static function get($id)
    {
        return static::$container[$id];
    }
}