<?php
/**
 * Http协议Json-Rpc客服端
 */
namespace Chazz\Rpc;

use Chazz\Facades\Log;

/**
 * Json-Rpc客户端
 */
class jsonRPCClient {
    private $url;//服务端url
    private $response = array();
    private $request  = array();
    private $id       = null;  //当前id
    private $Multi    = false; //是否批量
    public function __construct($url = "") {
        //检测url是否合法
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \Exception('url不合法');
        }
        $this->url = $url;
    }
    
    //魔术方法，当请求不存在的方法时触发
    public function __call($method,$params){
        if($this->id == null){
            throw new \Exception('请先调用rpcBind命令绑定一个变量');
        }
        if (!is_scalar($method)) {
            throw new \Exception('方法名不合法');
        }
        $params = array_values($params);
        $this->request[] = array(
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => $this->id
        );
        $this->id = null;
        //没有开启批量功能的话直接提交
        if(!$this->Multi){
            $this->rpcCommit();
        }else{
            return $this;
        }
    }

    /**
     *随机id
     */
    private function generateId() {
        $chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        $id = '';
        for($c = 0; $c < 16; ++$c)
            $id .= $chars[mt_rand(0, count($chars) - 1)];
        return $id;
    }
    
    /**
     *绑定变量
     */
    public function rpcBind(&$name){
        //$this->id = $id = $this->generateId();
        $this->id = $id = uuid();
        $this->response[$id] = &$name;
        $this->response[$id]=null;
        return $this;
    }
    
    //开启批量提交，需要手动调用rpcCommit提交
    public function rpcMulti($open=true){
        if($open){
            $this->Multi = true;
        }else{
            $this->Multi = false;
        }
    }
    
    //提交请求
    public function rpcCommit(){
        if(empty($this->request)){
            return false;
        }
        $request = json_encode($this->request);
        //拼装成一个request请求
        $opts = array ('http' => array (
                'method'  => 'POST',
                'header'  => "Content-type: application/json\nX-Requested-With: jsonRPC",
                'content' => $request
            ));
        $context  = stream_context_create($opts);
        if ($fp = file_get_contents($this->url, false, $context)) {
            $response = json_decode($this->response,true);
            if($response){
                foreach($response as $res){
                    if(is_string($res) || !isset($res['id']) || !array_key_exists($res['id'],$this->response)){
                        continue;
                    }
                    $this->response[$res['id']] = $res;
                }
            }else{
                //log->$fp
            }
        } else {
            return false;
        }
    }
}

// $jsonRpcClient = new JsonRpcClient("http://192.168.159.128:9503/home/rpc");
// $jsonRpcClient->rpcMulti();//开启批量请求模式
// $jsonRpcClient->rpcBind($a)->index("1","33");
// $jsonRpcClient->rpcBind($b)->bye();
// $jsonRpcClient->rpcCommit();//提交请求
// if(isset($a['error'])){
//     echo $a['error'];
// }else{
//     echo $a['result']."<br/>";
// }
// if(isset($b['error'])){
//     echo $b['error'];
// }else{
//     echo $b['result'];
// }