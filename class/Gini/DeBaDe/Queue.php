<?php

namespace Gini\DeBaDe;

class Queue
{
    private $_name;
    private $_h;

    private static $_QUEUES = [];

    public function __construct($name)
    {
        $this->_name = $name;
        $conf = \Gini\Config::get('debade.queues')[$name];
        if ($conf) {
            $class_name = '\Gini\Debade\Queue\\'.$conf['driver'];
            class_exists($class_name)
                and $this->_h = \Gini\IoC::construct($class_name, $name, $conf['options']);
        }
    }

    // \Gini\DeBaDe\Queue::of($name)->push($message)
    public static function of($name)
    {
        if (!isset(self::$_QUEUES[$name])) {
            self::$_QUEUES[$name] = \Gini\IoC::construct('\Gini\DeBaDe\Queue', $name);
        }

        return self::$_QUEUES[$name];
    }

    public function push($message = null, $routing_key = '')
    {
        if ($this->_h instanceof Queue\Driver) {
            $this->_h->push($message, $routing_key);
        } else {
            \Gini\Logger::of('debade')->debug('[{name}] pushing message to nowhere: {message}{routing}', [
                'name' => $this->_name, 'message' => J($message), 'routing' => " R($routing_key)" ]);
        }

        return $this;
    }
}
