<?php
/**
 * Created by PhpStorm.
 * User: ff
 * Date: 2017/3/30
 * Time: 17:24
 */
namespace MPHP\Utils\Exception;

class Formater
{
    /**
     * register_shutdown_function
     * @param mixed $error
     * @param mixed $trace
     * @return mixed
     */
    public static function fatal($error, $trace=false)
    {
        $exceptionHash = array(
            'className' => 'fatal',
            'message' => $error['message'],
            'code' => -1,
            'file' => $error['file'],
            'line' =>$error['line'],
            'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'trace' => array(),
        );

        if ($trace) {
            $traceItems = debug_backtrace();
            $exceptionHash['trace'] = self::parseTrace($traceItems);
        }

        return $exceptionHash;
    }

    /**
     * set_exception_handler
     * @param Exception $exception
     * @param mixed $trace
     * @return mixed
     */
    public static function exception(\Exception $exception, $trace = false)
    {
        $exceptionHash = array(
            'className' => 'Exception',
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'trace' => array(),
        );

        if ($trace) {
            $traceItems = $exception->getTrace();
            $exceptionHash['trace'] = self::parseTrace($traceItems);
        }

        return $exceptionHash;
    }

    /**
     * set_exception_handler
     * @param mixed $error
     * @param mixed $message
     * @param mixed $file
     * @param mixed $line
     * @param mixed $trace
     * @return mixed
     */
    public static function error($error, $message, $file, $line, $trace = false)
    {
        switch($error) {
            case E_USER_ERROR:
                $type = '[USER_ERROR]';
                break;
            case E_USER_WARNING:
                $type = '[USER_WARNING]';
                break;
            case E_USER_NOTICE:
                $type = '[USER_NOTICE]';
                break;
            case E_ERROR:
                $type = '[ERROR]';
                break;
            case E_WARNING:
                $type = '[WARNING]';
                break;
            case E_NOTICE:
                $type = '[NOTICE]';
                break;
            default:
                $type = '['.$error.']';
                break;
        }

        $exceptionHash = array(
            "{$type} {$message} in line {$line} of file {$file}, PHP" .PHP_VERSION." (".PHP_OS.")",
            'trace' => array(),
        );

        if ($trace) {
            $traceItems = debug_backtrace();
            $exceptionHash['trace'] = self::parseTrace($traceItems);
        }

        return $exceptionHash;
    }

    private static function parseTrace($traceItems) {
        $traceArray = array();
        foreach ($traceItems as $traceItem) {
            $traceHash = array(
                'file' => isset($traceItem['file']) ? $traceItem['file'] : 'null',
                'line' => isset($traceItem['line']) ? $traceItem['line'] : 'null',
                'function' => isset($traceItem['function']) ? $traceItem['function'] : 'null',
                'args' => array(),
            );

            if (!empty($traceItem['class'])) {
                $traceHash['class'] = $traceItem['class'];
            }

            if (!empty($traceItem['type'])) {
                $traceHash['type'] = $traceItem['type'];
            }

            if (!empty($traceItem['args'])) {
                foreach ($traceItem['args'] as $argsItem) {
                    $traceHash['args'][] = \json_encode($argsItem);
                }
            }

            $traceArray[] = $traceHash;
        }

        return $traceArray;
    }
}