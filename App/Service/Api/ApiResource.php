<?php
namespace App\Service\Api;

use App\Models\UserDetail;
use App\Models\Information;
use App\Models\UserScoreHistory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
//use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Models\User;
use App\Models\UserScore;
use App\Exception\UserException;
use App\Service\Helper\Helper;
use App\Service\Helper\Log;
use App\Service\Mongodb\MongoDb;
use App\Service\DataDriver\UserDriver;
use App\Service\DataDriver\UserDetailDriver;

header("content-type:text/html;charset=utf-8");
class ApiResource {

    /**
     * @description 调用方法名
     * */
    protected $action;

    /**
     * 登录失败次数
     */
    const LOGIN_ERR_NUM = 8;

    const MONGODB_TABLE_NAME = 'u_user_login_log';
    public static $cache_key_userDetail_one = 'UD_101';
    public static $cache_key_user_one = 'U_101';
    public static $cache_key_userScore = 'US_101';
    public static $cache_key_captcha_mobile = 'O_502';
    public static $cache_key_captcha_email = 'O_501';
    public static $cache_key_filter_ip = 'O_305';
    public static $cache_key_login_fail_num = 'USH_104';

    public static $face_cols = array('reg_app','reg_app_info','reg_ip','reg_time','gender','face', 'user_safe_level', 'pwd_safe_level','email_verify', 'mobile_verify');
    public static $info_cols = array('user_id','user_name','name','email','pet_name','face','location','gender','birthday','mobile','interest','wish','last_login','login_num','reg_app','reg_ip','reg_time');
    public static $login_cols = array('user_id','email','user_name','mobile'); // is_login,new_face

    public function __construct(){}

    public static function InitRoute($action = '') {

        switch ($action) {
            case 'get.userInfos':
                return self::getUserInfos() ;
                break;
            case 'get.userFaces':
                return self::getUsers('user_id');
                break;
            case 'get.userNames':
                return self::getUsers('user_name');
                break;
            case 'get.userLoginNum':
                return self::getUserLogs() ;
                break;
            case "get.searchUser":
                return self::searchUser();
                break;
            case 'checkUser':
                return self::userCheck();
                break;
            case 'checkUserCaptcha':
                return self::userCaptchaCheck();
                break;
            case 'updateGame':
                return  self::gameUserEdit();
                break;
            case 'updatePwd':
                return self::pwdEdit();
                break;
            case 'updateResetPwd':
                return self::pwdEdit();
                break;
            case 'updateScore':
                return self::updateScore();
                break;
            case 'bindMobile':
                return self::mobileBind();
                break;

            case 'bindMail':
                return self::emailBind();
                break;

            case 'registerPassport':   //
                return self::register();  //测试通过
                break;

            case 'p3pLogin':
                return self::p3pLogin(); //测试通过
                break;

            case 'p3pExit':
                return self::p3pLoginOut(); //测试通过
                break;
            case 'post.information':
                return self::information();
                break;
            default:
                return Helper::throwMessage( 11011 );
                break;
        }
    }

    /**
     * @description 获取用户信息
     * @pram args  参数
     * @pram
     */
    private static  function  getUserInfos() {
        $args  = Helper::getContainer()->request->get('args');
        $uid = $args['user'];
        //获取redis缓存数据
        $user = Helper::getRedis()->hGetAll(self::$cache_key_user_one.':'.$uid);

        if($user){
            $userDetail = Helper::getRedis()->hGetAll(self::$cache_key_userDetail_one.':'.$uid);
            if(!$userDetail) {
                $userDetail = UserDetail::getUserByUid($user['user_id']);
                $userDetail['ims'] = empty($userDetail['ims'])?array():json_decode($userDetail['ims'],true);
            }else{
                Helper::setRedisByArray(self::$cache_key_userDetail_one,$user['user_id'],$userDetail);
            }
        }else{
            $user =  User::getUserById($uid,$args['type']);
            if(!$user['user_id']){
                return   Helper::throwMessage( 20001 );
            }
            Helper::setRedisByArray(self::$cache_key_user_one,$user['user_id'],$user);
            $userDetail = UserDetail::getUserByUid($user['user_id']);
            $userDetail['ims'] = empty($userDetail['ims'])?array():json_decode($userDetail['ims'],true);
            Helper::setRedisByArray(self::$cache_key_userDetail_one,$user['user_id'],$userDetail);
        }
        $result = self::get_cols(self::$info_cols, array_merge($user,$userDetail) );
        return array('status'=>true,'msg'=>$result);
    }

    /**
     * 获取用户信息
     * @return mixed
     * @throws UserException
     */
    private static function getUsers($type = '') {
        if( empty($type) ){
            return Helper::throwMessage( 90006 );
        }
        $reback = array();
        $args = Helper::getContainer()->request->get('args');
        $users = User::getManyUsers($args,$type);
        if( !$users ){
            return Helper::throwMessage( 20017 );
        }
        if($type == 'user_name') {
            $user_names = array_values( array_unique( explode(',', $args['user_name']) ) );
            $users = User::getManyUsers($user_names,$type);
            foreach($users as $key=>$val){
                $reback[] = self::get_cols(self::$login_cols,$val);
            }
        }else{
            $user_ids = array_values( array_unique( explode(',', $args['user_id']) ) );
            $users = User::getManyUsers($user_ids,$type);
            foreach($users as $key => $val){
                $userDetail = UserDetail::getUserByUid($val['user_id']);
               // $newUser = self::get_cols(self::$login_cols, $val );
                $reback[] = self::get_cols(self::$face_cols, $userDetail );
            }
        }
        return array('status',$reback);
    }

