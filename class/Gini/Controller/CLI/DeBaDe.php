<?php

namespace Gini\Controller\CLI;

class DeBaDe extends \Gini\Controller\CLI
{
    public function actionHello($args)
    {
        count($args)>0 or die("Usage: gini debade hello <queue>\n");
        \Gini\DeBaDe\Queue::of($args[0])->push(["hello" => "world"]);
    }
}
