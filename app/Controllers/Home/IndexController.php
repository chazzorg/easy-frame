<?php
namespace App\Controllers\Home;

use App\Model\User;
use Chazz\Facades\Db;
use Chazz\Lib\Request;
use Chazz\Facades\Redis;
use Chazz\Controllers\Controller;
use Chazz\Facades\Log;
use Chazz\Facades\Event;

class IndexController extends Controller
{

    public function index(Request $request)
    {
        echo 22222;
        // $this->json(['name'=>1,'age'=>2]);
        // $this->display('index/index',['name'=>'hello world']);
    }

    public function init(Request $request)
    {
        // Log::write('INFO',$request->get('id'));
        // Event::listen('start',$time);
        // $this->task->delivery (\App\Task\Test::class,'test',[1,time()]);
        // $user= new User();
        // $res=$user->get_one ("`name`='王二'");
        // $re=Redis::set('id',33333);
        // $res=Db::table('users')->get_one ("`name`='王二'");
    }
}