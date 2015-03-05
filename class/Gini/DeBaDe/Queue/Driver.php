<?php

namespace Gini\DeBaDe\Queue;

interface Driver
{
    public function __construct($name, array $options);
    public function push($message, $routing_key);
}
