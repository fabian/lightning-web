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

    /**
     * @param integer|null $seed
     */
    public function __construct($seed = null)
    {
        if ($seed) {
            mt_srand($seed);
        }
    }

    /**
     * Returns a string with random numbers and characters (lower and upper case).
     *
     * @param integer $length
     *
     * @return string
     */
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

    /**
     * Returns a random number between 1111 and 9999.
     *
     * @return integer
     */
    public function challenge()
    {
        $challenge = mt_rand(self::CHALLENGE_MIN, self::CHALLENGE_MAX);

        return $challenge;
    }

    /**
     * Returns a random MD5 hash.
     *
     * @return string
     */
    public function secret()
    {
        return md5(mt_rand());
    }
}
