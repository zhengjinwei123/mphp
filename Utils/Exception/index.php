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
    private static $_backtrace = false;

    private function __construct()
    {
        \spl_autoload_register(__CLASS__ . '::autoLoader');
        \register_shutdown_function(__CLASS__ . '::fataHandler');
        \set_exception_handler(__CLASS__ . '::exceptionHandler');
        \set_error_handler(__CLASS__ . '::errorHandler');
    }

    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    //自动加载
    final public static function autoLoader()
    {

    }

    //致命错误
    final public static function fataHandler()
    {
        $error = \error_get_last();
        if (empty($error)) {
            return null;
        }
        if (!in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))) {
            return null;
        }

        $str = Formater::fatal($error, self::$_backtrace);
    }

    //普通错误
    final public static function errorHandler($error, $message, $file, $line)
    {
        switch ($error) {
            case E_USER_ERROR:
            case E_ERROR:
                break;
            case E_USER_WARNING:
            case E_WARNING:
                break;
            case E_USER_NOTICE:
            case E_NOTICE:
                break;
            default:
                break;
        }

        Formater::error($error, $message, $file, $line, self::$_backtrace);
    }

    //异常
    final public static function exceptionHandler($exception)
    {
        $str = Formater::exception($exception, self::$_backtrace);
    }
}