<?php

//用户相关
$config['cache_list']['U_100'] = array('prefix'  => 'mod_user::get_one_key',
    'timeout' => 86400,
    'desc'=>'KEY=user_name|email');//用户信息key
$config['cache_list']['U_101'] = array('prefix'  => 'mod_user::get_one',
    'timeout' => 86400,
    'desc'=>'KEY=UID');//用户信息
$config['cache_list']['U_102'] = array('prefix'  => 'mod_user::get_count',
    'timeout' => 86400,
    'desc'=>'KEY=');//用户计数
$config['cache_list']['U_103'] = array('prefix'  => 'mod_user::get_list',
    'timeout' => 86400,
    'desc'=>'KEY=S');//用户列表
// 用户详细
$config['cache_list']['UD_101'] = array('prefix'  => 'mod_user_detail::get_one',
    'timeout' => 86400,
    'desc'=>'KEY=UID');//用户详细
$config['cache_list']['UD_103'] = array('prefix'  => 'mod_user_detail::get_list',
    'timeout' => 86400,
    'desc'=>'KEY=UID');//用户详细

// 用户积分
$config['cache_list']['US_101'] = array('prefix'  => 'mod_user_score::get_one',
    'timeout' => 86400,
    'desc'=>'KEY=UID');//用户信息
$config['cache_list']['USH_101'] = array('prefix'  => 'mod_user_score_history::get_one',
    'timeout' => 86400,
    'desc'=>'KEY=UID');//用户信息
$config['cache_list']['USH_104'] = array('prefix'  => 'mod_user_score_history::login_err',
    'timeout' => 60*15, // 缓存15分钟
    'desc'=>'KEY=UID登录错误记数');//用户信息

//其它相关
$config['cache_list']['O_101'] = array('prefix'=>'mod_misc::get_setting',
    'timeout'=>1728000,
    'desc' => 'KEY=');  //配置 缓存20天
$config['cache_list']['O_102'] = array('prefix'=>'mod_misc::report',
    'timeout'=>86400,
    'desc' => 'KEY=ip2long(IP)');  //举报
$config['cache_list']['O_103'] = array('prefix'  => 'mod_misc::get_keywords',
    'timeout' => 0,
    'desc' => 'KEY=');  // 可疑关键词

$config['cache_list']['O_303'] = array('prefix'  => 'pub_silver::get_prohibit',
    'timeout' => 0,
    'desc' => '');  //禁止刷积分用户
$config['cache_list']['O_305'] = array('prefix'  => 'lib_private::parse_iplimit',
    'timeout' => 0,
    'desc' => '');  //禁止IP

$config['cache_list']['O_401'] = array('prefix'  => 'mod_misc::get_notice_list',
    'timeout' => 0,
    'desc' => 'KEY=0(公告)|1(帮助)');  //公告|帮助

$config['cache_list']['O_501'] = array('prefix'  => 'lib_validate_mail',
    'timeout' => 600,
    'desc' => 'KEY=time');  //邮箱验证码
$config['cache_list']['O_502'] = array('prefix'  => 'lib_validate_mobile',
    'timeout' => 600,
    'desc' => 'KEY=time');  //手机验证码
$config['cache_list']['O_505'] = array('prefix'  => 'mod_misc::set_forget_password_step',
    'timeout' => 86400,
    'desc' => 'KEY=session_id');  //忘记密码
