<?php

namespace Lightning\ApiBundle\Service;

use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service
 */
class Random
{
    const CHARSET = '23456789ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    public function code($length = 8)
    {
        $code = '';
        $charset = self::CHARSET;
        $count = strlen($charset);
        while ($length--) {
            $code .= $charset[mt_rand(0, $count-1)];
        }
        return $code;
    }

    public function secret()
    {
        return md5(uniqid(null, true));
    }
}
