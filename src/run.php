<?php
namespace Chazz;

class Start 
{

    protected static $opt         = null ;
    protected static $type        = null ;
    protected static $ip          = null ;
    protected static $port        = null ;
    protected static $config      = null ;
    protected static $server      = null ;
    protected static $pid_file    = null ;
    protected static $log_file    = null ;

    /**
     * 环境检测
     * @var null
     */
    public static function check()
    {
        if (php_sapi_name() != "cli") {
            echo "仅允许在命令行模式下运行",PHP_EOL;
            exit;
        }
        if (version_compare(phpversion(), '7.0', '<')) {
            echo "PHP版本必须大于等于7.0 ，当前版本：",phpversion (),PHP_EOL;
            exit;
        }
        if (!class_exists('swoole_server')) {
            echo "系统缺少 Swoole\Server 类",PHP_EOL;
            exit;
        }
        if (version_compare(phpversion('swoole'), '4.3', '<')) {
            echo "Swoole 版本必须大于等于 4.3 ，当前版本：",phpversion ('swoole'),PHP_EOL;
            exit;
        }
        //检查日志目录是否存在并创建
        !is_dir(LOG_PATH) && mkdir(LOG_PATH,0777 ,TRUE);
        if(!is_dir(LOG_PATH)){
            echo LOG_PATH."文件夹创建失败,请检查权限",PHP_EOL;
            exit;
        }
        if(!in_array (self::$opt , ['start','stop','reload'])){
            echo PHP_EOL,"指令错误:",PHP_EOL,"     php swoole [start|stop|reload]",PHP_EOL,PHP_EOL;
            exit;
        }
        if(self::$type && !in_array (self::$type , ['http','ws','tcp','rpc'])){
            echo PHP_EOL,"缺少服务:",PHP_EOL,"     php swoole start [http|ws|tcp|rpc]",PHP_EOL,PHP_EOL;
            exit;
        }
    }

    /**
     * 配置服务
     * @var null
     */
    public static function setConfig()
    {
        global $argv;
        list( , $opt , $type) = array_pad($argv,3,'');
        $appConfig = config('app');
        self::$opt = $opt ?: 'start';
        self::$type = $type ?: $appConfig['type'];
        self::$config = $appConfig[self::$type]??[];
        self::$ip = self::$config['ip']??'';
        self::$port = self::$config['port']??'';
        self::$pid_file = self::$config['pid_file']??'';
        self::$log_file = self::$config['log_file']??'';
    }

    /**
     * 命令执行
     * @var null
     */
    public static function argv()
    {
        switch (self::$opt){
            case 'start':
                //检测进程是否已开启
                $pid = self::getPid();
                if ($pid && \Swoole\Process::kill((int)$pid, 0)) {
                    exit("swoole server process already exist!\n");
                }
                //日志文件存储
                if (!is_file(self::$log_file)) {
                    $resource = fopen(self::$log_file, "w");
                    fclose($resource);
                }
                break;
            case 'stop':
                if (!$pid = self::getPid()) {
                    exit("swoole server process not started!\n");
                }
                if (\Swoole\Process::kill((int)$pid)) {
                    exit("swoole server process close successful!\n");
                }
                exit("swoole server process close failed!\n");
                break;
            case 'reload'://仅重启onWorkerStart或onReceive等在Worker进程中include/require的PHP文件
                if(self::$ip && self::$port && self::$type){
                    $sing    = self::$type.':reload-'.time();
                    $encrypt = encrypt($sing, 'chazz-frame');
                    switch (self::$type) {
                        case 'http':
                            Http(self::$ip.':'.self::$port, ['server:reload'=>$encrypt]);
                            exit("Http服务重载成功\n");
                            break;
                        case 'rpc':
                            Http(self::$ip.':'.self::$port, ['server:reload'=>$encrypt]);
                            exit("Rpc服务重载成功\n");
                            break;
                        case 'ws':
                            Http(self::$ip.':'.self::$port, ['server:reload'=>$encrypt]);
                            exit("WebSocket服务重载成功\n");
                            break;
                        case 'tcp':
                            $client = new \swoole\client(SWOOLE_SOCK_TCP);
                            $client->connect(self::$ip, self::$port, -1);
                            $client && $client->send('server:reload'.$encrypt);
                            $client && $client->close();
                            exit("TCP服务重载成功\n");
                            break;
                        default:
                            exit("服务未启动\n");
                            break;
                    }
                }
                exit("服务未配置\n");
                break;
        }
    }

     /**
     * 设置服务
     * @var null
     */
    public static function setServer()
    {
        $class=self::$config['server'];
        if (!class_exists($class)) {
            echo "缺少服务配置!",PHP_EOL;
            exit;
        }
        self::$server = $class::getInstance();
    }

    /**
     * 启动服务
     * @var null
     */
    public static function run()
    {
        self::setConfig();//配置信息
        self::check();//环境检测
        self::argv();//命令检测
        self::setServer();//设置服务
        $worker = self::$server;
        $worker->setConfig(self::$config);
        $worker->run();
    }

    /**
     * 获取服务启动之后pid
     * @var int|bool
     */
    private static function getPid()
    {
        return file_exists(self::$pid_file) ? file_get_contents(self::$pid_file) : false;
    }

}