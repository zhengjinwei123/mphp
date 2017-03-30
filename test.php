<?php

/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/30
 * Time: 9:48
 */

use Utils\File;

class Tester
{
    private static $_instance = null;

    private function __construct()
    {

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
            if(File\FileUtil::getInstance()->isFile($v)){
                var_dump("1");
            }else{
                var_dump("2");
            }
        }
//        var_dump($files);
    }
}

Tester::getInstance()->testFile();