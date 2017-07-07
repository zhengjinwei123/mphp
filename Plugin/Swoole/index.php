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

// 端口绑定的ip信息
function portBind($port)
{
    $ret = [];
    $cmd = "/usr/sbin/lsof -i:{$port} | awk '$1 != \"COMMAND\" {print $1,$2,$9}'";
    exec($cmd, $out);
    if ($out) {
        foreach ($out as $k => $v) {
            $a = explode(' ', $v);
            list($ip, $p) = explode(":", $a[2]);
            $ret[$a[1]] = [
                'cmd' => $a[0],
                'ip' => $ip,
                'port' => $p
            ];
        }
    }
    return $ret;
}

// 开启服务器
function ServerStart($host, $port, $isDaemon, $name, $cnf)
{
    echo "正在启动swoole服务" . PHP_EOL;
    $server = new SwooleServer();

    if (!is_writeable(dirname(SWOOLE_PID_PATH))) {
        exit("swoole.pid文件需要写入权限:" . dirname(SWOOLE_PID_PATH));
    }
    if (file_exists(SWOOLE_PID_PATH)) {
        $pid = explode("\n", file_get_contents(SWOOLE_PID_PATH));
        $cmd = "ps ax | awk '{print $1}' | grep -e \"^{$pid[0]}$\"";
        exec($cmd, $out);
        if (!empty($out)) {
            exit("服务存在,进程id为{$pid[0]}");
        } else {
            echo "警告:swoole.pid文件 " . SWOOLE_PID_PATH . " 存在，可能 swoole-ws 服务上次异常退出(非守护模式ctrl+c终止造成是最大可能)" . PHP_EOL;
            unlink(SWOOLE_PID_PATH);
        }
    }

    $bind = portBind($port);
    if ($bind) {
        foreach ($bind as $k => $v) {
            if ($v['ip'] == "*" || $v['ip'] == $host) {
                exit("端口被占用 {$host}:{$port},进程id为:{$k}" . PHP_EOL);
            }
        }
    }

    $server->run($host, $port, $isDaemon, $name, $cnf);
    $bind = portBind($port);
    if (!empty($bind) && !file_exists(SWOOLE_PID_PATH)) {
        exit("swoole.pid 文件生成失败,请关闭服务后检查原因" . PHP_EOL);
    }
    exit("成功启动服务" . PHP_EOL);
}

// 关闭
function ServerStop($host, $port, $isRestart = false)
{
    echo "正在关闭 swoole 服务" . PHP_EOL;

    if (!file_exists(SWOOLE_PID_PATH)) {
        exit("swoole.pid文件 " . SWOOLE_PID_PATH . " 不存在" . PHP_EOL);
    }
    $pid = explode("\n", file_get_contents(SWOOLE_PID_PATH));
    $bind = portBind($port);
    if (empty($bind) || !isset($bind[$pid[0]])) {
        exit("服务进程不存在" . PHP_EOL);
    }

    $cmd = "kill {$pid[0]}";
    exec($cmd);
    do {
        $out = [];
        $c = "ps ax | awk '{print $1 }' | grep -e \"^{$pid[0]}$\"";
        exec($c, $out);
        if (empty($out)) {
            break;
        }
    } while (true);

    if (file_exists(SWOOLE_PID_PATH)) {
        unlink(SWOOLE_PID_PATH);
    }

    $msg = "执行命令 {$cmd} 成功,端口 {$host}:{$port} 进程结束" . PHP_EOL;
    if ($isRestart) {
        echo $msg;
    } else {
        exit($msg);
    }
}

// 服务器列表
function ServerList()
{
    echo "本机运行的 swoole 服务进程" . PHP_EOL;

    $cmd = "ps -aux | grep " . SWOOLE_NAME_PRE . "| grep -v grep | awk '{print $1,$2,$6,$8,$9,$11}'";
    exec($cmd,$out);
    if(empty($out)){
        exit("没有正在运行的swoole服务进程");
    }

    echo "USER PID RSS(kb) STAT START COMMAND" . PHP_EOL;
    foreach ($out as $v) {
        echo $v . PHP_EOL;
    }
    exit();
}

// 服务器状态
function ServerStatus($host, $port)
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
    if ($k == 'h' || $k == 'host') {
        if (empty($v)) {
            exit("参数 -h --host 必须指定值\n");
        }
    }
    if ($k == 'p' || $k == 'port') {
        if (empty($v)) {
            exit("参数 -p --port 必须指定值\n");
        }
    }
    if ($k == 'n' || $k == 'name') {
        if (empty($v)) {
            exit("参数 -n --name 必须指定值\n");
        }
    }
    if ($k == 'c' || $k == 'config') {
        if (empty($v)) {
            exit("参数 -c --config 必须指定值");
        }
    }
}

// 检查命令
$cmd = $argv[$argc - 1];
if (!in_array($cmd, $cmds)) {
    exit("输入的命令有误:{$cmd},请查看帮助文档 php index.php --help\n");
}

//监听ip地址
$host = '';
if (!empty($opts['h'])) {
    $host = $opts['h'];
    if (!filter_var($host, FILTER_VALIDATE_IP)) {
        exit("输入host有误:{$host}");
    }
}

if (!empty($opts['host'])) {
    $host = $opts['host'];
    if (!filter_var($host, FILTER_VALIDATE_IP)) {
        exit("输入host有误:{$host}");
    }
}
//监听port
$port = 0;
if (!empty($opts['p'])) {
    $port = intval($opts['p']);
    if ($port <= 1024) {
        exit("输入port有误:{$port}");
    }
}
if (!empty($opts['port'])) {
    $port = intval($opts['port']);
    if ($port <= 1024) {
        exit("输入port有误:{$port}");
    }
}

// 服务进程名称
$name = '';
if (!empty($opts['n'])) {
    $name = trim($opts['n']);
}
if (!empty($opts['name'])) {
    $name = trim($opts['name']);
}

// 服务配置
$cnf = '';
if (!empty($opts['c'])) {
    $cnf = trim($opts['c']);
}
if (!empty($opts['config'])) {
    $cnf = trim($opts['config']);
}

//是否守护进程
$isDaemon = -1;
if (isset($opts['D']) || isset($opts['nondaemon'])) {
    $isDaemon = 0;
}
if (isset($opts['d']) || isset($opts['daemon'])) {
    $isDaemon = 1;
}

// 执行命令
if ($cmd == 'start') {
    ServerStart($host, $port, $isDaemon, $name, $cnf);
} else if ($cmd == 'stop') {
    if (empty($port)) {
        exit("停止服务需要指定port" . PHP_EOL);
    }
    ServerStop($host, $port);
} else if ($cmd == 'restart') {
    if (empty($port)) {
        exit("重启服务必须指定port" . PHP_EOL);
    }
    ServerStop($host, $port);
    ServerStart($host, $port, $isDaemon, $name, $cnf);
} else if ($cmd == 'status') {
    if (empty($host)) {
        $host = '127.0.0.1';
    }
    if (empty($port)) {
        exit("查看服务状态必须指定port");
    }
    ServerStatus($host, $port);
} else if ($cmd == 'list') {
    ServerList();
}