    /**
     * 获取登录次数
     * @return array
     * @throws UserException
     */
    private static function  getUserLogs() {
        $args = Helper::getContainer()->request->get('args');
        $user_ids = array_values( array_unique( explode(',', $args['user_id']) ) );
        $userDetails = UserDetail::getUsersDetails($user_ids);
        if($userDetails) {
            foreach($userDetails as $key => $val) {
                $reback[$val['user_id']] = array(
                    'reg_time'=>$val['reg_time'],
                    'last_login'=>$val['last_login'],
                    'login_num'=> (new MongoDb('mongodb://'.$GLOBALS['config']['mongoDb']['host']))->selectDb('game_leajoy')->count(self::MONGODB_TABLE_NAME,array('uid'=>$val['user_id']))
                );
            }
        }else{
            return Helper::throwMessage( 20017 );
        }
        return array('status'=>true,'msg'=>$reback);
    }
    
    
    
    

    /**
     * @description 限制IP过滤检查
     * @param string $ip
     * @return bool
     * @throws UserException
     * @author Jarvis
     * @history Create 2016/02/27
     * */
    private static function ipFilter($ip){
        if (empty($ip)) {
            return false;
        }

        //获取限制IP列表
//        $filter_ip_list = Memcache::get(self::$cache_key_filter_ip);
        $filter_ip_list = Helper::getRedis()->hGetAll(self::$cache_key_filter_ip);

        if (empty($filter_ip_list)) {
            $filter_ip_list = Helper::getXmlLimitIpList();
        }
        if (!empty($filter_ip_list)) {
//            Memcache::set(self::$cache_key_filter_ip, '', $filter_ip_list);
            foreach ($filter_ip_list as $key => $val) {
                Helper::getRedis()->hSet(self::$cache_key_filter_ip, $key, $val);
            }
        } else {
            return false;
        }

        $filter_ip_list = preg_quote(implode('|',$filter_ip_list), '/');
        $filter_ip_list = '/'.str_replace(array('\*','\|'), array('(.*?)','|'), $filter_ip_list).'/';

        return preg_match($filter_ip_list, $ip);
    }



    /**
     * @description 搜索用户
     * @input  $username  用户名
     * @return array['user_id','pub_email','mobile','show_mail','show_mobile']
     * @throws UserException
     * @author Jarvis
     * @history Create 2016/02/25
     */
    private static function searchUser()
    {

        //输入参数信息
        $user_parameter = array();

        $user_args = Helper::getContainer()->request->get('args');

        if (!empty($user_args['username'])) {
            $user_parameter['username'] = $user_args['username'];
        } else {
            //throw new UserException(90006);
            return  Helper::throwMessage( 90006 );
        }

        //检查user_name类型
        $user_parameter['flag'] = 'user_name';
        if (Helper::emailFilter($user_parameter['username'])) {
            $user_parameter['flag'] = 'email';
        } elseif (Helper::mobileFilter($user_parameter['username'])) {
            $user_parameter['flag'] = 'mobile';
        }

        //memcache获取用户信息
        $user_info = UserDriver::getUserInformation($user_parameter['username'], $user_parameter['flag']);
        if (empty($user_info)) {
            //throw new UserException(50006);
            return  Helper::throwMessage( 20017 );
        }

        $return_array = array();

        //依据原始业务逻辑，emai通过user_detaile表查询
        $data_array = UserDetailDriver::getUserDetailInformation($user_info['user_id'], array('pub_email'));

        $return_array['pub_email'] = $data_array['pub_email'];

        $return_array['mobile'] = $user_info['mobile'];

        if (empty($return_array['pub_email']) && empty($return_array['mobile'])) {
            //throw new UserException('未绑定手机，未绑定邮箱');
            return  Helper::throwMessage( 90017 );
        }

        if (!empty($return_array['pub_email'])) {
            $return_array['show_mail'] = Helper::getSafeEmail($return_array['pub_email']);
        }

        if (!empty($return_array['mobile'])) {
            $return_array['show_mobile'] = Helper::getSafeMobile($return_array['mobile']);
        }

        $return_array['user_id'] = $user_info['user_id'];

        return array('status'=>true,'msg'=>$return_array);
    }


