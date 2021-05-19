<?php
namespace Chazz\Lib;

/**
 * A simple Facade Class, From Illuminate\Support\Facades\Facade
 */
abstract class Facade
{
    /**
     * The application instance being facaded config.
     *
     * @var array
     */
    protected static $app;

    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstance;

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \Exception
     */
    protected static function getFacadeAccessor()
    {
        throw new \Exception('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Set the application instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public static function setFacadeApplication($name)
    {
        static::$app = config('facades.'.$name);
        if (! static::$app) {
            throw new \Exception('No facade in facade config.');
        }
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @param  string|object  $name
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (!isset(static::$resolvedInstance[$name])) {
            static::setFacadeApplication($name);
            static::$resolvedInstance[$name] = static::$app::getInstance();
        }

        return static::$resolvedInstance[$name];
    }

    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array   $args
     * @return mixed
     *
     * @throws \Exception
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new \Exception('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}