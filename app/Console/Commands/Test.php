<?php

namespace App\Console\Commands;

use Chazz\Console\Commands;
use App\Model\User;
use Chazz\Facades\Db;
use Chazz\Lib\Request;
use Chazz\Facades\Redis;
use Chazz\Controllers\Controller;
use Chazz\Facades\Log;
use Chazz\Facades\Event;
use App\Queue\TestQueue;

class Test extends Commands {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name=true;

    /**
     * The console command opts.
     *
     * @var array
     */
    protected $opts=[];



    public function handle()
    {
        $type=$this->argument('name');
        if($type=='pu'){
            $res=TestQueue::send('haha1111');
        }else if($type=='get'){
            $res=TestQueue::work();
        }
        dd($res);
    }

}