    /**
     * @description  检测用户
     * @input $account  帐号(昵称或email)
     * @input $passwd   密码
     * @input $ip       客户端ip
     * @input $referer  入口网址
     * @return false | array
     * @throws UserException
     * @author Jarvis
     * @history Create 2016/02/25
     */
    private static function  userCheck() {
        //函数返回值默认设置为 false
        $return_data = false;

        //输入参数信息
        $user_args = Helper::getContainer()->request->get('args');

        $key_list = array('account', 'passwd', 'ip', 'referer');

        //判断参数完整
        if (!isset($user_args['account']) || !isset($user_args['passwd'])) {
            //throw new UserException(90016);
            return  Helper::throwMessage( 90016 );
        }
        foreach ($user_args as $key => $value) {
            // 检查字段
            if (in_array($key, $key_list)) {
                if (empty($value)) {
                    //throw new UserException(90016);
                    return  Helper::throwMessage( 90016 );
                }
            }
        }

        $param_list = array(
            'username' => $user_args['account'],
            'passwd' => $user_args['passwd'],
            'is_remember' => false
        );

        //若未传递IP，通过URL获取用户IP地址
        if (empty($user_args['ip'])) {
            $param_list['ip'] = Helper::getClientIp();
        } else {
            $param_list['ip'] = $user_args['ip'];
        }

        //判断IP是否被禁止
        if (self::ipFilter($param_list['ip'])) {
            //throw new UserException('抱歉，IP已被禁止');
            return  Helper::throwMessage( 20032 );
        }

        //检查用户名
        $param_list['flag'] = 'user_name';
        if (Helper::emailFilter($param_list['username'])) {
            $param_list['flag'] = 'email';
        } elseif (Helper::mobileFilter($param_list['username'])) {
            $param_list['flag'] = 'mobile';
        } else {
            if (!Helper::userNameCheck($param_list['username'])) {
                //throw new UserException(20021);
                return  Helper::throwMessage( 20021 );
            }
        }

        //检查本次登录连续失败次数  Memcache::get(self::$cache_key_login_fail_num, $param_list['username'])
        $login_fail_count = intval(Helper::getRedis()->hGet(self::$cache_key_login_fail_num,$param_list['username']));

        Helper::getRedis()->hSet(self::$cache_key_login_fail_num,$param_list['username'],$login_fail_count+1);
       // Memcache::set(self::$cache_key_login_fail_num, $param_list['username'], $login_fail_count + 1);

        // 错误次数
        if ($login_fail_count > self::LOGIN_ERR_NUM) {
            //throw new UserException('登录失败，帐号已被锁定；请' . self::LOGIN_ERR_NUM . '分后再试。');
            return  Helper::throwMessage(20025,false,self::LOGIN_ERR_NUM);
        }

        //获取用户信息
        $user_info = UserDriver::getUserInformation($param_list['username'], $param_list['flag']);
        if (empty($user_info)) {
            //throw new UserException(20030);
            return  Helper::throwMessage(20030);
        }

        // 是否允许用户登录
        if (!$user_info['is_login']) {
            //throw new UserException(20023);
            return  Helper::throwMessage(20023);
        }

        // 验证密码
        if (Helper::enPasswd($param_list['passwd']) != $user_info['passwd']) {
            //throw new UserException(20022);
            return  Helper::throwMessage(20022);
        }

        $param_list['user_id'] = $user_info['user_id'];
        $param_list['last_login'] = $_SERVER['REQUEST_TIME'];
        unset($user_info['passwd']);

        // 写入cookie数据
        $status = Helper::setLoginCookie($param_list['username'], $user_info, $param_list['is_remember']);
        if (!$status) {
            return Helper::throwMessage(20024);
        }

        //登录成功，删除失败次数缓存
        Helper::getRedis()->hDel(self::$cache_key_login_fail_num,$param_list['username']);
      // Memcache::del(self::$cache_key_login_fail_num, $param_list['username']);

        // 第三方域名
        $param__t = Helper::getContainer()->request->get('__t');
        if ($param__t != '') {
            //Memcache::mc_set(MYAPI_API_LOGIN_USERID_PREFIX,$param__t, array_merge($user_info, array('is_remember' => $param_list['is_remember'])));
            $cache_prefix =  MYAPI_MD_PREFIX . '_ ' . MYAPI_API_LOGIN_USERID_PREFIX;

            if (false !== $param__t) {
                $cache_prefix =  $cache_prefix . '_' .  $param__t;
            }
            $user_info__t = array_merge($user_info, array('is_remember' => $param_list['is_remember']));
            foreach ($user_info__t as $key => $val) {
                Helper::getRedis()->hSet($cache_prefix, $key, $val);
            }
            Helper::getRedis()->expire($cache_prefix,86400);
        }

        // 更新登录时间
        try {
            // return false is update failed
            $up_user_detail_state = UserDetail::userDetailEditByUid($user_info['user_id'], array('last_login' => $param_list['last_login']));
            if ($up_user_detail_state) {
                $user_detail_old_data = Helper::getRedis()->hGetAll(self::$cache_key_userDetail_one.':'.$user_info['user_id']);// Memcache::get(self::$cache_key_userDetail_one, $user_info['user_id']);
                if ($user_detail_old_data) {
                    Helper::setRedisByArray(self::$cache_key_userDetail_one,$user_info['user_id'],array_merge($user_detail_old_data, array('last_login' => $param_list['last_login'])));
//                    Memcache::set(self::$cache_key_userDetail_one, $user_info['user_id'], array_merge($user_detail_old_data, array('last_login' => $param_list['last_login'])));
                }
            } else {
                Log::save(array('key' => '2000003', 'message' => $user_info['user_id'] . ' |update failed| last_login=' . $param_list['last_login']));
            }
        } catch (\Exception $e_msg) {
            // 写入失败日志
            Log::save(array('key' => '2000003', 'message' => $user_info['user_id'] . '|' . $e_msg->getMessage() . '| last_login=' . $param_list['last_login']));
        }

        //返回数据
        $return_data = array();
        foreach (self::$login_cols as $value) {
            if (isset($user_info[$value])) {
                $return_data[$value] = $user_info[$value];
            }
        }

        return array('status'=>true,'msg'=>$return_data);
    }


