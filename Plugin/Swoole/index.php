<?php

// 设置默认时区
date_default_timezone_set('Asia/Shanghai');
define('SERVER_TIME_ZONE', 8);

// 设置错误报告模式
error_reporting(E_ALL);

// 检查mysqli
if (!extension_loaded('mysqli')) {
    exit("Mysqli extension not loaded" . PHP_EOL);
}

// 检查exec 函数是否启用
if (!function_exists('exec')) {
    exit('exec function is disable' . PHP_EOL);
}

// 检查lsof 命令是否存在
exec('whereis lsof', $out);
if ($out[0] == 'lsof:') {
    exit('lsof is not found' . PHP_EOL);
}

// 定义项目常量
defined('SWOOLE_ROOT_PATH') || define('SWOOLE_ROOT_PATH', __DIR__);
defined('DS') || define("DS", DIRECTORY_SEPARATOR);
define('SWOOLE_NAME_PRE', 'sw');
define('SWOOLE_PID_PATH', SWOOLE_ROOT_PATH . DS . 'tmp' . DS . 'swoole.pid');

$setting = include SWOOLE_ROOT_PATH . DS . "config/setting.php";
if (!isset($setting['mode'])) {
    exit("config error,setting required mode");
}


// 服务器模式
$validModes = array(
    'websocket',
    'tcp',
    'udp',
    'http'
);

if (!in_array(strtolower($setting['mode']), $validModes)) {
    exit("invalid service mode");
}

// 引用服务器文件,所有模式的服务器定义接口都必须保持一致
include SWOOLE_ROOT_PATH . DS . "mode" . DS . $setting['mode'] . DS . "server.php";

// 开启服务器
function ServerStart()
{

}

// 关闭
function ServerClose()
{

}

// 服务器列表
function ServerList()
{

}

// 服务器状态
function ServerStatus()
{

}

// cmd
//--------------------------------------------------
$cmds = array(
    'start',
    'stop',
    'restart',
    'status',
    'list'
);
$shortOpts = "dDh:p:n:c:";
$longOpts = [
    'help',
    'daemon',
    'nondaemon',
    'host:',
    'port:',
    'name:',
    'config:'
];

$opts = getopt($shortOpts, $longOpts);

// 帮助,使用定界符显示
if (isset($opts['help'])) {
    echo <<< HELP
用法: php index.php
选项: [start|stop|restart|status|list]
如果不指定参数,使用配置,请参考config/swoole.ini文件

参数说明:
    --help   显示帮助文档
    -d (--daemon)   以守护进程模式运行
    -D (--nondamon) 以非守护进程模式运行
    -h (--host)     指定监听的ip地址 例如: php index.php -h 127.0.0.1
    -p (--port)     指定监听端口    例如:  php index.php -p 8001
    -n (--name)     指定服务进程名  例如: php index.php  -n test
    -c (--config)   指定应用配置文件 例如: php index.php -c zjw

服务维护
    启动   php index.php start
    关闭   php index.php -p 8001 stop
    重启   php index.php -p 8001 restart
    状态   php index.php -p 8001 status
    列表   php index.php -p 8001 list
HELP;
    exit;
}

//参数检测
foreach ($opts as $k => $v) {
    if($k == 'h' || $k == 'host'){
        if(empty($v)){
            exit("参数 -h --host 必须指定值\n");
        }
    }
    if($k == 'p' || $k == 'port'){
        if(empty($v)){
            exit("参数 -p --port 必须指定值\n");
        }
    }
    if($k == 'n' || $k == 'name'){
        if(empty($v)){
            exit("参数 -n --name 必须指定值\n");
        }
    }
    if($k == 'c' || $k == 'config'){
        if(empty($v)){
            exit("参数 -c --config 必须指定值");
        }
    }
}

// 检查命令
$cmd = $argv[$argc-1];
if(!in_array($cmd,$cmds)){
    exit("输入的命令有误:{$cmd},请查看帮助文档 php index.php --help\n");
}

//监听ip地址
$host = '';
if(!empty($opts['h'])){
    $host = $opts['h'];
    if(!filter_var($host,FILTER_VALIDATE_IP)){
        exit("输入host有误:{$host}");
    }
}

if(!empty($opts['host'])){
    $host = $opts['host'];
    if(!filter_var($host,FILTER_VALIDATE_IP)){
        exit("输入host有误:{$host}");
    }
}
//监听port




