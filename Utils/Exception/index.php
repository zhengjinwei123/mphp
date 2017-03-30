<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/30
 * Time: 17:00
 */

namespace MPHP\Utils\Exception;

class ExceptionUtil
{
    private static $_instance = null;

    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    final public static function autoLoader(){

    }
}