    /**
     * @description 验证码检测
     * @return bool
     * @throws UserException
     * @author Jarvis
     * @history Create 2016/02/25
     */
    private static function  userCaptchaCheck() {
        //函数返回值默认设置为 false
        $return_bool = false;

        //输入参数信息
        $user_args = Helper::getContainer()->request->get('args');

        //关键信息
        $key_list = array('cache_key', 'captcha', 'flag', 'op');

        // bindmobile 验证手机用到
        $op_list = array('forget_password', 'bindmobile');

        //去掉多余字段
        foreach ($user_args as $key => $value) {
            // 检查字段
            if (in_array($key, $key_list)) {
                if (empty($value)) {
                    //throw new UserException(90016);
                    return  Helper::throwMessage(90016);
                }
            } else {
                unset($user_args[$key]);
            }
        }

        //注意判断参数完整
        if (!isset($user_args['cache_key']) || !isset($user_args['captcha']) || !isset($user_args['flag']) || !isset($user_args['op'])) {
            //throw new UserException(90016);
            return  Helper::throwMessage(90016);
        }

        //限定验证参数
        if( !in_array($user_args['op'], $op_list) ){
            //throw new UserException(90016);
            return  Helper::throwMessage(90016);
        }

        //获取实际验证码
        $check_captcha = '';
        switch($user_args['flag']){
            case 'mobile':
                //$check_captcha =  Memcache::get(self::$cache_key_captcha_mobile,$user_args['op']  . '_' . $user_args['cache_key']);
                $check_captcha = Helper::getRedis()->hGet(self::$cache_key_captcha_mobile,$user_args['op']  . '_' . $user_args['cache_key']);
                break;
            case 'email':
                //$check_captcha = Memcache::get(self::$cache_key_captcha_email,$user_args['op'] . '_' . $user_args['cache_key']);
                $check_captcha = Helper::getRedis()->hGet(self::$cache_key_captcha_email,$user_args['op'] . '_' . $user_args['cache_key']);
                break;
        }

        //检查
        if(empty($check_captcha)||empty($user_args['captcha'])||strtolower($user_args['captcha'])!=strtolower($check_captcha))
        {
            $return_bool = false;
        } else {
            $return_bool = true;
        }

        //日志记录
        if($return_bool){
            log::save(array('key' => '2000006', 'message' =>" check_user_captcha ". var_export($user_args,true) ));
        }

        return array('status' => $return_bool, 'msg' => $return_bool);
    }

    /**
     * @description 绑定手机
     * @input $uid 用户ID
     * @input $mobile 手机号码
     * @input $captcha 验证码
     * @return bool
     * @throws \Exception | UserException
     * @author Jarvis
     * @history Create 2016/02/25
     */
    private static function  mobileBind() {
        $return_bool = false;
        $user_args = Helper::getContainer()->request->get('args');

        //检查字段
        if (empty($user_args['uid']) || empty($user_args['mobile']) || empty($user_args['captcha'])) {
            //throw new UserException(90016);
            return  Helper::throwMessage(90016);
        }

        if (!is_numeric($user_args['uid'])) {
            //throw new UserException(50005);
            return  Helper::throwMessage(50005);
        }
        if (!Helper::mobileFilter($user_args['mobile'])) {
            //throw new UserException(90014);
            return  Helper::throwMessage(90014);
        }

        //转换为整型
        $user_args_uid = intval($user_args['uid']);
        $db_mobile_info = UserDetailDriver::getUserDetailInformation($user_args_uid, array('mobile', 'mobile_verify'));
        if (empty($db_mobile_info)) {
            //throw new UserException(50005);
            return  Helper::throwMessage(50005);
        }

        //删除非法字段
        $key_list = array('mobile', 'captcha', 'old_captcha');
        foreach ($user_args as $key => $value) {
            if (!in_array($key, $key_list)) {
                unset($user_args[$key]);
            }
        }


        //验证是否已绑定(若绑定，则需要先验证原手机验证码 同时 验证新手机验证码)
        if (1 == $db_mobile_info['mobile_verify']) {
            if ($user_args['mobile'] == $db_mobile_info['mobile']) {
                //手机号码一致
                //throw new UserException(20015);
                return  Helper::throwMessage(20015);
            }

            if (empty($user_args['old_captcha'])) {
                //原手机验证码错误
                //throw new UserException(20013);
                return  Helper::throwMessage(20013);
            }

            //原手机验证码验证
           // $mc_mobile_captcha = Memcache::get(self::$cache_key_captcha_mobile,'old_bindmobile_' . $db_mobile_info['mobile']);
            $mc_mobile_captcha = Helper::getRedis()->hGet(self::$cache_key_captcha_mobile,'old_bindmobile_' . $db_mobile_info['mobile']);
            if (strtolower($mc_mobile_captcha) != strtolower($user_args['old_captcha'])) {
                //throw new UserException(20013);
                return  Helper::throwMessage(20013);
            }
        }

        //新手机验证码验证
        //$mc_mobile_captcha = Memcache::get(self::$cache_key_captcha_mobile,'bindmobile_' . $user_args['mobile']);
        $mc_mobile_captcha = Helper::getRedis()->hGet(self::$cache_key_captcha_mobile,'bindmobile_' . $user_args['mobile']);
        //需要确定 缓存中验证码是否一定小写
        if (strtolower($mc_mobile_captcha) != strtolower($user_args['captcha'])) {
            //throw new UserException(20011);
            return  Helper::throwMessage(20011);
        }

        //检测手机是否存在
        $isExist = UserDriver::getUserInformation($user_args['mobile'], 'mobile');
        if (!empty($isExist['user_id'])) {
            //throw new UserException(20015);
            return  Helper::throwMessage(20015);
        }

        try {
            //入库绑定

            $up_user_state = User::editByUid($user_args_uid, array('mobile' => $user_args['mobile']));
            if ($up_user_state) {
                //更新user缓存
                //$user_old_data = Memcache::get(self::$cache_key_user_one, $user_args_uid);
                $user_old_data = Helper::getRedis()->hGetAll(self::$cache_key_user_one . ':' . $user_args_uid);
                if ($user_old_data) {
                    //Memcache::set(self::$cache_key_user_one, $user_args_uid, array_merge($user_old_data, array('mobile' => $user_args['mobile'])));
                    Helper::setRedisByArray(self::$cache_key_user_one, $user_args_uid, array_merge($user_old_data, array('mobile' => $user_args['mobile'])));
                }

                //添加用户详情表
                $up_user_detail_state = UserDetail::userDetailEditByUid($user_args_uid, array('mobile' => $user_args['mobile'], 'mobile_verify' => 1));
                if ($up_user_detail_state) {
                    Log::save(array('key' => '2000006', 'message' => ' bind_mobile ' . var_export($user_args, true)));
                    $return_bool = true;

                    //若 电话号码 通过校验,入库绑定成功，则删除 user_detail缓存
                    //$user_detail_old_data = Memcache::get(self::$cache_key_userDetail_one, $user_args_uid);
                    //$user_detail_old_data = Helper::getRedis()->hGetAll(self::$cache_key_userDetail_one . ':' . $user_args_uid);
                    //if ($user_detail_old_data) {
                        //Memcache::del(self::$cache_key_userDetail_one, $user_args_uid);
                    //}
                    Helper::getRedis()->delete(self::$cache_key_userDetail_one . ':' . $user_args_uid);

                }
            }
        } catch (\Exception $e_msg) {
            Log::save(array('key' => '2000007', 'message' => ' [ERR] bind_mobile {' . $e_msg->getCode() . '},{' . $e_msg->getMessage() . '},' . var_export($user_args, true)));
            //throw new \Exception($e_msg->getMessage(), $e_msg->getCode());
            return array('status' => false, 'msg' => $e_msg->getMessage());
        }
        return array('status' => $return_bool, 'msg' => $return_bool);
    }


