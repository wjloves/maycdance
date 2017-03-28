<?php
namespace App\Service\Helper;
use App\Service\Helper\Helper;
use App\Service\Helper\Log;
/**
 * 发送短信验证码   和   邮箱验证
 *
 * @author skey <cookphp@gmail.com>
 */
class Mobile{
    const SIGN = ' 【杏娱】';
    const SMS_ADMIN_NAME = '';
    const SMS_ADMIN_PWD = '';
    protected static $moblieKey = 'Mobile:0_502';
    protected static $mailKey  = 'Mail:O_501';

    public static function echo_validate_mobile( $config = array() ) {
        if( empty($config['mobile']) ||  !Helper::mobileFilter(($config['mobile']))  ){
            Helper::throwMessage( 20012 );
        }

        // mobile round validate
        $mobile_validate = self::randNumber();

        //发送手机短信
        $msg = sprintf("短信验证码为：%s，请勿将验证码提供给他人。", $mobile_validate);
        $res = self::send_sms($config['mobile'], $msg);
        $res_m = false;
        if( $res ){
            // 保存验证码
            $cacheKey = self::$moblieKey.':'.$config['mobile'];
            $res_m = Helper::getRedis()->set($cacheKey,$mobile_validate);
            Helper::getRedis()->expire($cacheKey,180);
            // 开发模式 记录验证码
//            if(DEBUG_LEVEL) cls_log::save(array('key' => '4000008', 'message' => $config['op'].': '.$cache_key.' = '.$mobile_validate ));
//            if($config['mobile']=='13585806151')cls_log::save(array('key' => '4000008', 'message' => $config['op'].': '.$cache_key.' = '.$mobile_validate ));
        }

        return $res && $res_m ? true : false;
    }

    /**
     * 获取手机验证码
     *
     * @param  $mobile
     * @return
     */
    public static function get_validate_mobile( $mobile ) {
        return Helper::getRedis()->get(self::$moblieKey.':'.$mobile);
    }

    /**
     * 随机生成6位数字
     * @return string
     */
    public  static function randNumber(){
        //生成六位随机数
        $ychar="0,1,2,3,4,5,6,7,8,9";
        $list=explode(",",$ychar);
        $content='';
        for($i=0;$i<6;$i++){
            $randnum=mt_rand(0,9); // 10+26;
            $content.=$list[$randnum];
        }
        return $content;
    }


    /**
     * 发送短信
     */
    public static function send_sms( $mobile, $msg )
    {
        $return = false;
        $start_time = microtime(true);
        $use_time = 0;
        $result = '';

        $params = '';
        $argv = array(
            'name'=>$GLOBALS['config']['sms']['sp_username'],     //必填参数。用户账号
            'pwd'=>$GLOBALS['config']['sms']['sp_userpwd'],     //必填参数。（web平台：基本资料中的接口密码）
            'content'=>$msg,   //必填参数。发送内容（1-500 个汉字）UTF-8编码
            'mobile'=>$mobile,   //必填参数。手机号码。多个以英文逗号隔开
            'stime'=>'',   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
            'sign'=>$GLOBALS['config']['sms']['sp_sign'],    //必填参数。用户签名。
            'type'=>'pt',  //必填参数。固定值 pt
            'extno'=>''    //可选参数，扩展码，用户定义扩展码，只能为数字
        );

        try{
            $params = http_build_query($argv);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_URL, $GLOBALS['config']['sms']['sp_url']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 80);
            $result = curl_exec($ch);
            $errno = curl_errno($ch);
            curl_close($ch);

            $return = substr( $result, 0, 1 ) == '0' ? true : false;
            if( !$return ){
                #写入流水
                Log::save( array('key' => '4000009', 'message' => " [{$errno}]".   $result ) );

            }
        }catch( \Exception $e ){
            Log::save( array('key' => '4000009', 'message' => $e->getMessage() ) );
        }

        return $return;
    }

    /**
     * 发送验证邮箱
     *
     * @param  $config
     * @return
     */
    public static function echo_validate_mail( $config = array() ) {
        $to = $config['to_mail'];
        $subject = '找回密码 验证码';
        $body = '';

        switch( $config['op'] ){
            case 'bindmail':
            case 'old_bindmail':
                $subject = '绑定邮箱 验证码';
                break;
        }

        // cache key
        $cache_key = $config['op']. '_'. $config['_t'];
        if( empty($config['to_mail']) || ! Helper::email($config['to_mail']) ){
             Helper::throwMessage( 90013 );
        }

        try{
            // round validate
            $validate = self::randNumber();

            //发送邮件
            $body = sprintf("验证码为：%s，请勿将验证码提供给他人。", $validate). self::SIGN;
            $res = '';//send_email($to,$subject, $body);
            if( $res ){

                // 保存验证码
                $res_m = Helper::getRedis()->set(self::$mailKey.':'.$config['to_mail'],$validate);
              //  $res_m = pub_memcache::set('O_501', $cache_key, $validate);
                // 开发模式 记录验证码
                Log::save(array('key' => '4000008', 'message' => $config['op'].': '. $cache_key.' = '.$validate ));
                if( !$res_m ){
                    // log
                    Helper::throwMessage('验证码保存失败');
                }
            }
            else{
                Helper::throwMessage('邮件发送失败');
            }
        }catch( \Exception $e ){
            Log::save( array('key' => '4000008', 'message' => $e->getMessage() ));
        }

        return $res;
    }

    /**
     * 获取邮箱验证码
     *
     * @param type $op
     * @param type $mobile
     * @return type
     */
    public static function get_validate_mail( $mail ) {
        return Helper::getRedis()->get(self::$mailKey.':'.$mail);
    }
}
?>