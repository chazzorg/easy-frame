<?php
/**
 * http Controller
 */
namespace Chazz\Controllers;

class Controller
{
    /**
     * @var swoole_server->response
     */
    protected $response;
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

    /**
     * 渲染输出JSON
     * @param array $array
     * @param null  $callback
     */
    final public function json($array = array(),$callback=null)
    {
        $this->response->header('Content-type','application/json');
        $json = json_encode($array);
        $json = is_null($callback) ? $json : "{$callback}({$json})" ;
        echo $json;
    }

    /**
     * 渲染模板
     * @param null $file 为空时，
     * @param bool $param 传递数据
     * @return string
     */
    final public function display($path, $param = array())
    {
        if(is_array($param)){
            extract($param);
        }
        $path = config('app.view').$path.'.php';
        if(!file_exists ($path)){
            $this->response->status(404);
            $this->response->end("模板不存在：".$path);
            return ;
        }
        if(!empty(ob_get_contents())) ob_end_clean ();
        ob_start();
        include $path;
    }
}