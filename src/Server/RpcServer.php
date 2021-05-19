<?php
/**
 * Rpc-http 服务
 */
namespace Chazz\Server;

use Chazz\Lib\Task;
use Chazz\Facades\Log;
use Chazz\Facades\App;

class RpcServer
{
    private static $instance;
    public  $server;
    private static $config;
    private static $ip;
    private static $port;
    private function __construct () {}
    private function __clone() {}

    public static function getInstance()
    {
        if(is_null (self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setConfig($config)
    {
        self::$ip     = isset($config['ip']) && ip2long($config['ip']) ? $config['ip'] : '0.0.0.0';
        self::$port   = isset($config['port']) && intval($config['port']) ? $config['port'] : 9500;
        if($config && isset($config['server'])){
            unset($config['server']);
        }
        if($config && isset($config['ip'])){
            unset($config['ip']);
        }
        if($config && isset($config['port'])){
            unset($config['port']);
        }
        self::$config = $config;
    }

    public function run()
    {
        $this->server = new \Swoole\Http\Server(self::$ip,self::$port);
        $this->server->set(self::$config);
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('workerstart',[$this,'onWorkerStart']);
        $this->server->on('workerstop', [$this, 'onWorkerStop']);
        $this->server->on('workererror',[$this,'onWorkerError']);
        $this->server->on('managerStart', [$this, 'onManagerStart']);
        $this->server->on('managerStop', [$this, 'onManagerStop']);
        $this->server->on('request' ,[$this,'onRequest']);
        if( isset(self::$config['task_worker_num']) && self::$config['task_worker_num']>0){
            $this->server->on('task',[$this,'onTask']);
            $this->server->on('finish',[$this,'onFinish']);
        }
        $this->server->start();
    }

    /**
     * 启动后在主进程（master）的主线程回调此函数
     */
    public function onStart($server)
    {
        date_default_timezone_set('Asia/Shanghai');
        echo "Rpc服务启动成功    ","ip:".self::$ip,"    port:".self::$port,PHP_EOL;
    }

    /**
     * Worker进程/Task进程启动时
     */
    public function onWorkerStart($server,$workder_id)
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        date_default_timezone_set('Asia/Shanghai');
        //每3秒执行一次
        swoole_timer_tick(3000,function ($time_id){
            Log::save();
        });
    }
    
    /**
     * 当worker/task_worker进程发生异常
     */
    public function onWorkerError($server, $worker_id, $worker_pid, $exit_code)
    {
        Log::write('ERROR',"进程异常","WorkerID:{$worker_id}","WorkerPID:{$worker_pid}","ExitCode:{$exit_code}");
    }

    /**
     * worker进程终止时
     * @param  $server
     * @param  $worker_id
     */
    public function onWorkerStop($server, $worker_id)
    {
        Log::write('ERROR',"进程终止","WorkerID:{$worker_id}");
    }

    /**
     * 当管理进程启动时
     * @param $server
     */
    public function onManagerStart($server)
    {
        Log::write('INFO ',"管理进程启动");
    }

    /**
     * 当管理进程结束时
     * @param $server
     */
    public function onManagerStop($server)
    {
        Log::write('INFO',"管理进程结束");
    }

    /**
     * Http请求
     * @param $request
     * @param $response
     */
    public function onRequest($request,$response)
    {
        //请求时间
        define('START_TIME', microtime(true));
        if(isset($request->post['server:reload']) && $request->post['server:reload']){
            $encrypt=$request->post['server:reload'];
            $data=decrypt($encrypt,'chazz-frame');
            $data && list($name,$time)=explode('-',$data) ?: array_pad($data,2,'');
            if(time()-$time<2 && $name='rpc:reload'){
                $this->server->reload();
                return '';
            }
        }
        if($request->server['request_uri'] == '/favicon.ico'){
            return ;
        }
        App::rpc($this->server,$request,$response);
    }

    /**
     * Task任务
     * @param $server
     */
    public function onTask($server,$task){
        return Task::dispatch($server,$task->task_id ?? -1,$task->workder_id ?? -1,$task->data??[]);
    }

    /**
     * Task任务完成
     * @param $server
     */
    public function onFinish($server,$task_id,$data){
        Task::finish($task_id,$data);
    }
}