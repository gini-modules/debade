<?php

namespace Gini\Debade;

class Master
{
    public static function send($channel, $message=null, $type='message')
    {
        $message = json_encode([
            'channel'=> $channel,
            'type'=> $type,
            'content'=> [
                'data'=> $message,
                'time'=> microtime()
            ]
        ]);

        $config = (array)\Gini\Config::get('debade.redis');
        $redis_channel = $config['channel'] ?: 'my-redis-channel-for-message-async-send';
        $redis_host = $config['host'] ?: '127.0.0.1';
        $redis_port = $config['port'] ?: 6379;
        $redis_password = $config['password'];

        $redis = new \Redis();
        $redis->connect($redis_host, $redis_port);
        if (!empty($redis_password)) {
            $redis->auth($redis_password);
        }

        $redis->publish($redis_channel, $message);
    }
}
