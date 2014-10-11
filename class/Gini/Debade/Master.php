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

    public static function sendImmediately($channel, $message) {
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

        $redis_channel = \Gini\Config::get('debade.redis_channel') ?: 'my-redis-channel-for-message-async-send';

        \Gini\Cache::of('redis')->publish($redis_channel, $message);
    }
}
