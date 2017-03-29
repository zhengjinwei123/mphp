<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/29
 * Time: 13:51
 */

namespace Utils\Redis;

use Base\DB\RedisDb;

class RedisUtil
{
    private static $_instance = null;

    private function __construct()
    {

    }

    private function __destruct()
    {

    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
