<?php

namespace Gini\Debade\Driver;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ extends \Gini\Debade\Driver
{
    private static $_connection = null;
    private static $_channel = null;
    public function __construct(array $options=[])
    {
        $server = $options['server'] ?: '127.0.0.1';
        $port = $options['port'] ?: 5672;
        $user = $options['user'] ?: 'guest';
        $password = $options['password'] ?: 'guest';

        if (!self::$_connection) {
            self::$_connection = new AMQPConnection($server, $port, $user, $password);
        }
        if (self::$_connection && !self::$_channel) {
            self::$_channel = self::$_connection->channel();
        }
    }

    public function __destruct()
    {
        if (self::$_channel) {
            self::$_channel->close();
            self::$_channel = null;
        }
        if (self::$_connection) {
            self::$_connection->close();
            self::$_connection = null;
        }
    }

    public function send($channel, $event_name, $message)
    {
        $data = [
            'event'=> $event_name,
            'data'=> $message,
            'time'=> microtime()
        ];

        self::$_channel->exchange_declare($channel, 'fanout', false, false, false);
        $msg = new AMQPMessage(json_encode($data));
        self::$_channel->basic_publish($msg, $channel);
    }
}
