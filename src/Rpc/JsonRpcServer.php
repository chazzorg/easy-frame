<?php
/**
 * 基于Http的Json-Rpc服务
 */
namespace Chazz\Rpc;

use Chazz\Facades\Log;
use Chazz\Lib\Request;

/*
 * JsonRpcServer jsonRPC服务端类
 */
class JsonRpcServer{
    private $requests ;//请求列表
    private $responses;//返回信息
    private $controller;

    function __construct($controller){
        $this->controller = $controller;
    }

    function run(Request $request) {
        if ($this->checkRequest($request)) {
            foreach ($this->requests as $content) {
                $this->invoke($this->controller, $content);
            }
            return json_encode($this->responses);
        }
    }
    
    //检查请求是否合法
    function checkRequest($request){
        //检查协议头
        if($request->header('content-type') != 'application/json' && $request->header('x-requested-with') != 'jsonRPC'){
            return false;
        }

        //读取content
        $content = $request->rawContent();

        //json_decode
        $content = json_decode($content,true);
        if($content == null){
            return false;
        }
        $this->setRequests($content);
        if(empty($this->requests)){
            return false;
        }
        return true;
    }
    //过滤并填充请求列表
    function setRequests($content=array()){
        $content = (array)$content;
        foreach($content as $request){
            if(!isset($request['jsonrpc']) || $request['jsonrpc']!='2.0' || !isset($request['method']) || !isset($request['id'])){
                continue;
            }
            $this->requests[] = $request;
        }
    }
    //调用方法
    function invoke($controller,$content){
        extract($content);
        if(!isset($params)){
            $params = array();
        }
        $response = array(
            'jsonrpc'=>'2.0',
            'id'=>$id
        );
        if(!method_exists($controller, $method)){
            $response['error'] = '远程方法'.$method.'不存在';
        }else{
            $response['result'] = call_user_func_array(array($controller,$method), $params);//调用函数得到结果
        }
        $this->responses[] = $response;
    }
}