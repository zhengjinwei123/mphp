<?php

/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/30
 * Time: 9:48
 */

use Utils\File;
use Utils\Redis;
use Utils\Mysql;

class Tester
{
    private static $_instance = null;

    private function __construct()
    {
        $this->__autoLoad();
    }

    private function __autoLoad()
    {
        $rootPath = dirname(__FILE__);
        $modules = array(
            "Base" => $rootPath . "/Base",
            "Utils" => $rootPath . "/Utils"
        );

        include_once "./Utils/File/index.php";
        foreach ($modules as $k => $v) {
            $files = File\FileUtil::getInstance()->getFiles($v);
            foreach ($files as $key => $value) {
                include_once "$value";
            }
        }
    }

    private function __clone()
    {

    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function testFile()
    {
        include "./Utils/File/index.php";
        $files = File\FileUtil::getInstance()->getFiles(dirname(__FILE__));

        foreach ($files as $k => $v) {
            if (File\FileUtil::getInstance()->isFile($v)) {
                var_dump("1");
            } else {
                var_dump("2");
            }
        }
//        var_dump($files);
    }

    public function testRedis()
    {
        $d = new Redis\RedisUtil(
            array('host' => '127.0.0.1', 'port' => 6379, 'db' => 0, 'auth' => null, 'pconnect' => false)
        );

        if (!$d->ping()) {
            var_dump("connect error");
            return;
        }

        $d->redis()->set("a", "zhengjinwei");
        echo $d->redis()->get("a");
    }

    public function testMysql()
    {
        $d = new Mysql\MysqlUtil(
            array('host' => '127.0.0.1', 'username' => "root", 'password' => "root", 'db' => "lxh_reportdb", 'port' => 3306, 'charset' => 'utf8')
        );

        if (!$d->mysql()->ping()) {
            var_dump("connect mysql error");
            return;
        }

        $mysql = $d->mysql();

        $result = $mysql->query("select * from t_postdata limit 100");

        var_dump($result);
    }
}

//Tester::getInstance()->testFile();

//Tester::getInstance()->testRedis();
Tester::getInstance()->testMysql();