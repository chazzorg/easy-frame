<?php
namespace Chazz\Mq;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Queue
{
    /**
     * mq 队列
     * @var [type]
     * @author chazz
     * @date 2020-04-30
     * @version
     */
    public static $instance;//实例
    public $config;//配置
    public $channel;//信道
    public $connection;//连接
    public  $Exchange;//交换机名
    public  $ExchangeType;//交换机类型
    public  $queue;//队列名
    public  $route;//路由
    public  $durable;//持久化
    public  $autoAck;//ack自动应答
    public  $prefetchCount=1000;//消费者并行处理限制
    public function __clone(){}
    public function __construct () {}

    public static function getInstance()
    {
        if(is_null (self::$instance)){
            self::$instance=new static();
            self::$instance->init();
        }
        return self::$instance;
    }

    public function init()
    {
        if(!$this->config){
            $this->setConfig();
        }
        if(!$this->connection){
            $this->createConnect();
        }
    }

    public function setConfig($config=[]){ 
        if (is_array($config) && $config){
            foreach ($config as $key => $value) {
                $this->config[$key]=$value;
            }
        }else{
            $this->config['host']=config('mq.host');
            $this->config['port']=config('mq.port');
            $this->config['user']=config('mq.user');
            $this->config['password']=config('mq.pass');
            $this->config['vhost']=config('mq.vhost');
        }
        return $this;
    }

    /**
     * 创建连接
     * @return void
     * @author chazz
     * @date 2020-05-14
     * @version
     */
    public function createConnect()
    {
        $host   = $this->config['host'];
        $port   = $this->config['port'];
        $user   = $this->config['user'];
        $vhost  = $this->config['vhost'];
        $password = $this->config['password'];
        if(empty($host) || empty($port) || empty($user) || empty($password) || empty($vhost)){
            return false;
        }

        try {
            $this->connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
            $channel_id=$this->connection->get_free_channel_id();
            if($channel_id){
                $this->channel=$this->connection->channel($channel_id);
            }else{
                $this->channel=$this->connection->channel();
            }
            $this->exchangeDeclare();
            $this->queueDeclare();
            $this->queueBind();
        } catch (\Throwable $th) {
            $this->connection = null ;
        }
    }

    /**
     * 交换器声明
     * exchange: 交换器名称
     * type : 交换器类型 DIRECT("direct"), FANOUT("fanout"), TOPIC("topic"), HEADERS("headers");
     * durable: 是否持久化,durable设置为true表示持久化,反之是非持久化,持久化的可以将交换器存盘,在服务器重启的时候不会丢失信息.
     * autoDelete是否自动删除,设置为TRUE则表是自动删除,自删除的前提是至少有一个队列或者交换器与这交换器绑定,之后所有与这个交换器绑定的队列或者交换器都与此解绑,一般都设置为fase
     * internal 是否内置,如果设置 为true,则表示是内置的交换器,客户端程序无法直接发送消息到这个交换器中,只能通过交换器路由到交换器的方式
     * @return void
     * @author chazz
     * @date 2020-05-14
     * @version
     */
    public function exchangeDeclare()
    {
        $this->channel->exchange_declare($this->Exchange, $this->ExchangeType, $this->durable?:false, true, false);
    }

    /**
     * 队列声明
     * queue 队列名称
     * passive 消极处理，为true时判断是否存在队列，存在则返回，不存在直接抛出
     * durable 重启后是否会重建这个队列，在服务器重启时，能够存活
     * exclusive 排他队列,在连接断开后，会自动删除该队列.不管是否设置了持久化或者自动删除.
     * autodelete 当没有任何消费者使用时，自动删除该队列
     * @return void
     * @author chazz
     * @date 2020-05-14
     * @version
     */
    public function queueDeclare()
    {
        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    /**
     * Undocumented function
     * @return void
     * @author chazz
     * @date 2020-05-15
     * @version
     */
    public function queueBind()
    {
        $this->channel->queue_bind($this->queue, $this->Exchange, $this->route, false);
    }

    /**
     * 发送消息
     * @param [string] $message
     * @param [bool] $continue
     * @return void
     * @author chazz
     * @date 2020-05-14
     * @version
     */
    public function dispatch($message,$continue=false)
    {
        if(!$this->connection->isConnected()) {
            $this->connection->connect();
        }
        $msg = new AMQPMessage($message, array(
            'content_type' => 'text/plain', 
            'delivery_mode' => $this->durable?AMQPMessage::DELIVERY_MODE_PERSISTENT:AMQPMessage::DELIVERY_MODE_NON_PERSISTENT //DELIVERY_MODE_NON_PERSISTENT=1,DELIVERY_MODE_PERSISTENT = 2
        ));
        $this->channel->basic_publish($msg, $this->Exchange, $this->route,true);
        if(!$continue){
            $this->closeConnetct();
        }
        return true;
    }

    /**
     * 发送消息门面调用方法
     * @param [type] $message
     * @param bool $continue
     * @return void
     * @author chazz
     * @date 2020-05-20
     * @version
     */
    public static function send($message,$continue=false)
    {
        return self::getInstance()->dispatch($message,$continue=false);
    }

    /**
     * 消费队列消息
     * @return void
     * @author chazz
     * @date 2020-05-14
     * @version
     */
    public function dealMq()
    {
        $this->channel->queue_bind($this->queue, $this->Exchange, $this->route);
        $this->basicQos();
        $this->basicConsume();
        //监听消息
        while(count($this->channel->callbacks)){
            $this->channel->wait();
        }
    }

    /**
     * 消费队列门面调用方法
     * @author chazz
     * @date 2020-05-20
     * @version
     */
    public static function work()
    {
        return self::getInstance()->dealMq();
    }

    /**
     * 队列消费限制
     * prefetchSize：0 
     * prefetchCount：会告诉RabbitMQ不要同时给一个消费者推送多于N个消息，即一旦有N个消息还没有ack，则该consumer将block掉，直到有消息ack
     * global：true\false 是否将上面设置应用于channel，简单点说，就是上面限制是channel级别的还是consumer级别
     * @return void
     * @author chazz
     * @date 2020-05-14
     * @version
     */
    public function basicQos()
    {
        $this->channel->basic_qos(0, $this->prefetchCount, false);
    }

    /**
     * 获取队列
     * queue 要取得消息的队列名
     * consumer_tag 消费者标签
     * no_local false这个功能属于AMQP的标准,但是rabbitMQ并没有做实现.参考
     * no_ack false收到消息后,是否不需要回复确认即被认为被消费
     * exclusive false排他消费者,即这个队列只能由一个消费者消费.适用于任务不允许进行并发处理的情况下.比如系统对接
     * nowait false不返回执行结果,但是如果排他开启的话,则必须需要等待结果的,如果两个一起开就会报错
     * callback null回调函数
     * @return void
     * @author chazz
     * @date 2020-05-14
     * @version
     */
    public function basicConsume()
    {
        $this->channel->basic_consume($this->queue, '', false, $this->autoAck, false, false, function($message){
            $this->get($message);
        });
    }
    
    /**
     * 手动执行消费
     * @param Type $var
     * @return void
     * @author chazz
     * @date 2020-05-14
     * @version
     */
    public function manual($queue)
    {
        $message = $this->channel->basic_get($queue); //取出消息
        $this->get($message);
    }

    //获取消息处理
    public function get($message)
    {
        $param=$message->body;
        $res=$this->doProcess($param);
        if(!$this->autoAck && $res)
        {
            $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
        }
    }

    //关闭连接
    public function closeConnetct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}