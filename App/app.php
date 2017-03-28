<?php
if(!defined('BASEDIR')){
    exit('File not found');
}
define("__APP__",__DIR__.'\App');
define("__CONFIG__",__DIR__.'/Config');
define("__CORE__", substr(__DIR__, 0, -4).'/Core');
define("__SERVICE__",__DIR__.'/Service');
define("__STATIC__",__DIR__.'/web/');
define("__UPLOAD__",BASEDIR.'/Upload/');
define("__WEB__",BASEDIR.'/web');


define('PATH_STATIC_LOG', BASEDIR.'/Cache/Logs/');
define('ENCRYPT_KEY', 'H@3%D#@#&%LVC2SOCX80551');
// 上线时把demo.去掉
define('MYAPI_URL_MY', 'http://moonjoy.aucenter.com');               //  用户中心网址
define('MYAPI_DOMAIN', 'moonjoy.aucenter.com');               //  用户中心网址

// 各个项目的私KEY
define('MYAPI_APP_ID', 'm');                                  //  使用的API_ID
define('MYAPI_APP_KEY', 'M67ac8XCb2p1ha7eY5');               //  使用的API_KEY
//-------------------------以下两项请参照注释内容进行修改    -----------------------//
define('MYAPI_SERVER_URL', MYAPI_URL_MY.'/api/user-api.php');         //  服务端网址
define("MYAPI_USE_CURL", true);

// MYAPI COOKIE
define('MYAPI_COOKIE', "LEAJOYA");                                 // COOKIE 名称
define('MYAPI_ONLINE_REPORT',false);                             // 是否向MY汇报在线情况
define('MYAPI_COOKIE_ONLINE',"LEAJOYO");                           // 在线COOKIE标志
define("MYAPI_ONLINE_INTERVAL",900);                            // 在线时间间隔 15分钟,15分钟向my汇报一次在线情况
define('MYAPI_COOKIE_DOMAIN',  ".aucenter.com");              // COOKIE 域名.leajoy.com
define('MYAPI_API_COOKIE_DOMAIN',  ".aucenter.com");              // COOKIE 域名 多个用逗号分开
define('MYAPI_COOKIE_EXPIRE',604800);                         // 自己设定的cookie 14天=1209600 7=604800

// MYAPI签名,加密KEY
define('MYAPI_SIGN_KEY', 'ACS567DADDCGLP82JGY');               // COOKIE签名KEY
define('MYAPI_ENCRYPT_KEY', 'XDzmcx9283azklZCVSDWEl');          // COOKIE加密KEY

// API 缓存设置
define("MYAPI_MD_PREFIX","UAPI");
define("MYAPI_API_LOGIN_USERID_PREFIX","ucenter_other_login_userid");  // 登录用户ID缓存前缀
define("MYAPI_API_LOGOUT_USERID_PREFIX","ucenter_other_logout_userid");  // 登出用户ID缓存前缀

// 加载自动加载类中 composer方式 并注册到系统中
$loader = include BASEDIR.'/vendor/autoload.php';
spl_autoload_register(array($loader,'loadClass'));
// 初始化框架入口 容器
$app = new \Core\Application();

// 注册服务到框架中，全局都可以调用 调用时候 $container->make(service)
//$app->alias('App\Service\Task\TaskService','taskServer');
//$app->alias('App\Service\Message\MessageService','messageServer');
////$app->alias('OAuth2\Server','oauthServer');
//
//
//$app->alias('App\Service\Admin\AdminUser\AdminUserService','adminServer');
// 加载项目所有路由配置

include 'Config/route.php';

return $app;
