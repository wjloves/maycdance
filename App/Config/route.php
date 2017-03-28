<?php
if(!defined('BASEDIR')){
    exit('File not found');
}
//前台登录页面
$app->addRoute(['GET','POST'],'/login',['as'=>'front_login','uses'=>'App\Controller\Front\LoginController@login']);
//获取验证码
$app->get('/captcha', ['as'=>'front_captcha', 'uses'=>'App\Controller\Front\BaseController@captcha']);
//用户登录后页面
$app->routeDecorator(['decorator' => ['App\Controller\Front\BaseController@notLogin']], function () use ($app) {
    $app->get('/index' , ['as'=> 'front_index' , 'uses'=>'App\Controller\Front\IndexController@index']);
    $app->get('/member',['as'=>'member_index','uses'=>'App\Controller\Front\MemberController@index']);
    $app->addRoute(['GET','POST'],'/upload',['as'=>'upload_img','uses'=>'App\Controller\Front\MemberController@avatarUpload']);
});

//忘记密码，找回密码
$app->addRoute(['GET','POST'],'/forgetpwd', ['as' => 'forgetpwd_index', 'uses' => 'App\Controller\Front\ForgetPasswordController@index']);
$app->addRoute(['GET','POST'],'/forgetpwd/validate', ['as' => 'forgetpwd_validate', 'uses' => 'App\Controller\Front\ForgetPasswordController@getValidate']);
$app->addRoute(['GET','POST'],'/forgetpwd/legalize', ['as' => 'forgetpwd_legalize', 'uses' => 'App\Controller\Front\ForgetPasswordController@safetyLegalize']);
$app->post('/forgetpwd/updatepwd', ['as' => 'forgetpwd_updatepwd', 'uses' => 'App\Controller\Front\ForgetPasswordController@updatePassword']);
$app->get('/forgetpwd/mobilevalidate', ['as' => 'forgetpwd_mobile_validate', 'uses' => 'App\Controller\Front\ForgetPasswordController@updatePassword']);
$app->get('/forgetpwd/emailvalidate', ['as' => 'forgetpwd_email_validate', 'uses' => 'App\Controller\Front\ForgetPasswordController@updatePassword']);


//  用户中心API 访问地址
$app->addRoute(['GET','POST'],'/api/route' , ['as'=> 'api_route' , 'uses'=>'App\Controller\Front\ApiController@pipeLine']);



//后台登录、登出页面
$app->addRoute(['GET','POST'],'/admin/login' , ['as'=> 'adminlogin' , 'uses'=>'App\Controller\Admin\LoginController@login']);
$app->get('/admin/loginOut' , ['as'=> 'admin_loginout' , 'uses'=>'App\Controller\Admin\LoginController@loginOut']);
//平台列表、添加、编辑、删除、生成秘钥
//$app->get('/admin' , ['as'=> 'admin_index' , 'uses'=>'App\Controller\Admin\AdminController@index']);
$app->routeDecorator(array('decorator'=>['App\Controller\Admin\BaseController@checkLogin']),function() use($app){
    $app->get('/admin' , ['as'=> 'admin_index' , 'uses'=>'App\Controller\Admin\AdminController@index']);
    $app->get('/admin/app' , ['as'=> 'app_index' , 'uses'=>'App\Controller\Admin\AppController@index']);
    $app->addRoute(['GET','POST'],'/app/addApp' , ['as'=> 'app_add' , 'uses'=>'App\Controller\Admin\AppController@addApp']);
    $app->addRoute(['GET','POST'],'/app/editApp[/{id:\d+}]' , ['as'=> 'app_edit' , 'uses'=>'App\Controller\Admin\AppController@editApp']);
    $app->get('/app/Sceurity' , ['as'=> 'app_Sceurity' , 'uses'=>'App\Controller\Admin\AppController@ajaxSceurity']);
    $app->post('/app/delApp' , ['as'=> 'app_Sceurity' , 'uses'=>'App\Controller\Admin\AppController@ajaxDelApp']);
});

$app->get('/msg' , ['as'=> 'msg' , 'uses'=>'App\Controller\Front\IndexController@showMessage']);
