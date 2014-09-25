<?php
/**
* @file Agent.php
* @brief gini debade agent ****
* @author Hongjie Zhu
* @version 0.1.0
* @date 2014-09-24
 */

namespace Gini\Controller\CLI\Debade;

class Agent extends \Gini\Controller\CLI\Debade
{
    public function __index($params)
    {
        $this->actionHelp($params);
    }

    public function actionRegister($params)
    {
        $prompt = [];
        if (!isset($params[0])) {
            $prompt = [
                'channel'=> [
                    'title'=> 'Channel'
                ]
            ];
        }
        $prompt = array_merge($prompt, [
            'callback'=> [
                'title'=> 'Callback Info',
                'example'=> '{}'
            ],
            'server'=> [
                'title'=> 'Server Name'
            ],
            'token'=> [
                'title'=> 'Token'
            ]
        ]);
        $data = $this->getData($prompt);
        foreach ($prompt as $k=>$v) {
            if (!isset($data[$k])) {
                return $this->showError('Error: Please input info for <' . $v['title'] . '>');
            }
        }
        $server = $data['server'];
        $channel = $data['channel'];
        $token = $data['token'];
        $callback = $data['callback'];
    }

    public function actionHelp($params)
    {
        $this->show('gini debade agent register');
        $this->show('gini debade agent unregister');
        $this->show('gini debade agent list');
    }
}
