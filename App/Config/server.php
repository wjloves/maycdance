<?php
if(!defined('BASEDIR')){
    exit('File not found');
}
$server = array(
    'host' => '127.0.0.1',
    'port' => '9588',
    'setting' => array(
        'reactor_num' => 2, //reactor thread num
        'worker_num' => 1,    //worker process num
        'backlog' => 128,   //listen backlog
        'max_request' => 50,
        'dispatch_mode' => 1,
//        'daemonize' => 0,
        'daemonize' => 1, //后台守护运行

    ),
);
return $server;