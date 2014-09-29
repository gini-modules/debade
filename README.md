gini-debade
===========

## Message Queue Manager

    rabbitMQ server：rabbitMQ服务器提供了消息确认机制、消息持久化和均衡调度等机制保证消息队列有效执行

### debade-master
* 将message发送给rabbitMQ，没有必要作为驻留服务存在，在需要的时候代码内发送message到rabbitMQ
* 调用方式：\Gini\Debade\Master::send(‘CHANNEL’, ‘event name’[, ‘event data/message’]);
* 配置文件：debade.yml
        
        ---
            default: rabbitMQ
            rabbitMQ:
                driver: RabbitMQ
                options:
                    server: 127.0.0.1
                    port: 5672
                    user: guest
                    password: guest
        ...

### debade-agent
* gini debade-agent register
* gini debase-agent unregister
* gini debade-agent list 

