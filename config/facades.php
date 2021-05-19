<?php

/**
 * 静态代理
 */
return [
    'app'       =>  \Chazz\Lib\App::class,
    'router'    =>  \Chazz\Lib\Router::class,
    'redis'     =>  \Chazz\Cache\Redis::class,
    'db'        =>  \Chazz\Database\Model::class,
    'log'       =>  \Chazz\Log::class,
    'event'     =>  \Chazz\Lib\Event::class,
];
