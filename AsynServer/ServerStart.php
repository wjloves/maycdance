<?php

//var_dump(is_file(__DIR__.'/../App/app.php'));
define('BASEDIR',dirname(__DIR__));
$app = require __DIR__.'/../App/app.php';
$serv = new \AsynServer\AsynServer($app);
$serv ->run();
//var_dump($app -> config['database']);
//$serv = new \swoole_server();
