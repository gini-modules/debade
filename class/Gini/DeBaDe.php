<?php

namespace Gini;

class DeBaDe
{
    public static function hash($str, $secret)
    {
        return base64_encode(hash_hmac('sha1',
            $str, $secret, true));
    }
}
