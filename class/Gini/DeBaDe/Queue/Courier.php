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
            $sock->connect($this->_dsn);

            $this->_sock = $sock;
            $this->_queue = $options['queue'];
        } catch (\Exception $e) {
            // DO NOTHING
            $this->log('error', 'error: {error}', ['error' => $e->getMessage()]);
        }
    }

    public function push($rmsg)
    {
        if (!$this->_sock) {
            return;
        }

        $msg = [
            'queue' => $this->_queue,
            'data' => $rmsg
        ];

        $this->_sock->send(J($msg), \ZMQ::MODE_DONTWAIT);

        $this->log('debug', 'pushing message: {message}', ['message' => J($rmsg)]);
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
