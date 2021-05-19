<?php
namespace Chazz\Lib;

use Chazz\Lib\Request;
use Chazz\Lib\Task;
use Chazz\Facades\Router;
use Chazz\Rpc\JsonRpcServer;

class App
{
    //实例
    private static $instance;
    //映射表
    private static $map = [];

    private function __construct () {}

    /**
     * Undocumented function
     * @return void
     * @author chazz
     * @date 2020-01-10
     * @version
     */
    public function getInstance()
    {
        if(is_null (self::$instance)){
            self::$instance = new self();
        }
        return self:: $instance;
    }

    /**
     * Undocumented function
     * @param [type] $server
     * @param [type] $request
     * @param [type] $response
     * @return void
     * @author chazz
     * @date 2020-01-10
     * @version
     */
    public function http($server,$request,$response)
    {
        $req = Request::getInstance();
        $req->set($request);
        if($req->server('request_uri') == '/favicon.ico') return ;
        $router        = Router::http($req->server('request_uri'));
        $app_namespace = config('app.namespace');
        $module        = $router['m'] ;
        $controller    = $router['c'] ;
        $action        = $router['a'] ;
        $classname     = $app_namespace.$module."\\".$controller."Controller";

        try{
            if(class_exists($classname) && method_exists($classname,$action)){
                if(!isset(self::$map[$classname])){
                    $class = new $classname ;
                    if(get_parent_class ($class)!='Chazz\Controllers\Controller'){
                        $response->header('Content-type',"text/html;charset=utf-8;");
                        $response->status(503);
                        $response->end('503 Service Unavailable');
                        return ;
                    }
                    self::$map[$classname] = $class;
                }
                if(!empty(ob_get_contents ())) {
                    ob_end_clean ();
                }
                ob_start();
                self:: $map[$classname]->server   = $server;
                self:: $map[$classname]->response = $response;
                self:: $map[$classname]->task     = Task:: getInstance()->setServer($server);
                self:: $map[$classname]->$action($req);
                $content = ob_get_contents();
                ob_end_clean();
                $response->end($content);
                return ;
            }else{
                $response->header('Content-type',"text/html;charset=utf-8;");
                $response->status(404);
                $response->end('404 NOT FOUND');
                return ;
            }
        }catch(\Throwable $th){
            $response->header('Content-type',"text/html;charset=utf-8;");
            $response->status(500);
            $response->end('Server Bad');
            return ;
        }
    }

    public function websocket($server,$frame)
    {
        $router        = Router::websocket( $frame ->data );
        $app_namespace = config('app.namespace');
        $module        = $router['m'] ;
        $controller    = $router['c'] ;
        $action        = $router['a'] ;
        $data          = $router['data'] ;
        $classname     = $app_namespace.$module."\\".$controller."Controller";
        try {
            if (class_exists($classname) && method_exists($classname, $action)) {
                if (! isset(self ::$map[ $classname ])) {
                    $class = new $classname;
                    if (get_parent_class($class)!='Chazz\Controllers\WsController') {
                        echo "[{$classname}]  必须继承 Chazz\Controllers\WsController",PHP_EOL;
                        return ;
                    }
                    self ::$map[ $classname ] = $class;
                }
                self:: $map[$classname]->server  = $server;
                self:: $map[$classname]->fd      = $frame->fd;
                self:: $map[$classname]->frame   = $frame;
                self:: $map[$classname]->request = $data;
                self:: $map[$classname]->task    = Task:: getInstance()->setServer($server);
                self:: $map[$classname]->$action();
                return ;
            }
        } catch (\Throwable $th) {
            $server->push($frame->fd,"Server Bad");
            echo $th->getMessage (),PHP_EOL;
            return ;
        }
        $server->push($frame->fd,$frame ->data);
        return ;
    }

    public function tcp($server, $fd, $reactor_id, $data)
    {
        $router        = Router::tcp($data);
        $app_namespace = config('app.namespace');
        $module        = $router['m'] ;
        $controller    = $router['c'] ;
        $action        = $router['a'] ;
        $data          = $router['data'] ;
        $classname     = $app_namespace.$module."\\".$controller."Controller";
        try {
            if (class_exists($classname) && method_exists($classname, $action)) {
                if (! isset(self ::$map[ $classname ])) {
                    $class = new $classname;
                    if (get_parent_class($class)!='Chazz\Controllers\TcpController') {
                        echo "[{$classname}]  必须继承 Chazz\Controllers\TcpController",PHP_EOL;
                        return ;
                    }
                    self ::$map[ $classname ] = $class;
                }
                self:: $map[$classname]->server      = $server;
                self:: $map[$classname]->fd          = $fd;
                self:: $map[$classname]->reactor_id  = $reactor_id;
                self:: $map[$classname]->task        = Task:: getInstance()->setServer($server);
                self:: $map[$classname]->$action($data);
            }else{
                echo "Not Found",PHP_EOL;
                return ;
            }
        } catch (\Throwable $th) {
            echo $th->getMessage (),PHP_EOL;
            return ;
        }
    }

    public function rpc($server,$request,$response)
    {
        $req = Request::getInstance();
        $req->set($request);
        if($req->server('request_uri') == '/favicon.ico') return ;
        $router        = Router::rpc($req->server('request_uri'));
        $app_namespace = config('app.namespace');
        $module        = $router['m'] ;
        $controller    = $router['c'] ;
        $classname     = $app_namespace.$module."\\".$controller."Controller";
        $error = array(
            'jsonrpc' => '2.0',
            'id'      => '',
            'error'   => ''
        );
        
        try{
            if(class_exists($classname)){
                if(!isset(self::$map[$classname])){
                    $class = new $classname ;
                    if(get_parent_class ($class)!='Chazz\Controllers\JsonRpcController'){
                        $response->header('Content-type',"application/json;");
                        $response->status(200);
                        $response->end(json_encode(array_merge($error,['error'=>'Service Unavailable'])));
                        return ;
                    }
                    self::$map[$classname] = $class;
                }
                self::$map[$classname]->server = $server;
                self::$map[$classname]->task   = Task::getInstance()->setServer($server);
                      $jsonRpcServer           = new JsonRpcServer(self::$map[$classname]);  //初始化jsonRpc服务端
                      $content                 = $jsonRpcServer->run($req);
                $response->header('Content-type',"application/json;");
                $response->status(200);
                $response->end($content);
                return ;
            }else{
                $response->header('Content-type',"application/json;");
                $response->status(200);
                $response->end(json_encode(array_merge($error,['error'=>'Not Found'])));
                return ;
            }
        }catch(\Throwable $th){
            $response->header('Content-type',"application/json;");
            $response->status(200);
            $response->end(json_encode(array_merge($error,['error'=>'Server Bad'])));
            return ;
        }
    }

}