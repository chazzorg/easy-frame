<?php
/**
 * TCP服务
 */
namespace Chazz\Server;

use Chazz\Lib\Task;
use Chazz\Facades\Log;
use Chazz\Facades\App;

class TcpServer
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

    /**
     * 启动服务
     */
    public function run()
    {
        $this->server = new \Swoole\Server(self::$ip, self::$port, SWOOLE_BASE, SWOOLE_SOCK_TCP);
        $this->server->set(self::$config);
        $this->server->on('start', [$this, 'onStart']);
        $this->server->on('workerstart',[$this,'onWorkerStart']);
        $this->server->on('workerstop', [$this, 'onWorkerStop']);
        $this->server->on('workererror',[$this,'onWorkerError']);
        $this->server->on('managerStart', [$this, 'onManagerStart']);
        $this->server->on('managerStop', [$this, 'onManagerStop']);
        $this->server->on('connect' ,[$this,'onConnect']);
        $this->server->on('receive' ,[$this,'onReceive']);
        $this->server->on('pipeMessage' ,[$this,'onPipeMessage']);
        $this->server->on('close' ,[$this,'onClose']);
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
        echo "TCP服务启动成功    ","ip:".self::$ip,"    port:".self::$port,PHP_EOL;
    }

    /**
     * Worker进程/Task进程启动时
     */
    public function onWorkerStart($server,$workder_id)
    {
        date_default_timezone_set('Asia/Shanghai');
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
    public function onWorkerStop( $server, $worker_id)
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
     * 
     *
     * @param [Swoole\Server] $server
     * @param [int] $fd
     * @param [int] $reactorId 来自哪个Reactor线程
     * @return void
     */
    public function onConnect($server,$fd,$reactorId){
        Log::write('INFO',"FD:{$fd}","握手成功");
    }

    /**
     * 接收到数据时回调此函数，发生在worker进程中
     *
     * @param [type] $server
     * @param [type] $fd
     * @param [type] $reactor_id TCP连接所在的Reactor线程ID
     * @param [type] $data 收到的数据内容，可能是文本或者二进制内容
     * @return void
     */
    public function onReceive($server , $fd , $reactor_id , $data){
        if(substr($data, 0, 13) === 'server:reload'){
            $encrypt=decrypt(substr($data, 13),'chazz-frame');
            $encrypt && list($name,$time)=explode('-',$encrypt) ?: array_pad($encrypt,2,'');
            if(time()-$time<2 && $name='tcp:reload'){
                $this->server->reload();
                return '';
            }
        }
        App::tcp($server , $fd , $reactor_id , $data);
    }

    /**
     * 当工作进程收到由 sendMessage 发送的管道消息时会触发onPipeMessage事件
     *
     * @param [swoole_server] $server
     * @param [int] $src_worker_id 消息来自哪个Worker进程
     * @param [mixed] $message 任意PHP类型
     * @return void
     */
    public function onPipeMessage($server, $src_worker_id, $message){

    }

    /**
     * Task任务
     * @param $server
     */
    public function onTask($server,$task){
        return Task::dispatch($server,$task->task_id ?? -1,$task->workder_id ?? -1,$task->data??[]);
    }

    /**
     * 当worker进程投递的任务在task_worker中完成时，
     * task进程会通过swoole_server->finish()方法将任务处理的结果发送给worker进程。
     * 
     * swoole_server $server
     * $task_id是任务ID，由swoole扩展内自动生成，用于区分不同的任务。$task_id和$src_worker_id组合起来才是全局唯一的，不同的worker进程投递的任务ID可能会有相同
     * $data 是任务的内容
     */
    public function onFinish($server,$task_id,$data){
    }

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     *
     * @param [Swoole\Server] $server
     * @param [int] $fd
     * @param [int] $reactorId 来自那个reactor线程，主动close关闭时为负数
     * @return void
     */
    public function onClose($server,$fd,$reactorId){
        Log::write('INFO',"FD:{$fd}","关闭连接");
    }


}