<?php
namespace Chazz;

class Log
{
    /**
     * 实例
     * @var object
     */
    private static $instance ;
    /**
     * 配置参数
     * @var array
     */
    private static $config = [];

    private static $logs = [];
    private static $dir_path;

    private function __construct (){
        $dir_path = LOG_PATH.date('Ymd').DIRECTORY_SEPARATOR;
        !is_dir($dir_path) && mkdir($dir_path,0777,TRUE);
        self::$dir_path=$dir_path;
    }

    public static function getInstance(){
        if(is_null (self::$instance)){
            self::$config = config('app.log');
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 写入日志
     * @param       $type
     * @param array ...$msg
     */
    public function write($type,...$logs){
        $type = strtoupper ($type);
        $msg = "{$type} \t ".date("Y-m-d H:i:s")." \t ".join (" \t ",$logs);
        if( !in_array($type,self::$config['level'])) return false;
        if(self::$config['echo']){
            echo $msg,PHP_EOL;
        }
        self::$logs[$type][]=$msg;
    }

    /**
     * swoole异步写入日志信息
     * @param mixed  $msg   调试信息
     * @param string $type  信息类型
     * @return bool
     */
    public function save(){
        if (empty(self::$logs)) return false;
        foreach(self::$logs as $type => $logs){
            $content = NULL ;
            foreach($logs as $log){
                $content .=$log.PHP_EOL;
            }
            $Co_filename=self::$dir_path.date("H").'.'.$type.'.log';
            \Swoole\Coroutine::create(function () use ($Co_filename,$content)
            {
                \Swoole\Coroutine::writeFile($Co_filename , $content, FILE_APPEND);
            });
        }
        self::$logs = [] ;
        return true;
    }
}