    /**
     * @description 绑定邮箱
     * @input $uid 用户ID
     * @input $email 邮箱地址
     * @input $captcha 验证码
     * @return bool
     * @throws UserException
     * @author Jarvis
     * @history Create 2016/02/25
     */
    private static function emailBind()
    {
        $return_bool = false;
        $user_args = Helper::getContainer()->request->get('args');

        //检查字段
        if (empty($user_args['uid']) || empty($user_args['email']) || empty($user_args['captcha'])) {
            //throw new UserException(90016);
            return  Helper::throwMessage(90016);
        }
        if (!is_numeric($user_args['uid'])) {
            //throw new UserException(50005);
            return  Helper::throwMessage(50005);
        }
        if (!Helper::emailFilter($user_args['email'])) {
            //throw new UserException(90013);
            return  Helper::throwMessage(90013);
        }

        //转换为整型
        $user_args_uid = intval($user_args['uid']);
        $db_email_info = UserDetailDriver::getUserDetailInformation($user_args_uid, array('pub_email', 'email_verify'));
        if (empty($db_email_info)) {
            //throw new UserException(50005);
            return  Helper::throwMessage(50005);
        }

        //删除非法字段
        $key_list = array('email', 'captcha', 'old_captcha');
        foreach ($user_args as $key => $value) {
            if (!in_array($key, $key_list)) {
                unset($user_args[$key]);
            }
        }


        //验证是否已绑定(若绑定，则需要先验证原邮箱验证码 同时 验证新邮箱验证码)
        if (1 == $db_email_info['email_verify']) {
            if ($user_args['email'] == $db_email_info['pub_email']) {
                //邮箱地址一致
                //throw new UserException(20016);
                return  Helper::throwMessage(20016);
            }

            if (empty($user_args['old_captcha'])) {
                //原邮箱验证码错误
                //throw new UserException(20014);
                return  Helper::throwMessage(20014);
            }

            //原邮箱验证码验证
            //$mc_email_captcha = Memcache::get(self::$cache_key_captcha_email, 'old_bindmail_' . $db_email_info['pub_email']);
            $mc_email_captcha = Helper::getRedis()->hGet(self::$cache_key_captcha_email,'old_bindmail_' . $db_email_info['pub_email']);
            if (strtolower($mc_email_captcha) != strtolower($user_args['old_captcha'])) {
                //throw new UserException(20014);
                return  Helper::throwMessage(20014);
            }
        }

        //新邮箱验证码验证
        //$mc_email_captcha = Memcache::get(self::$cache_key_captcha_email, 'bindmail_' . $user_args['email']);
        $mc_email_captcha = Helper::getRedis()->hGet(self::$cache_key_captcha_email,'bindmail_' . $user_args['email']);
        if (strtolower($mc_email_captcha) != strtolower($user_args['captcha'])) {
            //throw new UserException(20011);
            return  Helper::throwMessage(20011);
        }

        //检测邮箱是否存在
        $isExist = UserDriver::getUserInformation($user_args['email'], 'email');
        if (!empty($isExist['user_id'])) {
            //throw new UserException(20016);
            return  Helper::throwMessage(20016);
        }

        try {
            //入库绑定
            $up_user_state = User::editByUid($user_args_uid, array('email' => $user_args['email']));
            if ($up_user_state) {
                //更新user缓存
                //$user_old_data = Memcache::get(self::$cache_key_user_one, $user_args_uid);
                $user_old_data = Helper::getRedis()->hGetAll(self::$cache_key_user_one . ':' . $user_args_uid);
                if ($user_old_data) {
                    //Memcache::set(self::$cache_key_user_one, $user_args_uid, array_merge($user_old_data, array('email' => $user_args['email'])));
                    Helper::setRedisByArray(self::$cache_key_user_one, $user_args_uid, array_merge($user_old_data, array('email' => $user_args['email'])));
                }

                //更新cookie
                $user_new_data = UserDriver::getUserInformation($user_args_uid, 'user_id');
                Helper::setLoginCookie($user_new_data['user_name'], $user_new_data);

                //添加用户详情表
                $up_user_detail_state = UserDetail::userDetailEditByUid($user_args_uid, array('pub_email' => $user_args['email'], 'email_verify' => 1));
                if($up_user_detail_state){
                    Log::save(array('key' => '2000006', 'message' => ' bind_mail ' . var_export($user_args, true)));
                    $return_bool = true;

                    //若 email 通过校验入库绑定成功，则删除 user_detail缓存
                    //$user_detail_old_data = Memcache::get(self::$cache_key_userDetail_one, $user_args_uid);
                    //$user_detail_old_data = Helper::getRedis()->hGetAll(self::$cache_key_userDetail_one . ':' . $user_args_uid);
                    //if ($user_detail_old_data) {
                        //Memcache::del(self::$cache_key_userDetail_one, $user_args_uid);
                    //}
                    Helper::getRedis()->delete(self::$cache_key_userDetail_one . ':' . $user_args_uid);
                }
            }
        } catch (\Exception $e_msg) {
            Log::save(array('key' => '2000007', 'message' => ' [ERR] bind_mail {' . $e_msg->getCode() . '},{' . $e_msg->getMessage() . '},' . var_export($user_args, true)));
            //throw new \Exception($e_msg->getMessage(), $e_msg->getCode());
            return array('status' => false, 'msg' => $e_msg->getMessage());
        }

        return array('status' => $return_bool, 'msg' => $return_bool);
    }

    
    
    

