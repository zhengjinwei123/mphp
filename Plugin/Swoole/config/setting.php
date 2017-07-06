<?php

return array(
    "mode" => "websocket", // 启动模式 websocket | tcp | udp | http
    "setting" => array(
        'host' => "127.0.0.1", //监听ip
        'post' => "8001", //监听端口
        'cnf' => 'prod', //应用配置 dev|test|prod
        'process_name' => SWOOLE_NAME_PRE, //swoole进程名称
        'daemonize' => 0,//是否守护进程 1=>守护进程| 0 => 非守护进程

        'max_connection' => 20000,  //最大允许维持多少个tcp连接
        'timeout' => 3, //select and epoll_wait timeout
        'reactor_num' => 6,   //reactor线程数
        'dispatch_mode' => 2,//数据包分发策略 1=>轮循模式| 2=>固定模式| 3=>抢占模式 | 4=>IP分配| 5=>UID分配
        'backlog' => 128,//Listen队列长度,同时有多少个等待accept的连接

        'worker_num' => 12, // worker进程 cpu 1-4倍
        'max_request' => 10000, //worker进程的最大任务数,超过这个值进程就会重启 0表示不退出

        'task_worker_num' => 4,// task进程数
        'task_ipc_mode' => 1, //task进程与worker进程之间通信的方式 1=>unix socket通信| 2=>消息队列通信| 3=>消息队列通信并为争抢模式
        'task_max_request' => 5000,  //task进程处理请求超过此值则关闭task进程 0表示不退出

        'heartbeat_check_interval' => 60,  //心跳检测频率
        'heartbeat_idle_time' => 600, //连接最大允许空闲的时间

        'open_eof_check' => true,                                    //打开EOF检测
        'open_eof_split' => true,                                    //启用EOF自动分包
        'package_eof' => '\r\n',                                  //EOF字符串,最大8个字节

        'open_cpu_affinity' => true,                                    //启用CPU亲和性设置
        'cpu_affinity_ignore' => [0],                                     //忽略CPU设置

        'open_tcp_nodelay' => true,                                    //关闭Nagle算法,提高HTTP服务器响应速度
        'tcp_defer_accept' => 5,                                       //TCP连接延迟5秒或者有数据发送时才触发accept
        'log_level' => 3,                                       //范围是0-5,低于log_level设置的日志信息不会抛出0:DEBUG;1:TRACE;2:INFO;3:NOTICE;4:WARNING;5:ERROR

        'tmp_dir' => SWOOLE_ROOT_PATH . DS . "/tmp",
        'log_dir' => SWOOLE_ROOT_PATH . DS . "/tmp/log",
        'task_tmpdir' => SWOOLE_ROOT_PATH . DS . "/tmp/task",           //task进程临时数据目录 投递数据超过8192字节将启用
        'log_file' => SWOOLE_ROOT_PATH . DS . "/tmp/log/http.log",   //日志文件目录
    ),
    "database" => array(
        "mysql" => array(
            'host'          =>      '127.0.0.1',
            'username'      =>      'root',
            'password'      =>      '123456',
            'db'            =>      'db_xxx',
            'port'          =>      3306,
            'prefix'        =>      't_',
            'charset'       =>      'utf8',
            'pingtime'      =>      300
        ),
        "redis" => array(
            'host'          =>      '127.0.0.1',
            'port'          =>      6379,
            'db'            =>      1,
            'auth'          =>      null,
            'pconnect'      =>      false,
            'pingtime'      =>      300
        ),
        "memcache" => array(
            'host'          =>      '192.168.2.150',
            'port'          =>      15000,
            'pingtime'      =>      120
        ),
        "mongodb" => array(

        )
    )
);