<?php

namespace Gini\Debade;

abstract class Driver
{
    abstract public function send($channel, $event_name, $message);
}
