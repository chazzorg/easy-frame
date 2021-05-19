<?php
namespace App\Controllers\Home;

use App\Model\User;
use Chazz\Facades\Db;
use Chazz\Facades\Log;
use Chazz\Facades\Redis;
use Chazz\Facades\Event;
use Chazz\Controllers\JsonRpcController;

class RpcController extends JsonRpcController
{
    public function index(){
         // $this->task->delivery (\App\Task\Test::class,'test',[1,time()]);
    }

    public function hello($name,$msg)
    {
        return $name.','.$msg.'!';
    }

    public function bye($name)
    {
        return $name.','."bye!";
    }

    public function uuid()
    {
        return uuid();
    }
}