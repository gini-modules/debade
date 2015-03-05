<?php

namespace Gini\DeBaDe\Queue;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Rabbit implements Driver
{
    private $_name;
    private $_connection;
    private $_channel;
    private $_exchange;
    // private $_queue;

    public function log($level, $message, array $context = [])
    {
        $context['@name'] = $this->_name;
        \Gini\Logger::of('debade')->{$level}('0MQ[{@name}] '.$message, $context);
    }

    public function __construct($name, array $options = [])
    {
        try {
            $this->_name = $name;

            $this->_connection = new AMQPConnection(
                $options['host'] ?: '127.0.0.1',
                $options['port'] ?: 5672,
                $options['username'],
                $options['password'],
                $options['vhost'] ?: '/'
            );
            if (!$this->_connection) {
                return;
            }

            $ch = $this->_channel = $this->_connection->channel();
            if (!$ch) {
                return;
            }

            $exopt = (array) $options['exchange'];
            $this->_exchange = $exopt['name'] ?: 'default';
            $ch->exchange_declare(
                $this->_exchange,
                $exopt['type'] ?: 'fanout',
                $exopt['passive'] ?: false,
                $exopt['durable'] ?: false,
                $exopt['auto_delete'] ?: true);

            $this->log('debug', 'declared an exchange: {exchange}', ['exchange' => $exopt['name']]);

            // $qopt = (array) $options['queue'];
            // $ch->queue_declare(
            //     $qopt['name'],
            //     $qopt['passive'] ?: false,
            //     $qopt['durable'] ?: false,
            //     $qopt['exclusive'] ?: false,
            //     $qopt['auto_delete'] ?: false );

            // $ch->queue_bind($this->_queue, $this->_exchange);
        } catch (\Exception $e) {
            // DO NOTHING
            $this->log('error', 'error: '.$e->getMessage());
        }
    }

    public function push($rmsg, $routing_key = null)
    {
        $ch = $this->_channel;
        if (!$ch) {
            return;
        }

        $msg = new AMQPMessage(J($rmsg), ['content_type' => 'text/plain']);

        $this->log('debug', 'pushing message: {message}{routing}', ['message' => J($rmsg), 'routing' => $routing_key ? " R($routing_key)" : '' ]);
        // $mandatory = false,
        // $immediate = false,
        // $ticket = null
        $ch->basic_publish($msg, $this->_exchange, $routing_key, false, false, null);
    }

    public function __destruct()
    {
        if ($this->_channel) {
            $this->_channel->close();
        }
        if ($this->_connection) {
            $this->_connection->close();
        }
    }
}
