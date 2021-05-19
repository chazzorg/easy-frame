# easy-frame

# 极简、快速，基于swoole微型的web框架easy-frame
===================

## hello world
环境变量
路由
类静态代理
模板引擎更换参考 https://www.bookstack.cn/read/easySwoole-2.x-cn/Base-template.md
事件
异步日志
redis
mysql
模型
tcp服务
websocket服务
http服务
task任务
UUID
服务热重启
TCP客户端
JSON-RPC客服端
JSON-RPC服务端
自定义命令
RabbitMQ队列


安装

```shell
cd easy-frame
composer install
php run  [start|stop|reload] [http|tcp|ws|rpc] 
or
php run (默认启动http服务)
```

测试

```shell
curl http://127.0.0.1:9501/
```