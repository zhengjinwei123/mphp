<?php
/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/3/30
 * Time: 12:41
 */

namespace MPHP\Utils\Log;
use MPHP\Utils\File;

class LogUtil
{
    private static $fp = null;
    private static $daemonize = false;
    private static $logDir = null;
    private static $fpDate = null;
    public static $isBuffer = false;

    private static $buffer = array();
    private static $logTag = array(
        "debug" => true,
        "error" => true,
        "warn" => true,
        "info" => true,
        "sql" => true
    );

    private static $colorTag = array(
        "debug_pre" => "\e[1;32m",
        "debug_suf" => "\e[0m",
        "error_pre" => "\e[1;31m",
        "error_suf" => "\e[0m",
        "warn_pre" => "\e[1;33m",
        "warn_suf" => "\e[0m",
        "info_pre" => "\e[1;37m",
        "info_suf" => "\e[0m",
        "sql_pre" => "\e[1;31m",
        "sql_suf" => "\e[0m",
    );

    public static function set($logTag = array(), $daemonize = false, $logDir = null)
    {
        if (!empty($logTag)) {
            $tmp = self::$logTag;
            foreach ($tmp as $k => $v) {
                if (isset($logTag[$k]) && is_bool($v)) {
                    self::$logTag[$k] = $logTag[$k];
                }
            }
        }

        self::$daemonize = $daemonize;
        self::$logDir = $logDir;

        if (!File\FileUtil::isDirectory(self::$logDir)) {
            self::$logDir = dirname(__FILE__);
        }
    }

    private static function logger($msg, $type = null)
    {
        //参数处理
        $type = $type ? $type : 'debug';
        $msg = self::_formatMsg($msg, $type);
        if (empty($msg)) {
            return false;
        }

        if (self::$isBuffer) {
            self::$buffer[] = $msg;
        } else {
            self::write($msg);
        }

        return true;
    }

    private static function _formatMsg($msg, $type)
    {
        if (empty($msg) || empty($type)) {
            return false;
        }

        //参数处理
        if (!is_string($msg)) {
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
        $msg = date('Y-m-d H:i:s') . "\t" . self::logPre() . " [{$type}] {$msg}" . PHP_EOL;

        //屏幕输出
        if (self::$daemonize == false) {
            $pre = isset(self::$colorTag[$type . '_pre']) ? self::$colorTag[$type . '_pre'] : '';
            $suf = isset(self::$colorTag[$type . '_suf']) ? self::$colorTag[$type . '_suf'] : '';

            echo $pre . $msg . $suf;
        }

        return $msg;
    }

    public static function write($msg = '')
    {
        if (self::$isBuffer) {
            $msg = implode('', self::$buffer);
            self::$buffer = array();
        }

        if (empty($msg)) {
            return false;
        }

        $date = date('Y-m-d');
        //按天切割
        if (self::$fpDate != $date) {
            if (self::$fp) {
                fclose(self::$fp);
            }

            list($y, $m, $d) = explode('-', $date);
            $dir = self::$logDir . "/" . $y . $m;
            $filename = self::logfile() . "-{$d}.log";
            $file = "{$dir}/$filename";

            if (!file_exists($dir)) {
                mkdir($dir, 0777);
            }

            self::$fp = fopen($file, 'a');
            self::$fpDate = $date;
        }

        fwrite(self::$fp, $msg);
        unset($msg);

        return true;
    }

    public static function logPre()
    {
        return "";
    }

    public static function logfile()
    {
        return "jade";
    }

    public static function debug($msg)
    {
        if (self::$logTag['debug']) {
            self::logger($msg, 'debug');
        }
    }

    public static function error($msg)
    {
        if (self::$logTag['error']) {
            self::logger($msg, 'error');
        }
    }

    public static function warn($msg)
    {
        if (self::$logTag['warn']) {
            self::logger($msg, 'warn');
        }
    }

    public static function info($msg)
    {
        if (self::$logTag['info']) {
            self::logger($msg, 'info');
        }
    }

    public static function sql($msg)
    {
        if (self::$logTag['sql']) {
            self::logger($msg, 'sql');
        }
    }

    public static function gm($msg)
    {
        self::_write($msg, 'gm');
    }

    public static function log($msg)
    {
        self::_write($msg, 'log');
    }

    private static function _write($msg = '', $type = '')
    {
        $msg = self::_formatMsg($msg, $type);
        if (empty($msg)) {
            return false;
        }

        $dir = self::$logDir;
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }

        $file = "{$dir}/{$type}.log";
        $fp = fopen($file, 'a');
        fwrite($fp, $msg);
        fclose($fp);
    }
}