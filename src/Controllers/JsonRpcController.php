<?php
/**
 * Rpc Controller
 */
namespace Chazz\Controllers;

class JsonRpcController
{
    /**
     * @var swoole_server->server
     */
    protected $server;
    /**
     * @var swoole_server->task
     */
    protected $task;

    public function __set($name,$object)
    {
        $this->$name = $object;
    }

    public function __get($name)
    {
        return $this->$name;
    }

}