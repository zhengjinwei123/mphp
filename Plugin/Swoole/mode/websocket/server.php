<?php

/**
 * Created by PhpStorm.
 * User: zhengjinwei
 * Date: 2017/7/6
 * Time: 13:39
 * swoole 实现websocket server
 */
class SwooleServer
{
    private $server = null;
    private $app = null;
    private $setting = [];

    public function __construct($host = '0:0:0:0:0:0:0:0', $port = 8001)
    {
        $this->__init();

        $configPath = SWOOLE_ROOT_PATH . DS . 'config';
        $configFilePath = $configPath . DS . 'swoole.ini';

        if (!file_exists($configFilePath)) {
            $cfg = include SWOOLE_ROOT_PATH . DS . "config" . DS . "setting.php";
            $setting = $cfg['setting'];
            // 生成ini配置文件
            $iniSettings = '[ws]' . PHP_EOL;
            foreach ($setting as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                $iniSettings .= "{$key}=$value" . PHP_EOL;
            }

            file_put_contents($configFilePath, $iniSettings);
        }

        $tmpPath = SWOOLE_ROOT_PATH . DS . 'tmp';

        //首次启动创建临时目录存放日志信息
        if (!file_exists($tmpPath)) {
            mkdir($tmpPath, 0777);
            mkdir($tmpPath . DS . 'log', 0777);
            mkdir($tmpPath . DS . 'task', 0777);
        }

        //加载配置内容
        if (!is_file($configFilePath)) {
            throw new ErrorException("swoole config file:{$configFilePath} exists");
        }

        $ini = parse_ini_file($configFilePath, true);

        // 数组参数特殊处理
        if (isset($ini['ws']['cpu_affinity_ignore'])) {
            $ini['ws']['cpu_affinity_ignore'] = json_decode($ini['ws']['cpu_affinity_ignore'], true);
        }

        $this->setting = $ini['ws'];
    }

    private function __init()
    {
    }

    public function run($host = '', $port = '', $daemon = -1, $processName = '', $cnf = null, $baseDir = '')
    {
        if ($host) {
            $this->setting['host'] = $host;
        }
        if ($port) {
            $this->setting['port'] = intval($port);
        }
        if ($daemon >= 0) {
            $this->setting['daemonize'] = $daemon;
        }
        if ($processName) {
            $this->setting['process_name'] = SWOOLE_NAME_PRE . '-' . $processName;
        }
        if ($cnf) {
            $this->setting['cnf'] = $cnf;
        }
        if ($baseDir && file_exists(SWOOLE_ROOT_PATH . DS . $baseDir)) {
            $this->setting['chroot'] = SWOOLE_ROOT_PATH . DS . $baseDir;
        }

        $this->server = new swoole_websocket_server($this->setting['host'], $this->setting['port'], SWOOLE_PROCESS, SWOOLE_TCP6);

        //设置启动参数
        $this->server->set($this->setting);

        $callFunctions = [
            'Start',
            'Shutdown',
            'ManagerStart',
            'WorkerStart',
            'WorkerStop',
            'Close',
            'Task',
            'Finish',
            'Open',
            'Message',
            'Request',
            'PipeMessage'
        ];

        //绑定回调函数
        foreach ($callFunctions as $key => $value) {
            $method = "on" . ucfirst($value);
            if (method_exists($this, $method)) {
                $this->server->on($value, [$this, $method]);
            }
        }

        $this->loadFrameWork();
        $this->loadApp();
        $this->server->start();
    }

    // 装载应用框架，将所有项目文件include 到内存
    public function loadFrameWork()
    {

    }

    // 加载应用 启动应用
    public function loadApp()
    {

    }

    public function setProcessName($name)
    {
        if (function_exists('cli_set_process_title')) {
            cli_set_process_title($name);
        } else {
            if (function_exists('swoole_set_process_name')) {
                swoole_set_process_name($name);
            } else {
                trigger_error(__METHOD__ . ' failed,require cli_set_process_title or swoole_set_process_name.');
            }
        }
    }

    // swoole server master start
    public function onStart($server)
    {
        //记录进程id,脚本实现自动重启
        $pid = "{$server->master_pid}\n{$server->manager_pid}";
        file_put_contents(SWOOLE_PID_PATH, $pid);
    }

    // swoole server master shutdown
    public function onShutdown()
    {
        unlink(SWOOLE_PID_PATH);

        echo date('Y-m-d H:i:s') . "Swoole server shutdown. \n";
    }

    // manager 进程启动
    public function onManagerStart($server)
    {
        echo date('Y-m-d H:i:s') . "\t{$server->manager_pid}\tManager start. \n";

        $this->setProcessName($server->setting['process_name'] . '-' . $this->setting['port'] . '-man');
    }

    // worker 进程启动
    public function onWorkerStart($server, $workerId)
    {

    }

    // worker 进程关闭
    public function onWorkerStop($server, $workerId)
    {

    }

    // 客户端关闭 数据需要清理
    public function onClose($server, $fd)
    {

    }

    // 任务处理
    public function onTask($server, $taskId, $fromId, $data)
    {

    }

    // 任务结束回调函数
    public function onFinish($server, $taskId, $data)
    {

    }

    // 客户端建立WebSocket连接
    public function onOpen($server, $request)
    {

    }

    // 监听 websocket 消息
    public function onMessage($server, $frame)
    {

    }

    // 监听 HTTP消息
    public function onRequest($request, $response)
    {

    }

    // 当工作进程收到由sendMessage发送的管道消息时会触发onPipeMessage事件
    public function onPipeMessage($server, $workerId, $message)
    {

    }
}