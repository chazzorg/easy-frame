<?php

/**
 * 应用配置
 */
return [
    'name'      => env('APP_NAME', 'chazz-frame'), //项目名称
    'type'      => env('SWOOLE_TYPE', 'http'),     //服务类型
    'namespace' => "\\App\\Controllers\\",        //应用命名空间
    'view'      => APP_PATH . 'View/',              //模板目录
    'task'      => APP_PATH . 'Task/',              //模板目录
    'event'     => APP_PATH . 'Event/',            //模板目录

    'http'       => [
        'ip'        => env('HTTP_IP', '0.0.0.0'),      //监听IP
        'port'      => env('HTTP_PORT', 9501),         //监听端口
        'server'    => \Chazz\Server\HttpServer::class, //绑定服务
        'daemonize' => env('SWOOLE_DAEMONIZE', 1), //是否作为守护进程
        'worker_num'      => env('SWOOLE_WORKER', 4), //进程数，设置为CPU核数的1-4倍
        'task_worker_num' => env('SWOOLE_TASK_WORK', 2), //使用数量累计 worker_num
        'document_root'         => PUBLIC_PATH, // v4.4.0以下版本, 此处必须为绝对路径
        'enable_static_handler' => true,  //开启静态文件请求处理功能, 需配合document_root使用
        'http_compression'      => true,  //开启压缩
        'task_enable_coroutine' => true,  //Task工作进程支持协程
    ],

    'ws'       => [
        'ip'        => env('WS_IP', '0.0.0.0'),      //监听IP
        'port'      => env('WS_PORT', 9502),         //监听端口
        'server'    => \Chazz\Server\WsServer::class, //绑定服务
        'daemonize' => env('SWOOLE_DAEMONIZE', 1), //是否作为守护进程
        'max_coroutine'   => 1000, //Worker进程最多同时处理的协程数目
        'worker_num'      => env('SWOOLE_WORKER', 4), //进程数，设置为CPU核数的1-4倍
        'task_worker_num' => env('SWOOLE_TASK_WORK', 2), //使用数量累计 worker_num
        'enable_static_handler' => true,  //开启静态文件请求处理功能, 需配合document_root使用
        'http_compression'      => true,  //开启压缩
        'heartbeat_idle_time'      => 10, //最大断开连接时间单位（秒）
        'heartbeat_check_interval' => 5,  //循环检测连接间隔单位（秒）
        'task_enable_coroutine'    => true,  //Task工作进程支持协程
    ],

    'tcp'       => [
        'ip'            => env('TCP_IP', '0.0.0.0'),      //监听IP
        'port'          => env('TCP_PORT', 9503),         //监听端口
        'server'        => \Chazz\Server\TcpServer::class, //绑定服务
        'daemonize'     => env('SWOOLE_DAEMONIZE', 1), //是否作为守护进程
        'max_coroutine' => 1000, //Worker进程最多同时处理的协程数目
        'worker_num'               => env('SWOOLE_WORKER', 4), //进程数，设置为CPU核数的1-4倍
        'task_worker_num'          => env('SWOOLE_TASK_WORK', 2), //使用数量累计 worker_num
        'heartbeat_idle_time'      => 10, //最大断开连接时间单位（秒）
        'heartbeat_check_interval' => 5, //循环检测连接间隔单位（秒）
        'task_enable_coroutine'    => true,  //Task工作进程支持协程
    ],

    'rpc'       => [
        'ip'        => env('HTTP_IP', '0.0.0.0'),      //监听IP
        'port'      => env('HTTP_PORT', 9504),         //监听端口
        'server'    => \Chazz\Server\RpcServer::class, //绑定服务
        'daemonize' => env('SWOOLE_DAEMONIZE', 1), //是否作为守护进程
        'worker_num'            => env('SWOOLE_WORKER', 4), //进程数，设置为CPU核数的1-4倍
        'task_worker_num'       => env('SWOOLE_TASK_WORK', 2), //使用数量累计 worker_num
        'task_enable_coroutine' => true,  //Task工作进程支持协程
    ],

    //日志
    'log' => [
        //输出到屏幕，当 daemonize = false 时，该配置生效，
        'echo'  => 0,
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别，共8个级别
        'level' => ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG', 'SQL'],
    ],

    //路由配置
    'router' => [
        'm'             => 'home',     //默认模块
        'c'             => 'index',     //默认控制器
        'a'             => 'index',     //默认操作
        'ext'           => '.html',     //url后缀    例如 .html
    ],

];
