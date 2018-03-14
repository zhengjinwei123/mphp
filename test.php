<?php

/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/30
 * Time: 9:48
 */
use  MPHP\Utils\File;
use  MPHP\Utils\Redis;
use  MPHP\Utils\Mysql;
use  MPHP\Utils\Log;

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
            $files = File\FileUtil::getFiles($v);
            foreach ($files as $key => $value) {
                if(File\FileUtil::get_extension($value) == "php"){
                    include_once "$value";
                }
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
        $files = File\FileUtil::getFiles(dirname(__FILE__));

//        foreach ($files as $k => $v) {
//            if (File\FileUtil::getInstance()->isFile($v)) {
//                var_dump("1");
//            } else {
//                var_dump("2");
//            }
//        }

//        File\FileUtil::getInstance()->remove("C:/h.html");
//        File\FileUtil::getInstance()->write("d:/h.html","<html>hehe</html>");
        echo File\FileUtil::read("d:/h.html");
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

    public function testLog(){
        Log\LogUtil::set();
        Log\LogUtil::debug("zhengjinwei is handsome");
    }

    public function testCsv(){
        File\Csv\CsvUtil::writeCache("./a.csv");
    }
    public function testJson(){
        File\Json\JsonUtil::writeCache("./b.json");
    }
}

//Tester::getInstance()->testFile();
//
//Tester::getInstance()->testRedis();
//Tester::getInstance()->testMysql();
//
//Tester::getInstance()->testLog();
//Tester::getInstance()->testCsv();
Tester::getInstance()->testJson();