<?php

namespace Gini\DeBaDe\Queue;

class Courier implements Driver
{
    private $_name;
    private $_dsn;
    private $_sock;
    private $_queue;

    public function log($level, $message, array $context = [])
    {
        $context['@name'] = $this->_name;
        \Gini\Logger::of('debade')->{$level}('Courier[{@name}] '.$message, $context);
    }

    public function __construct($name, array $options = [])
    {
        try {
            $this->_name = $name;
            $this->_dsn = $options['dsn'];

            $sock = new \ZMQSocket(new \ZMQContext(), \ZMQ::SOCKET_PUSH);
            // by pihizi
            // 升级到php7之后，debade消息发现多节点出现cli假死的状态
            // 排查了代码，唯一可能导致问题的就是zmq的socket
            // 增加这个配置，尝试解决问题
            $sock->setSockOpt(\ZMQ::SOCKOPT_SNDTIMEO, 2000);
            $sock->connect($this->_dsn);

            $this->_sock = $sock;
            $this->_queue = $options['queue'];
        } catch (\Exception $e) {
            // DO NOTHING
            $this->log('error', 'error: {error}', ['error' => $e->getMessage()]);
        }
    }

    public function push($rmsg, $routing_key = null)
    {
        if (!$this->_sock) {
            return;
        }

        $msg = [
            'queue' => $this->_queue,
            'data' => $rmsg,
        ];

        if ($routing_key) {
            $msg['routing'] = $routing_key;
        }

        $this->_sock->send(J($msg), \ZMQ::MODE_DONTWAIT);

        $this->log('debug', 'pushing message: {message}{routing}', ['message' => J($rmsg), 'routing' => $routing_key ? " R($routing_key)" : '']);
    }

    public function __destruct()
    {
        if ($this->_sock) {
            // wait only 1000ms if disconnected
            $this->_sock->setSockOpt(\ZMQ::SOCKOPT_LINGER, 1000);
            $this->_sock->disconnect($this->_dsn);
        }
    }
}
