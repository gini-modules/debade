<?php

namespace Gini\Debade;

class Master
{
    private static $_config = null;
    private static function _getConfig()
    {
        $config = self::$_config;
        if (!$config) {
            $c = (array)\Gini\Config::get('debade.master');
            $config = $c[$c['default']];
        }
        return $config;
    }

    private static $_driver = null;
    private static function _getDriver()
    {
        if (!self::$_driver) {
            $config = self::_getConfig();
            $driverClass = "\\Gini\\Debade\\Driver\\{$config['driver']}";
            self::$_driver = \Gini\IoC::construct($driverClass, $config['options']);
        }
        return self::$_driver;
    }

    public static function sendImmediately($channel, $message) 
    {
        $driver = self::_getDriver();
        return $driver->send($channel, $message);
    }

    public static function send($channel, $event_name, $message=null, $type='message')
    {
        $message = json_encode([
            'channel'=> $channel,
            'type'=> $type,
            'content'=> [
                'event'=> $event_name,
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
