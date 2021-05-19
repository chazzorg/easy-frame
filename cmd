#!/usr/bin/env php
<?php

define('START_TIME', microtime(true));

// 定义应用目录
define ('APP_PATH',__DIR__.'/app/');

//定义框架目录
define ('CORE_PATH',__DIR__.'/src/');

//配置文件目录
define ('CONFIG_PATH',__DIR__ .'/config/');

//日志路径
define('LOG_PATH',__DIR__ .'/logs/');

require __DIR__.'/vendor/autoload.php';
require "app/Console/Kernel.php";

//执行命令
$cmd = new App\Console\Kernel();
$cmd->run();