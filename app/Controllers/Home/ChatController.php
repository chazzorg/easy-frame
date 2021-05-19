<?php
namespace App\Controllers\Home;

use App\Model\User;
use Chazz\Facades\Db;
use Chazz\Facades\Redis;
use Chazz\Facades\Log;
use Chazz\Facades\Event;
use Chazz\Controllers\WsController;

class ChatController extends WsController
{
    public function index(){
        $res=$this->get('co');
        var_dump($res);
    }
}