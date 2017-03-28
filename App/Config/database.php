<?php
if(!defined('BASEDIR')){
    exit('File not found');
}
//这个是数据库配置固定模板,参见Eloquent的配置
$db = array(
    'fetch'=> PDO::FETCH_CLASS,
    'default' => 'mysql',
    'connections' => array(
        'mysql' => array(
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'u_leajoy',
            'username' => 'root',
            'password' => '',
            'port' => '3306',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => 'u_',
        ),
       
        /*   'mysql' => array(
             'driver' => 'mysql',
             'host' => '127.0.0.1',
             'database' => 'wisdom_db',
             'username' => 'oauth',
             'password' => 'oauthpwd',
             'port' => '3306',
             'charset' => 'utf8',
             'collation' => 'utf8_unicode_ci',
             'prefix' => 'wisdom_',
         ),
       'discuz_db'=>array(
             'driver' => 'mysql',
             'host' => '10.1.10.185',
             'database' => 'discuz_first',
             'username' => 'root',
             'password' => '123456',
             'port' => '3306',
             'charset' => 'utf8',
             'collation' => 'utf8_unicode_ci',
             'prefix' => 'pre_',
         ),*/
     /*   'phpwind_db'=>array(
            'driver' => 'mysql',
            'host' => '173.245.83.234',
            'database' => 's8_bbs_1',
            'username' => 'anni',
            'password' => '123456',
            'port' => '3306',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => 'pw_',
        ),
     */
     ),
    'redis' => array(
        'cluster' => false,
        'options' => array(
            \Redis::OPT_PREFIX => '',
            \Redis::OPT_SERIALIZER=> \Redis::SERIALIZER_PHP,
        ),
        'timeout' => 5,
        'default' => array(
            'host'     => '10.72.5.53',
            'port'     => 6379,
            'database' => 0,
        ),
    ),
);
return $db;
