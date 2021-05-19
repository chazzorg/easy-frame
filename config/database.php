<?php

/**
 * 数据库配置
 */
return [
    'default' => [
        'hostname' => env('DB_HOST', '127.0.0.1'),          // 服务器地址
        'port'     => env('DB_PORT', 3306),                 // 数据库连接端口
        'database' => env('DB_DATABASE', 'test'),           // 数据库名
        'username' => env('DB_USERNAME', 'root'),           // 数据库用户名
        'password' => env('DB_PASSWORD', 'root'),           // 数据库密码
        'charset'  => 'utf8',                               // 数据库编码默认采用utf8
        'prefix'   => '',                                   // 数据表前缀
        'debug'    => false,                                // 调试模式
        'pconnect' => true,                                 // 长连接连接方式
    ],
];
