<?php

namespace Gini\Controller\CLI;

class Debade extends \Gini\Controller\CLI
{
    protected function getData($data)
    {
        $result = [];
        foreach ($data as $k => $v) {
            $tmpTitle = $v['title'];
            $tmpEG = $v['example'] ? " (e.g \e[31m{$v['example']}\e[0m)" : '';
            $tmpDefault = $v['default'] ? " default value is \e[31m{$v['default']}\e[0m" : '';
            $tmpData = readline($tmpTitle . $tmpEG . $tmpDefault . ': ');
            if (isset($v['default']) && !$tmpData) {
                $tmpData = $v['default'];
            }
            if (isset($tmpData) && $tmpData!=='') {
                $result[$k] = $tmpData;
            }
        }

        return $result;
    }

    protected function surround($string)
    {
        return "\e[31m" . $string . "\e[0m";
    }

    protected function show($msg)
    {
        echo $msg . "\n";
    }

    protected function showError($msg)
    {
        $this->show($this->surround($msg));
    }
}
