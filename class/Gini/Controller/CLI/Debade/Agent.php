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

    private static function _post($data, $server) {

        $timeout = 5;

        $ch = curl_init($server);

        $data = http_build_query($data);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);
        if ($errno) {
            return curl_error($ch);
        }

    }

    public function __index($params)
    {
        $this->actionHelp($params);
    }

    private function _action($params, $type)
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
            'server'=> [
                'title'=> 'Server Name',
                'example'=> 'http://172.17.42.1:8877/'
            ],
            'callback.type'=> [
                'title'=> 'Callback Type',
                'example'=> 'http-jsonrpc OR rest',
                'default'=> 'http-jsonrpc'
            ],
            'callback.host'=> [
                'title'=> 'Callback Host',
                'example'=> 'gapper.in'
            ],
            'callback.port'=> [
                'title'=> 'Callback Port',
                'default'=> '80'
            ],
            'callback.token'=> [
                'title'=> 'Callback Token',
                'example'=> 'RanDom',
            ]
        ]);
        $data = $this->getData($prompt);
        foreach ($prompt as $k=>$v) {
            if (!isset($data[$k])) {
                return $this->showError('Error: Please input info for <' . $v['title'] . '>');
            }
        }

        $server = $data['server'];
        $postData = [
            'channel'=> $params[0] ?: $data['channel'],
            'callback'=> $data['callback.type'] . ':' . json_encode([
                'host'=> $data['callback.host'],
                'port'=> $data['callback.port'],
                'token'=> $data['callback.token']
            ])
        ];

        $confirm = readline("Continue? (Y/n):");

        if ($confirm!=='Y') {
            exit(0);
        }

        return self::_post($postData, rtrim($server, '/') . '/' . $type);

    }

    public function actionRegister($params)
    {
        $result = $this->_action($params, 'register');
        return $result ? $this->showError($result) : $this->show('Register Done!');
    }

    public function actionUnregister($params)
    {
        $result = $this->_action($params, 'unregister');
        return $result ? $this->showError($result) : $this->show('UnRegister Done!');
    }

    public function actionList()
    {
    }

    public function actionHelp($params)
    {
        $this->show('gini debade agent register');
        $this->show('gini debade agent unregister');
        $this->show('gini debade agent list');
    }
}
