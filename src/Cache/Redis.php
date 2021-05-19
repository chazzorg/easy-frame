<?php
namespace Chazz\Cache;

use Chazz\Facades\Log;

class Redis
{
    private static $instance;
    private function __construct ()
    {
        try {
            self::$instance = new \Redis();
            if( config('redis.pconnect') ){
                self::$instance->pconnect(config('redis.host'),config('redis.port'));
            }else{
                self::$instance->connect(config('redis.host'),config('redis.port'));
            }
            if( config ('redis.passwd','') != '' ){
                self::$instance->auth(config ('redis.passwd'));
            }
            self::$instance->select(config ('redis.db'));
        } catch (\Throwable $th) {
            self::$instance = null ;
        }
    }

    public static function getInstance()
    {
        if(is_null (self::$instance)){
            new self();
        }
        return self::$instance;
    }

    // public function __call($method ,$args=NULL){
    //     $this->handle->$method(...$args);
    // }
}   
