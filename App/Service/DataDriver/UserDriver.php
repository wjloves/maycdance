<?php
namespace App\Service\DataDriver;
use App\Exception\UserException;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserScore;
use App\Service\Helper\Helper;
use App\Service\Helper\Log;
use App\Service\DataDriver\UserDetailDriver;

/**
 * @description 老论坛的数据获取层
 * */
class UserDriver
{
    public static $cache_key_user_one = 'U_101';

    /**
     * 添加新用户
     *
     * @param array $param
     * @return bool
     */
    public static function saveUser($newContion,$detail){
        //检测用户名长度和内容
        if(!Helper::userNameCheck($newContion['user_name'])){
            return Helper::throwMessage( 20019 );
        }
        if( (empty($newContion['user_name']) && empty($newContion['email']) && empty($newContion['mobile']) ) || empty($newContion['passwd']) || empty($detail['reg_ip']) ) {
            return Helper::throwMessage( 90006 );
        }

        //检测用户是否存在
        $isExist = User::checkUser($newContion);

        if( isset($isExist['user_id']) && !empty($isExist['user_id']) ){
            return Helper::throwMessage( 20006);
        }

        //入库
        $user = User::regUser($newContion);
        if( $user['user_id'] ){
            try {
                //添加用户详情表
                UserDetail::newUser( array_merge($detail,array('user_id' => $user['user_id'], 'reg_time' => time())) );
                //添加用户积分表
                UserScore::newUser(array('user_id' => $user['user_id']));
                //insert log
                Log::save(array('key' => '2000006', 'message' => ' register_passport ' . var_export($args, true)));
                return array('status'=>true,'msg'=>$user['user_id']);
            }catch (UserException $e){
                User::delByUid($user['user_id']);
                Log::save(array('key' => '2000002', 'message' => '回滚用户表'));
                return  Helper::throwMessage( 20009 );
            }
        }else{
            return Helper::throwMessage( 90005 );
        }
    }



    /**
     * @description 获取User信息
     * @param string $str
     * @param string $flag
     * @return array | bool
     * @author Jarvis
     * @history Create 2016/02/25
     * */
    public static function getUserInformation($str, $flag = 'user_id')
    {
        static $user_data = array();

        //若flag=user_id则先尝试从缓存中获取数据
        if ('user_id' == $flag) {
            if (!Helper::idCheck($str)) {
                return false;
            }

            if (empty($user_data[$str])) {
                $user_data[$str] = Helper::getRedis()->hGetAll(self::$cache_key_user_one . ':' . $str);
            }

            if (!empty($user_data[$str])) {
                return $user_data[$str];
            }
        }

        #数据库查询用户信息，注意：email和mobile均无唯一键，需要和mogan沟通下是否如此实现；
        $user_info = array();
        switch ($flag) {
            case 'user_id':
                $user_info = User::getUserById($str);
                break;
            case 'user_name':
                $user_info = User::getUserByName($str);
                break;
            case 'email':
                $user_info = User::getUserByEmail($str);
                break;
            case 'mobile':
                $user_info = User::getUserByMobile($str);
                break;
        }
        if (empty($user_info['user_id'])) {
            return false;
        }

        //清理安全数据
        $key_list = array('pay_passwd');
        foreach ($key_list as $value) {
            unset($user_info[$value]);
        }

        //更新缓存数据
        $user_data[$user_info['user_id']] = $user_info;
        Helper::setRedisByArray(self::$cache_key_user_one,$user_info['user_id'],$user_info);
        //Memcache::set(self::$cache_key_user_one, $user_info['user_id'], $user_info);

        return $user_data[$user_info['user_id']];
    }



    /**
     * @description 更新User信息(+Redis数据更新，passwd字段数据在此方法中转码)
     * @param int $uid
     * @param array $param_list
     * @return bool
     * @author Jarvis
     * @history Create 2016/03/10
     * */
    public static function updateUserInfo($uid, $param_list = array())
    {
        $result = false;
        //检查用户ID
        if (!Helper::idCheck($uid)) {
            return false;
        }
        if(isset($param_list['passwd'])){
            $uncode_passwd = $param_list['passwd'];
            $param_list['passwd'] = Helper::enPasswd($param_list['passwd']);
        }
        //更新数据库
        $state = User::editByUid($uid, $param_list);
        if ($state) {
            // 取缓存
            $old_data = Helper::getRedis()->hGetAll(self::$cache_key_user_one . ':' . $uid);
            if (!empty($old_data)) {
                //清理安全数据['pay_passwd']不能存在缓存中
                $key_list = array('pay_passwd');
                foreach ($key_list as $value) {
                    unset($param_list[$value]);
                }

                // 更新缓存 当前会员
                Helper::setRedisByArray(self::$cache_key_user_one, $uid, array_merge($old_data, $param_list));
            }

            if (isset($param_list['passwd']) && !empty($param_list['passwd'])) {
                //更新密码强度
                UserDetailDriver::updateUserDetailInfo($uid, array('feed_privacy' => Helper::setFeedPrivacy(array('passwd' => $uncode_passwd))));
            }

            $result = true;
        }
        return $result;
    }

}
