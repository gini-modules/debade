<?php

namespace Gini\Controller\CLI;

class DeBaDe extends \Gini\Controller\CLI
{
    public function actionHello($args)
    {
        count($args)>0 or die("Usage: gini debade hello <queue>\n");
        \Gini\DeBaDe\Queue::of($args[0])->push(["hello" => "world"]);
    }

    public function actionResendDatabaseMessage()
    {
        $queues = \Gini\Config::get('debade.queues');
        foreach ($queues as $key=>$queue) {
            if (strtolower($queue['driver'])!='database') continue;
            self::_resend($key);
        }
    }

    private static function _resend($key)
    {
        $queue = \Gini\Debade\Queue::of($key);
        foreach ($queue->getNextNeedResend() as $row) {
            if (!$row->id) continue;
            \Gini\DeBaDe\Queue::of($row->queue)->push([
                'debade::key'=> $row->ymlkey,
                'debade::hash'=> $row->hash
            ]);
        }
    }

    public function actionDatabaseColumnUpdate()
    {
        $key = "---debade-queue-database--temporary---";
        $defaultOPTs = \Gini\Config::get('debade.database');
        \Gini\Config::set("database.$key", [
            'dsn'=> $defaultOPTs['dsn'],
            'username'=> $defaultOPTs['username'],
            'password'=> $defaultOPTs['password']
        ]);
        $db = \Gini\Database::db($key);
        $sql = "ALTER TABLE _debade_queue modify data MEDIUMTEXT NOT NULL DEFAULT ''";
        if ($db->query($sql)) {
            echo 'done.';
        } else {
            echo 'fail.';
        }
    }
}
