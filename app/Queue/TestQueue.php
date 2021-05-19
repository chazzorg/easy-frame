<?php

namespace App\Queue;

use Chazz\Mq\Queue;

class TestQueue extends Queue{

    public $Exchange = 'bbq';

    public $ExchangeType = 'direct';

    public $queue='test-queue';

    public $route='test-route';

    public $durable=true;

    public $autoAck=false;

    public function __construct (){}
    
    public function doProcess($data)
    {
        var_dump($data);
    }

}
