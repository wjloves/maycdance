<?php
namespace App\Service\Helper;
use App\Service\Helper\Helper;
use App\Service\Helper\Log;
/**
 * ���Ͷ�����֤��   ��   ������֤
 *
 * @author skey <cookphp@gmail.com>
 */
class Mobile{
    const SIGN = ' �����顿';
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

        //�����ֻ�����
        $msg = sprintf("������֤��Ϊ��%s��������֤���ṩ�����ˡ�", $mobile_validate);
        $res = self::send_sms($config['mobile'], $msg);
        $res_m = false;
        if( $res ){
            // ������֤��
            $cacheKey = self::$moblieKey.':'.$config['mobile'];
            $res_m = Helper::getRedis()->set($cacheKey,$mobile_validate);
            Helper::getRedis()->expire($cacheKey,180);
            // ����ģʽ ��¼��֤��
//            if(DEBUG_LEVEL) cls_log::save(array('key' => '4000008', 'message' => $config['op'].': '.$cache_key.' = '.$mobile_validate ));
//            if($config['mobile']=='13585806151')cls_log::save(array('key' => '4000008', 'message' => $config['op'].': '.$cache_key.' = '.$mobile_validate ));
        }

        return $res && $res_m ? true : false;
    }

    /**
     * ��ȡ�ֻ���֤��
     *
     * @param  $mobile
     * @return
     */
    public static function get_validate_mobile( $mobile ) {
        return Helper::getRedis()->get(self::$moblieKey.':'.$mobile);
    }

    /**
     * �������6λ����
     * @return string
     */
    public  static function randNumber(){
        //������λ�����
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
     * ���Ͷ���
     */
    public static function send_sms( $mobile, $msg )
    {
        $return = false;
        $start_time = microtime(true);
        $use_time = 0;
        $result = '';

        $params = '';
        $argv = array(
            'name'=>$GLOBALS['config']['sms']['sp_username'],     //����������û��˺�
            'pwd'=>$GLOBALS['config']['sms']['sp_userpwd'],     //�����������webƽ̨�����������еĽӿ����룩
            'content'=>$msg,   //����������������ݣ�1-500 �����֣�UTF-8����
            'mobile'=>$mobile,   //����������ֻ����롣�����Ӣ�Ķ��Ÿ���
            'stime'=>'',   //��ѡ����������ʱ�䣬��дʱ����д��ʱ�䷢�ͣ�����ʱΪ��ǰʱ�䷢��
            'sign'=>$GLOBALS['config']['sms']['sp_sign'],    //����������û�ǩ����
            'type'=>'pt',  //����������̶�ֵ pt
            'extno'=>''    //��ѡ��������չ�룬�û�������չ�룬ֻ��Ϊ����
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
                #д����ˮ
                Log::save( array('key' => '4000009', 'message' => " [{$errno}]".   $result ) );

            }
        }catch( \Exception $e ){
            Log::save( array('key' => '4000009', 'message' => $e->getMessage() ) );
        }

        return $return;
    }

    /**
     * ������֤����
     *
     * @param  $config
     * @return
     */
    public static function echo_validate_mail( $config = array() ) {
        $to = $config['to_mail'];
        $subject = '�һ����� ��֤��';
        $body = '';

        switch( $config['op'] ){
            case 'bindmail':
            case 'old_bindmail':
                $subject = '������ ��֤��';
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

            //�����ʼ�
            $body = sprintf("��֤��Ϊ��%s��������֤���ṩ�����ˡ�", $validate). self::SIGN;
            $res = '';//send_email($to,$subject, $body);
            if( $res ){

                // ������֤��
                $res_m = Helper::getRedis()->set(self::$mailKey.':'.$config['to_mail'],$validate);
              //  $res_m = pub_memcache::set('O_501', $cache_key, $validate);
                // ����ģʽ ��¼��֤��
                Log::save(array('key' => '4000008', 'message' => $config['op'].': '. $cache_key.' = '.$validate ));
                if( !$res_m ){
                    // log
                    Helper::throwMessage('��֤�뱣��ʧ��');
                }
            }
            else{
                Helper::throwMessage('�ʼ�����ʧ��');
            }
        }catch( \Exception $e ){
            Log::save( array('key' => '4000008', 'message' => $e->getMessage() ));
        }

        return $res;
    }

    /**
     * ��ȡ������֤��
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