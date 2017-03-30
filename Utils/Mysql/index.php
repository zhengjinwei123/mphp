<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/29
 * Time: 14:11
 */

namespace MPHP\Utils\Mysql;

use MPHP\Base\DB\MysqliDb;

class MysqlUtil
{
    private $_mysql = null;

    public function __construct($config = array('host' => '127.0.0.1', 'username' => null, 'password' => null, 'db' => null, 'port' => 3306, 'charset' => 'utf8'))
    {
        $this->_mysql = new MysqliDb($config);
    }

    public function mysql()
    {
        return $this->_mysql;
    }
}

