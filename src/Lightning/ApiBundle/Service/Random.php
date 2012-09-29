<?php

namespace Lightning\ApiBundle\Service;

use JMS\DiExtraBundle\Annotation\Service;

/**
 * @Service
 */
class Random
{
    const CHARSET = '23456789ABCDEFGHJKLMNPRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    const CHALLENGE_MIN = 1111;

    const CHALLENGE_MAX = 9999;

    public function code($length = 8, $seed = null)
    {
        $code = '';
        $charset = self::CHARSET;
        $count = strlen($charset);
        while ($length--) {
            if ($seed) {
                mt_srand($seed + $length);
            }
            $code .= $charset[mt_rand(0, $count-1)];
        }
        return $code;
    }

    public function challenge($seed = null)
    {
        if ($seed) {
            mt_srand($seed);
        }
        $challenge = mt_rand(self::CHALLENGE_MIN, self::CHALLENGE_MAX);
        return $challenge;
    }

    /**
     * @codeCoverageIgnore
     */
    public function secret()
    {
        return md5(uniqid(null, true));
    }
}