    /**
     * 游戏站修改用户
     * $data = array('pet_name','gender', 'birthday', 'location','interest'=行业,'wish'=收入)
     * @return type
     */
    private  static  function gameUserEdit() {

        $args = Helper::getContainer()->request->get('args');
        $uid = empty($args['uid']) ? 0 : $args['uid'];
        $feilds = array('pet_name', 'gender', 'birthday', 'location', 'interest', 'wish');

        //检测uid是否为空
        if (!$uid) {
           return  Helper::throwMessage( 90006 );
        }
        unset( $args['uid'] );
        //排除非法字段
        foreach ($args as $key => $val) {
            // 检查字段
            if (!in_array($key, $feilds)) unset($args[$key]);
        }
        //修改字段检测
        if (empty($args)) {
            return  Helper::throwMessage( 90006 );
        }

        //获取用户数据
        $user = Userdetail::getUserByUid($uid);

        if (!$user['user_id']) {
            return  Helper::throwMessage( 20030 );
        }

        // 不相同修改
        $state = UserDetail::userDetailEditByUid($uid, $args);
        if (!$state) {
            return Helper::throwMessage( 10001 );
        } else {
            //此处缺少更新memcache操作  ！！！！！！！！！！！！！！
            // 更新缓存
            $uid = array($uid);
            foreach ($uid as $col) {
                $new_data = $args;
                // 更新缓存
                $udetails = Helper::getRedis()->hGetAll(self::$cache_key_userDetail_one.':'.$col);// Memcache::get( self::$cache_key_userDetail_one, $col );
                if ($udetails) {
                    if(!is_array($udetails)) $udetails = array($udetails);
                    Helper::setRedisByArray(self::$cache_key_userDetail_one,$col,array_merge($udetails, $new_data));
                   // Memcache::set(self::$cache_key_userDetail_one, $col, array_merge($udetails, $new_data));
                }
            }
            //日志记录
            Log::save(array('key' => '2000006', 'message' =>' update_game '. var_export($args,true) ));

            return array('status'=>true,'msg'=>$state);
        }
    }

