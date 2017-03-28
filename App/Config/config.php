<?php
if(!defined('BASEDIR')){
    exit('File not found');
}
define("FROMAPP",'bbs');//定义数据的来源

$config = array("debug"=>true);
//$config = array("debug"=>false);


//配置服务假名和其对应的服务类
//注册服务到框架中，全局都可以调用 调用时候 $container->make(alias)
//当value值为字符串时，    alias => abstract
//可以设定一个抽象类 ，然后在provider中根据条件进行初始化
// 后一个参数为回调,为字符串，调用对应的service中的method
$config['service'] = array(
    'taskServer'=>'App\Service\Task\TaskService',
    'oauthService'=>'App\Service\OAuth\OAuthService',
    'jwtService' => 'App\Service\OAuth\Encryption\Jwt',
    'recordService' => 'App\Service\Base\RecordService',
    'resource' => 'App\Service\Base\Resource',
    'resourceOwner' => 'App\Service\Base\ResourceOwner',
    'dataDriver'=>'App\Service\DataDriver\DataDriver',
    'appResourceStrategy' => 'App\Service\AppResourceStrategy\AppResourceStrategy',
    'client' => 'App\Service\Base\Client',
    'platform'=>'App\Service\Base\PlatformServer',
    'mail'=>'App\Service\Helper\PHPMailer'
);
$config['providers'] = array(
    'resourceOwner' => 'App\Service\ServiceProvider\ResourceOwnerProvider',
    'dataDriver'=>'App\Service\ServiceProvider\DataDriverProvider',
    'appResourceStrategy'=>'App\Service\ServiceProvider\AppResourceStrategyProvider',
    'client'=>'App\Service\ServiceProvider\ClientProvider',
    'mail'=>'App\Service\ServiceProvider\PHPMailerProvider',
);


$config['resourceDriver'] = array(
    'phpwind' => 'App\Service\DataDriver\PhpwindDriver'
);
//设置不同平台对应的平台资源的策略类
$config['app_resource'] = array(
    'APP' => 'App\Service\AppResourceStrategy\AppStrategy',
    'AP' => 'App\Service\AppResourceStrategy\ApStrategy',
//    'OAUTH' => 'App\Service\AppResourceStrategy\ApStrategy',
);
$config['accessRecord'] = array(
    'listKey'=>'accessRecord',//异步记录在redis中的list对应的key
    'oauth' => 1,//用户授权接口定义
    'accessToken' => 2,//获取token接口定义
    'refreshToken' => 3,//刷新token接口定义
    'resourceGet'=>4,//获取资源resource接口定义
    'getResourceSkipAuth' =>5, //不经过oauth2.0进行数据获取
);

$config['mail'] = array(
    'charset' => 'UTF-8',
    'isSMTP' => 1,
    'SMTPAuth' => true,
    'SMTPSecure' => 'ssl',
    'host'=> 'smtp.163.com',
    'port' => 465,
    'username' => 'lian3204321@163.com',
    'password' => 'anli3204321',
    'senderAddress' => 'lian3204321@163.com',
    'senderName' => 'xman'

);
$GLOBALS['config']['mongoDb'] = array(
    'host'=>'10.72.3.29:27017'
);
// 短信配置
$GLOBALS['config']['sms'] = Array(
    'sp_url' => 'http://web.cr6868.com/asmx/smsservice.aspx', // 开启调试
    'sp_username' => '13236659442',
    'sp_userpwd' => '7F67EFA6A7012A42AD67399BB693',
    'sp_sign' => '杏娱',
);

return $config;
