<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/29
 * Time: 13:51
 */

namespace MPHP\Utils\Redis;

use MPHP\Base\DB\RedisDb;

class RedisUtil
{
    private $_redis = null;

    public function __construct($config = array('host' => '127.0.0.1', 'port' => 6379, 'db' => 0, 'auth' => null, 'pconnect' => false))
    {
        $this->_redis = new RedisDb($config);
    }

    public function ping()
    {
        if ($this->_redis) {
            return $this->_redis->ping();
        }
        return false;
    }

    public function redis()
    {
        return $this->_redis;
    }

}