    /**
     * 修改和重置密码用户密码
     * @return mixed
     * @throws UserException
     */
    private  static function  pwdEdit(){
        $args = Helper::getContainer()->request->get('args');
        $uid = empty( $args['uid'] ) ? 0 : $args['uid'];
        $oldPwd = empty( $args['old_pwd'] ) ? 0 : $args['old_pwd'];
        $passwd = empty( $args['passwd'] ) ? 0 : $args['passwd'];
        $pwdconfirm = empty( $args['pwdconfirm'] ) ? 0 : $args['pwdconfirm'];
        //检测数据是否为空
        if ( empty($uid) ||  empty($passwd)  || empty($pwdconfirm) || ($passwd!=$pwdconfirm) ) {
           return  Helper::throwMessage( 90006 );
        }
        //
        if( empty($oldPwd) && empty($args['event_forget_password'])  ){
            return Helper::throwMessage( 20007 );
        }
        //检测密码是否符合规则
        if (  empty($args['event_forget_password']) && ($oldPwd == $passwd) ){
            return Helper::throwMessage( 20007 );
        }
        //密码长度检验
        if ( strlen($passwd)>16 || strlen($passwd)<4 ){
            return Helper::throwMessage( 20018 );
        }
        $oldPwd = Helper::enPasswd($oldPwd);
        $passwd = Helper::enPasswd($passwd);
        //获取原有密码
        $user = User::getUserById($uid);
        if($user['user_id']){
            if( empty($args['event_forget_password']) && $user['passwd'] != $oldPwd){
              return   Helper::throwMessage( 20028 );
            }
            //执行修改
            $state = User::editByUid( $uid,array('passwd'=>$passwd) );
            if($state){
                // 取缓存
                $old_data =  Helper::getRedis()->hGet(self::$cache_key_user_one.':'.$uid);
                if ($old_data) {
                    // 更新缓存 当前会员
                    Helper::getRedis()->hset(self::$cache_key_user_one.':'.$uid,'passwd',$passwd);
                //    Memcache::set(self::$cache_key_user_one, $uid, array_merge($old_data, array('passwd'=>$passwd)));
                    Helper::getRedis()->hSet(self::$cache_key_userDetail_one.':'.$uid,'passwd',$passwd);
                    //  Memcache::set(self::$cache_key_userDetail_one, $uid, array_merge( $old_data, array('passwd'=>$passwd) ));
                }
                //更新cookie信息
                if( isset($args['email'])) {
                    $user = User::getUserById($uid);
                    Helper::setLoginCookie($user['user_name'], $user);
                    // pub_user_api::get_instance()->set_login_cookie($user['user_name'], $user);
                }
                // 更新用户详情表中密码
                //  UserDetail::userDetailEditByUid( $uid, array('passwd'=>$passwd) );
            }else{
                return Helper::throwMessage( 90005 );
            }
            //日志记录 文件
            Log::save(array('key' => '2000006', 'message' =>' update_pwd '. var_export($args,true) ));
            return array('status'=>true,'msg'=>$state);
        }else{
            return Helper::throwMessage( 20028 );
        }
    }

    /**
     * 新用户注册
     * @return bool|mixed
     * @throws UserException
     * @throws \Exception
     */
    private  static  function  register(){
        $args = Helper::getContainer()->request->get('args');
        $regApp = Helper::getContainer()->request->get('api_app_id');
        $feilds = array('user_name', 'passwd', 'email', 'mobile', 'cli_ip');  // pub_email sta=mobile审核状态0=未验证|1=已验证
        foreach( $feilds as $key ){
            // 检查字段
            if( !isset($args[$key]) ) {
                return Helper::throwMessage( 20018 );
            }else{
                $newContion = array(
                    'user_name'=>$args['user_name'],
                    'passwd'=>Helper::enPasswd($args['passwd']),
                    'email'=>$args['email'],
                    'mobile'=>$args['mobile']
                );
            }
        }
        $detail = array(
            'reg_app' => $regApp,
            'reg_ip'  => $args['cli_ip'],
            'mobile_verify'=> (isset($args['mobile_verify']) && $args['mobile_verify']==1) ? 1 : 0
        );
        return UserDriver::saveUser($newContion,$detail);
    }

    /**
     * 跨域登录
     * @throws \Exception
     */
    private static function p3pLogin() {
        $args = Helper::getContainer()->request->get('args');
        $feilds = array('user_id', 'account', 'save_sta', 'back_url');
        if( isset($args['user_id']) && !empty($args['user_id']) ){
            foreach( $args as $k=>$v ){
                // 检查字段
                if( in_array($k, $feilds) ) {
                    if( empty($v) ) return  Helper::throwMessage( 90016 );
                } else {
                    unset($args[$k]);
                }
            }

            //获取用户信息
            $user = User::getUserById($args['user_id']);
            if( empty($user) ) {
                return  Helper::throwMessage( 20001 );
            }
            //记录登录信息至COOKIE
            $status = Helper::setLoginCookie( $user['user_name'], $user, $args['save_sta'] );

            if($status){
                return array('status'=>true,'msg'=>'登录成功');
            }else{
                return Helper::throwMessage( 20024 );
            }
           // return new RedirectResponse(urldecode($args['back_url']));
        }
        return Helper::throwMessage( 90006 );

    }

    /**
     * 跨域退出登录
     * @throws \Exception
     */
    private  static  function p3pLoginOut() {
        $args = Helper::getContainer()->request->get('args');
        $feilds = array('back_url');
        foreach( $args as $k=>$v ){
            // 检查字段
            if( in_array($k, $feilds) ){
                if( empty($v) ) return Helper::throwMessage( 90016 );
            } else{
                unset($args[$k]);
            }
        }
        //清除登录cookie
        $status = Helper::delLoginCookie();
        if($status){
            return array('status'=>true,'msg'=>'退出成功');
        }else{
            return Helper::throwMessage( 20026 );
        }
        //return new RedirectResponse($args['back_url']);
    }

