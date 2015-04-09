<?php

namespace Gini\Module;

class DeBaDe
{
    public static function diagnose()
    {
        $conf = \Gini\Config::get('debade.queues');
        if (empty($conf)) {
            return ['Please check your queues config in debade.yml!'];
        }
        foreach ((array)$conf as $key=>$opts) {
            switch (strtolower($opts['driver'])) {
            case 'courier':
                if (!isset($opts['options']['dsn']) || !isset($opts['options']['queue'])) {
                    return ['Driver "'.$opts['driver'].'" need options.dsn and options.queue in debade.yml!'];
                } 

                // 向courier发送一条ping指令, 以便确定指定的queue是否存在
                // 这个功能需要debade-courier提供支持
                // 暂时关闭
                /*
                $socket = new \ZMQSocket(new \ZMQContext(), \ZMQ::SOCKET_REQ);
                $socket->connect($opts['options']['dsn']);

                $loop = 5;
                $pid = $loop;
                $err = 0;
                while ($pid>=1) {
                    $message = [
                        'queue'=> 'PING',
                        'data'=> [
                            'queue'=> $opts['options']['queue'],
                            'id'=> $pid
                        ]
                    ];
                    $socket->send(json_encode($message));
                    $rpid = $socket->recv();
                    if ($rpid!=$pid) {
                        $err++;
                    }
                    $pid--;
                    sleep(1);
                }
                $socket->setSockOpt(\ZMQ::SOCKOPT_LINGER, 1000);
                $socket->disconnect($opts['options']['dsn']);

                if ($err==$loop) {
                    return ['Ping "'.$opts['options']['dsn'] . '|' .  $opts['options']['queue'].'" timeout!'];
                }
                 */
                break;
            case 'rabbit':
                break;
            default:
                return ['Driver "'.$opts['driver'].'" is not supported in debade.yml!'];
            }
        }
    }
}
