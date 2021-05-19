<?php
/**
 * Websocket Controller
 */
namespace Chazz\Controllers;

class WsController
{
    /**
     * @var swoole_server
     */
    protected $server;
    /**
     * @var swoole_server->frame->fd
     */
    protected $fd;
    /**
     * @var swoole_server->frame
     */
    protected $frame;
    /**
     * @var swoole_server->task
     */
    protected $task;
    /**
     * @var swoole_server->frame->data
     */
    protected $request;

    public function __set($name,$object)
    {
        $this->$name = $object;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    final public function get($key = null, $default = null)
    {
        $arr=[];
        if($this->request){
            $arr=$this->request;
        }
        if ($key === null) {
            return $arr;
        }
        if (isset($arr[$key])) {
            return $arr[$key];
        } else if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            foreach ($keys as $v) {
                if (isset($arr[$v])) {
                    $arr = $arr[$v];
                } else {
                    return $default;
                }
            }
            return $arr;
        } else {
            return $default;
        }
    }

    /**
     * 数组转JSON
     * @param array $array
     */
    final public function json($array = array())
    {
        return json_encode($array)?:'';
    }
}