    /**
     * 从数据组中取一些数据
     * @param type $cols
     * @param type $data
     * @return type
     */
    private static function get_cols( $cols, $data ){
        $ary = array();
        if(is_array($data)){
            foreach( $cols as $col ) {
                switch( $col )
                {
                    case 'login_num':
                        //192.168.8.3:27017
                        $ary[$col] =   (new MongoDb('mongodb://'.$GLOBALS['config']['mongoDb']['host']))->selectDb('game_leajoy')->count(self::MONGODB_TABLE_NAME,array('uid'=>$data['user_id']));
                        break;
                    case 'gender':
                        $ary[$col] = is_null($data[$col])?'未知':($data[$col]==1?'男':'女');
                        break;
                    default:
                        if(isset($data[$col]))
                        {
                            $ary[$col] = $data[$col];
                        }
                }
            }
        }else if(is_object($data)){
            foreach( $cols as $col ) {
                switch( $col )
                {
                    case 'login_num':
                        $ary[$col] =   (new MongoDb('mongodb://'.$GLOBALS['config']['mongoDb']['host']))->selectDb('game_leajoy')->count(self::MONGODB_TABLE_NAME,array('uid'=>$data->user_id));
                        break;
                    case 'gender':
                        $ary[$col] = is_null($data->$col)?'未知':($data->$col==1?'男':'女');
                        break;
                    default:
                        if(isset($data->$col))
                        {
                            $ary[$col] = $data->$col;
                        }
                }
            }
        }
        return $ary;
    }


    /**
     * 用户积分修改接口
     * @return array
     */
    private  static  function updateScore() {
        $args = Helper::getContainer()->request->get('args');
        $actionType = array( 1,2,3); // 积分类型
        $feilds = array('trade_id','source_code','in_silver','out_silver','action_type','notes');
        if( isset($args['uid']) && !empty($args['uid']) ){
            foreach( $args as $k=>$v ){
                // 检查字段
                if( !in_array($k, $feilds) ) {
                    return  Helper::throwMessage( 90006 );
                }
            }
            //检测积分类型
            if( !in_array($args['action_type'],$actionType) ) return  Helper::throwMessage( 80021 );

            switch( $args['action_type'] ){
                case 1: // pay
                case 2: // spend
                    if( is_numeric($args['out_silver']) || $args['out_silver']<1 ){
                        return  Helper::throwMessage( 80011 );
                    }
                    break;
                case 3: // score
                    if( is_numeric($args['in_silver']) || $args['in_silver']<1  ){
                        return  Helper::throwMessage( 80010 );
                    }
                    break;
            }
            //查询用户信息
            $user = User::getUserById( $args['uid']);
            if( !$user ){
                return  Helper::throwMessage( 20030 );
            }
            $args['user_name'] = $user['user_name'];

            // 查询用户积分
            $score = UserScore::getUserByUid($args['uid']);
            if( !$score ){
                return Helper::throwMessage( 80020 );
            }

            //积分表信息
            $e_score = array( 'dml_flag'=>2);
            switch( $args['action_type'] ){
                case 1: // pay
                    $e_score['total'] = $score['total'] + $args['in_silver'];
                    $e_score['silver_total'] = $score['silver_total'] + $args['in_silver'];
                    $e_score['silver_balance'] = $score['silver_balance'] + $args['in_silver'];
                    $code = 80001;
                    break;
                case 3: // score
                    $e_score['total'] = $score['total'] + $args['in_silver'];
                    $e_score['silver_total'] = $score['silver_total'] + $args['in_silver'];
                    $e_score['silver_balance'] = $score['silver_balance'] + $args['in_silver'];
                    $code = 80002;
                    break;
                case 2: // spend
                    if( $score['silver_balance'] < $args['out_silver'] ){
                        // 余额不足
                        return Helper::throwMessage( 80022 );
                    }
                    $e_score['silver_balance'] = $score['silver_balance'] - $args['out_silver'];
                    $code = 80003;
                    break;
            }

            //修改和记录信息
            $state = UserScore::userEditByUid( $args['uid'], $e_score );
            if( $state ){
                Helper::setRedisByArray(self::$cache_key_userScore,$args['uid'],$e_score);
                $contion = array(
                    'user_id'=>$user['uid'],
                    'user_name'=>$user['user_name'],
                    'trade_id'=>$user['trade_id'],
                    'source_code'=>$args['source_code'],
                    'in_silver'=>$args['in_silver'],
                    'out_silver'=>$args['out_silver'],
                    'notes'=>$args['notes'],
                    'action_type'=>$args['action_type']
                );
                UserScoreHistory::newScoureHistory($contion);
                Log::save(array('key' => '5000001', 'message' =>' user_score '. var_export($args,true) ));
                return array('status'=>true,'msg'=>$e_score['silver_balance']);
              //  mod_user_score_history::add($args);
            }else{
                return Helper::throwMessage( $code );
            }
        }
        return Helper::throwMessage( 94500 );
    }

    /**
     * 数据获取  并插入数据库
     * @return array
     */
    private  static function information(){
        $data = Helper::getContainer()->request->get('data');
        $data = json_decode($data,true);
        $feilds = array('title','img','url','app');
        if(!is_array($data)){
            return Helper::throwMessage( 94500 );
        }
        $data = Helper::setFeildsAttr($data,$feilds);
        $state = Information::insertInfo($data);
        return array('status'=>true,'msg'=>$state);
    }
}
