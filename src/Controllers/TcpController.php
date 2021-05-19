<?php
/**
 * Tcp Controller
 */
namespace Chazz\Controllers;

class TcpController
{
    /**
     * @var swoole_server
     */
    protected $server;
    /**
     * @var swoole_server->fd
     */
    protected $fd;
    /**
     * @var swoole_server->reactor_id
     */
    protected $reactor_id;
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