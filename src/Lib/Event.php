<?php
namespace Chazz\Lib;

class Event
{
    private static $instance;
    private static $config;

    private function __construct () {}

    public static function getInstance()
    {
        if(is_null (self::$instance)){
            self::$instance = new self();
            self::$config = config("event");
        }
        return self::$instance;
    }

    public function listen($event, ...$args)
    {
        $event = isset(self::$config[$event]) ? self::$config[$event] : [];
        while($event){
            list($class,$func) = array_shift($event);
            $class::getInstance()->$func(...$args);
        }
    }
}