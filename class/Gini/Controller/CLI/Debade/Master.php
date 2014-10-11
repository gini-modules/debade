<?php

namespace Gini\Controller\CLI\Debade
{
    class Master extends \Gini\Controller\CLI\Debade
    {
        public function actionListen()
        {
            $config = (array)\Gini\Config::get('debade.redis');

            $redis_channel = $config['channel'] ?: 'my-redis-channel-for-message-async-send';
            $redis_host = $config['host'] ?: '127.0.0.1';
            $redis_port = $config['port'] ?: 6379;
            $redis_password = $config['password'];

            $redis = new \Redis();
            $redis->connect($redis_host, $redis_port, 0);
            if (!empty($redis_password)) {
                $redis->auth($redis_password);
            }

            $redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);

            $redis->subscribe([$redis_channel], 'callback');
        }
    }
}

namespace
{
    function callback($instance, $channel, $message)
    {
        $data = json_decode($message);
        \Gini\Debade\Master::sendImmediately($data->channel, json_encode($data->content));
    }

}
