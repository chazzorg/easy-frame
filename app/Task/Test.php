<?php
namespace App\Task;

use Chazz\Facades\Redis;

class Test{

    public function test($fd,$data)
    {
        Redis::set(123456,"ToAll");
        return ;
    }
}