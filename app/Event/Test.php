<?php
namespace App\Event;

use Chazz\Facades\Redis;

class Test
{
    private static $instance;
    private function __construct () {}

    public static function getInstance()
    {
        if(is_null (self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function start($name)
    {
        Redis::set($name,"Test");
    }
}