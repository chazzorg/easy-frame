<?php
namespace Chazz\Lib;

class Router
{
    private static $instance;
    private static $config = [];
    private function __construct() {}

    public static function getInstance() {
        if( is_null(self::$instance) ) {
            self::$instance = new self();
            self::$config = config('app.router');
        }
        return self::$instance;
    }

    /**
     * Http 路由解析
     */
    public function http($request_uri){
        $module 	= self::$config['m'];
        $controller = self::$config['c'];
        $action 	= self::$config['a'];
        $path = trim($request_uri, '/');
        if(!empty( self::$config['ext']) && substr($path,-strlen(self::$config['ext'])) == self::$config['ext'] ){
            $path = substr($path , 0 , strlen($path)-strlen(self::$config['ext']));
        }
        if (!empty(config('routes'))) {
            foreach (config('routes') as $key => $value) {
                if(substr($path,0,strlen($key)) == $key) {
                    $path = str_replace($key, $value, $path);
                    break;
                }
            }
        }
        $path = explode( "/" , $path)?:[];
        if(count($path) > 2){
            list($module, $controller, $action) = $path;
        }
        return ['m'=>ucwords($module) ,'c'=>ucwords($controller), 'a'=>ucwords($action)];
    }

    /**
     * WebSocket 路由解析
     */
    public function websocket($data) {
        $data = json_decode ($data, true);
        if(empty($data)){
            return ['m'=>null ,'c'=>null,'a'=>null, 'data' =>null ];
        }
        $path = empty($data['action']) ? '' : trim($data['action'], '/');
        if(empty($path)){
            return ['m'=>null ,'c'=>null,'a'=>null, 'data' =>null ];
        }
        $param      = explode( "/" , $path)?:[];
        $module     = array_shift ($param);
        $controller = array_shift ($param);
        $action     = array_shift ($param);
        unset($data['action']);
        return ['m'=>ucwords($module) ,'c'=>ucwords($controller), 'a'=>ucwords($action), 'data' => $data];
    }

    /**
     * Tcp 路由解析
     */
    public function tcp($data) {
        $data = json_decode ($data, true);
        if(empty($data)){
            return ['m'=>null ,'c'=>null,'a'=>null, 'data' =>null ];
        }
        $path = empty($data['action']) ? '' : trim($data['action'], '/');
        if(empty($path)){
            return ['m'=>null ,'c'=>null,'a'=>null, 'data' =>null ];
        }
        $param      = explode( "/" , $path)?:[];
        $module     = array_shift ($param);
        $controller = array_shift ($param);
        $action     = array_shift ($param);
        unset($data['action']);
        return ['m'=>ucwords($module) ,'c'=>ucwords($controller), 'a'=>ucwords($action), 'data' => $data];
    }

    /**
     * RPC 路由解析
     */
    public function rpc($request_uri) {
        $module     = '';
        $controller = '';
        $path = trim($request_uri, '/');
        if (!empty(config('routes'))) {
            foreach (config('routes') as $key => $value) {
                if(substr($path,0,strlen($key)) == $key) {
                    $path = str_replace($key, $value, $path);
                    break;
                }
            }
        }
        $path = explode( "/" , $path)?:[];
        if(count($path) > 1){
            list($module, $controller) = $path;
        }
        return ['m'=>ucwords($module) ,'c'=>ucwords($controller)];
    }
}