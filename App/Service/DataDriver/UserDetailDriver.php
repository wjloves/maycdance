<?php
namespace App\Service\DataDriver;
use App\Service\Helper\Helper;
use App\Models\UserDetail;

/**
 * @description 数据库资源，数据集合查找功能
 * @Author  Jarvis
 * @history  Create 2016/3/9
 */
class UserDetailDriver
{
    public static $cache_key_userDetail_one = 'UD_101';

    /**
     * @description 获取UserDetail信息
     * @param string $user_id
     * @param array $properties
     * @return array | bool
     * @author Jarvis
     * @history Create 2016/02/25
     * */
    public static function getUserDetailInformation($user_id, $properties = array())
    {
        if (!Helper::idCheck($user_id)) {
            return false;
        }

        static $user_detail_data = array();

        if(empty($user_detail_data[$user_id])) {
            //取缓存
            // Memcache::get(self::$cache_key_userDetail_one, $user_id);
            $user_detail_data[$user_id] = Helper::getRedis()->hGetAll(self::$cache_key_userDetail_one . ":" . $user_id);
            if (empty($user_detail_data[$user_id])) {
                //缓存获取失败，则查找数据库
                $user_detail_info = UserDetail::getUserByUid($user_id);
                if (empty($user_detail_info)) {
                    return false;
                }
                //密码安全级别
                if (empty($user_detail_info['feed_privacy'])) {
                    $user_detail_info['feed_privacy'] = Helper::setFeedPrivacy(array());
                }

                $user_detail_info['feed_privacy'] = unserialize($user_detail_info['feed_privacy']);
                $user_detail_info = array_merge($user_detail_info, $user_detail_info['feed_privacy']);

                //用户安全级别
                if ($user_detail_info['mobile_verify'] == 1 && $user_detail_info['email_verify'] == 1) {
                    $user_detail_info['user_safe_level'] = 100;
                } else if ($user_detail_info['mobile_verify'] == 1 || $user_detail_info['email_verify'] == 1) {
                    $user_detail_info['user_safe_level'] = 50;
                }

                //数据库查询出用户信息后，需要更新Memcached数据
                Helper::setRedisByArray(self::$cache_key_userDetail_one, $user_id,$user_detail_info);
                //  Memcache::set(self::$cache_key_userDetail_one, $user_id, $user_detail_info);
                $user_detail_data[$user_id] = $user_detail_info;
            }
        }

        if (empty($property)) {
            return $user_detail_data[$user_id];
        }

        $return_array = array();

        foreach ($properties as $value) {
            if (!empty($user_detail_data[$user_id][$value])) {
                $return_array[$value] = $user_detail_data[$user_id][$value];
            }
        }
        return $return_array;
    }


    /**
     * @description 更新UserDetail信息
     * @param string $uid
     * @param array $param_list
     * @return bool
     * @author Jarvis
     * @history Create 2016/03/10
     * */
    public static function updateUserDetailInfo($uid, $param_list = array())
    {
        $result = false;

        //检查用户ID
        if (!Helper::idCheck($uid)) {
            return false;
        }

        //更新数据库
        $up_user_detail_state = UserDetail::userDetailEditByUid($uid, $param_list);
        if ($up_user_detail_state) {
            // 取缓存
            $user_detail_old_data = Helper::getRedis()->hGetAll(self::$cache_key_userDetail_one . ':' . $uid);
            if ($user_detail_old_data) {
                //更新缓存
                if (isset($param_list['passwd']) || isset($param_list['email_verify']) || isset($param_list['mobile_verify'])) {
                    Helper::getRedis()->delete(self::$cache_key_userDetail_one . ':' . $uid);
                } else {
                    Helper::setRedisByArray(self::$cache_key_userDetail_one, $uid, array_merge($user_detail_old_data, $param_list));
                }
            }
            $result = true;
        }
        return $result;
    }
}