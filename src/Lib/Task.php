<?php
/**
 * Task异步任务
 */
namespace Chazz\Lib;

final class Task
{
    private static $instance;
    public  $server;
    private function __construct () {}

    final public static function getInstance(){
        if( is_null(self::$instance) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    final public function setServer($server){
        self::$instance->server = $server;
        return self::$instance;
    }

    /**投递任务
     * @param $class        \app\task\classname
     * @param $func
     * @param $params       []
     *
     * @return swoole_server->task_id
     */
    final public function delivery($class,$func,$params=[]){
        $task_id = self::$instance->server->task([$class,$func,$params]);
        return $task_id;
    }

    /**
     * 调度任务
     */
    final public function dispatch($server,$task_id,$workder_id,$data){
        if(empty($data)){
            return false;
        }
        list($classname, $func, $params) = $data;
        $class = (new $classname);
        $class->server = $server;
        return $class->$func(...$params);
    }

    /**
     * 完成任务
     */
    final public function finish($task_id,$data){
        //log
    }

    public function __get($name){
        return $this->$name;
    }
}