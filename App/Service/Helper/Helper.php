<?php
namespace App\Service\Helper;

use App\Exception\IllegalStrException;
use Illuminate\Container\Container;
use App\Exception\UserException;

class Helper
{
    public static $illegalChar = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','%','?','　');

    private static $online_cookie_name = MYAPI_COOKIE_ONLINE;  // online的cookie标志
    private static $cookie_login_expire = MYAPI_COOKIE_EXPIRE;        // cookie保存14天
    private static $cookie_domain = MYAPI_COOKIE_DOMAIN;      // AUTH COOKIE 域名
    private static $cookie_auth = MYAPI_COOKIE;           // 登录成功的COOKIE，保存user_id, user_name, email
    private static $sign_key = MYAPI_SIGN_KEY;
    private static $_encrypt_key = MYAPI_ENCRYPT_KEY;

    /**
     * @return Illuminate\Container\Container
     * */
    public static function getContainer()
    {
        return Container::getInstance();
    }

    /**
     * @param string $connection
     * @return \Redis
     * */
    public static function getRedis($connection = 'default')
    {
        return Container::getInstance()->redis->getClient($connection);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Session
     * */
    public static function getSession()
    {
        /**
         * @var \Symfony\Component\HttpFoundation\Session\Session $session
         * */
        $session = Container::getInstance()->request->getSession();
        return $session;
    }

    /**
     * 验证邮件地址
     * ??是否做进一步验证??
     * @param string $str
     * @return boolean
     */
    public static function email($str) {
        return preg_match('/^[a-z0-9]+([\+_\-\.]?[a-z0-9]+)*@([a-z0-9]+[\-]?[a-z0-9]+\.)+[a-z]{2,6}$/i', $str);
    }


    /**
     * @param \Exception $e
     * */
    public static function logException(\Exception $e)
    {

        if(self::getContainer()->config['config.debug'])
        {
            var_dump($e ->getMessage() , $e->getFile() , $e->getLine() ,$e->getTrace());
        }
        else
        {
            $message = $e->getMessage().$e->getFile().$e->getLine();
            $fp = fopen(BASEDIR.'/Cache/Logs/'.date("Ymd",time()).'.log', "a");
            flock($fp, 2) ;
            fwrite($fp,"记录日期：".date("Y-m-d H:i:s",time())."\n". $message ."\n");
            flock($fp, 3);
            fclose($fp);
        }
    }
    /**
     * @description 用于返回构建jsonResponse的array
     * */
    public static function getResponseData($code , $msg)
    {
        return  $response = array(
            "code"=>$code,
            "msg" => $msg,
//            "request" => __METHOD__." :".$_SERVER["QUERY_STRING"]
        );
    }

    /**
     * @description 将根据attr的建和值 对model进行赋值
     * */
    public static function setModelAttr(&$model , $attr)
    {
        foreach($attr as $key => $value)
        {
            $model->$key = $value;
        }
    }

    /**
     * 通过设定值  返回attr标准的数组
     * @param $data
     * @param $attr
     * @return array
     */
    public static function setFeildsAttr($data , $attr){
        $contion = array();
        foreach($data as $key => $val){
            foreach($attr as  $v){
                $contion[$key][$v] = $val[$v];
            }
        }
        return $contion;
    }


    public static function checkEmail($email)
    {
		$pattern = "/^[a-z]([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?$/i";
        if(empty($email) || preg_match($pattern, $email) === 0)
        {
            throw new IllegalStrException("email is Illegal");
        }
        if(self::getContainer()->dataDriver->existField(array('email'=>$email)) > 0 )
        {
            throw new UserException('email has exist ');
        }
    }

    public static function checkName($name)
    {
        $len = strlen($name) ;
        if($len > 12 || $len < 5  || str_replace(self::$illegalChar, '', $name) != $name)
        {
            throw new IllegalStrException('name is illegal');
        }
        if(self::getContainer()->dataDriver->existField(array('username'=>$name) )>0 )
        {
            throw new UserException('name has exist ');
        }
    }

    public static function checkPassword($password , $passwordRepeat)
    {
        $len = strlen($password);
        if(  $len < 6 || $len > 16 || md5($password) != md5($passwordRepeat) || str_replace(self::$illegalChar, '', $password) != $password )
        {
            throw new IllegalStrException('password is illegal');
        }
    }


    public static function sendMail($subject , $message , $toEmail)
    {
        /**
         * @var $email \App\Service\Helper\PHPMailer
         * */
        $email = self::getContainer()->mail;
        $email -> AddAddress($toEmail);
        $email->Subject = $subject;
        $email->MsgHTML($message);
        if(!$email -> Send()){
            throw new \Exception('send mail false');
        }
    }

    /**
     * 本地生成和my一样的cookie
     * @parem $account     登录时填写的email或用户名
     * @parem $data        用户信息
     * @parem $expire      过期时间
     * @parem $domain      域名
     * @return void
     */
    public static function setLoginCookie($account, $data, $save_sta = true, $domain = '')
    {
        $expire = $save_sta === true ? (time() + self::$cookie_login_expire) : 0;
        header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

        // COOKIE的数组
        $cookie_data = array($data['user_id'], rawurlencode($data['user_name']), $data['email'], $data['mobile'], time());
        $value = implode(':', $cookie_data);

        $cookie_domain = !empty($domain) ? $domain : self::$cookie_domain;

        // 签名
        $value .=":" . hash_hmac('ripemd256', md5($value . self::$sign_key), self::$sign_key);
        // 加密
        $value = self::_encrypt($value, self::$_encrypt_key);
        return setcookie(self::$cookie_auth, $value, $expire, '/', $cookie_domain);
    }

    /**
     *
     * 加密cookie,修改此方法时与cls_my_auth方法保持一致
     * @param string $txt
     * @param string $key
     * @return string 加密串
     */
    public  static  function _encrypt($txt, $key) {
        $key_md5 = md5($key);
        $encode = "";
        for($i = 0; $i < strlen($txt); $i++){
            $j = $i % strlen($key_md5);
            $encode .= $txt[$i] ^ $key_md5[$j];
        }
        return rawurlencode($encode);
    }

    /**
     * 删除当前登录站点的cookie
     * @parem $domain      域名
     * @return void
     */
    public static function delLoginCookie($domain = '') {
        $cookie_domain = !empty($domain) ? $domain : self::$cookie_domain;
        return setcookie(self::$cookie_auth, "", time() - 1, '/', $cookie_domain);
    }

    /**
     * 加密密码
     * @param $passwd
     * @return string
     */
    public static function enPasswd( $passwd ){
        return sha1( $passwd.ENCRYPT_KEY);
    }

    /**
     * 错误信息返回
     * @param $key
     * @param int $code
     * @throws \Exception
     */
    public  static function throwMessage($key,$code = false,$str = '') {
        $reback = array(
            'status'=>$code,
            'errCode'=>$key,
        );
        if(is_numeric($key)) {
            if (empty($str) || false === strpos(self::translate($key), '%')) {
                $reback['msg'] = self::translate($key);
            } else {
                //支持单个格式字符串替换 add by Jarvis
                $reback['msg'] = sprintf(self::translate($key),$str);
            }
        }else{
            $reback['msg'] = $key;
        }
        return $reback;
    }

    /**
     * 错误代码对应语言包
     *
     * @param string $key
     * @return string
     */
    public static function translate($key) {
        /* 公共提示 */
        $lang['90001'] = "登陆超时，请重新登陆。";
        $lang['90002'] = "抱歉，数据格式不正确。";
        $lang['90003'] = "抱歉，您的操作有误。";
        $lang['90004'] = "操作成功。";
        $lang['90005'] = "操作失败。";
        $lang['90006'] = "抱歉，参数错误。";
        $lang['90007'] = "抱歉，URL错误。";
        $lang['90008'] = "删除成功。";
        $lang['90009'] = "删除失败。";
        $lang['90010'] = "抱歉，您的操作未授权。";
        $lang['90011'] = "您确认要删除此记录吗。";
        $lang['90012'] = "抱歉，请选择记录。";
        $lang['90013'] = "抱歉，Email格式不正确。";
        $lang['90014'] = "抱歉，手机号码格式不正确。";
        $lang['90015'] = "抱歉，您的操作有误，请联系管理员。";
        $lang['90016'] = "抱歉，参数不能为空。";
        $lang['90017'] = '未绑定手机，未绑定邮箱';
        $lang['90018'] = '未绑定邮箱';
        $lang['90019'] = '未绑定手机';


        /* 用户 */
        $lang['20001'] = '抱歉，用户帐号有误，请检查。';
        $lang['20002'] = '抱歉，两次输入的密码不一致。';
        $lang['20003'] = '抱歉，密码不能为空。';
        $lang['20004'] = '抱歉，真实姓名必须为中文，如：张三。';
        $lang['20005'] = '抱歉，身份证号有误，如：120100198709093259。';
        $lang['20006'] = '此用户名已经被使用，换一个试试吧';
        $lang['20007'] = '抱歉，新密码和旧密码相同。';
        $lang['20008'] = '注册成功。';
        $lang['20009'] = '注册失败。';
        $lang['20010'] = '抱歉，相同的Email已经存在。';
        $lang['20011'] = '验证码错误，请在倒计时内输入正确的验证码';
        $lang['20013'] = '抱歉，原手机验证码错误。';
        $lang['20014'] = '抱歉，原邮箱验证码错误。';
        $lang['20012'] = '抱歉，您的手机号码错误。';
        $lang['20015'] = '抱歉，手机号码相同。';
        $lang['20016'] = '抱歉，Email地址相同。';
        $lang['20017'] = '查询不到该用户，请检测';
        $lang['20018'] = '抱歉，密码长度在4-16位';
        $lang['20019'] =  '抱歉，用户名长度在4-16位';



        $lang['20021'] = '抱歉，登录失败，用户名或密码错误。';
        $lang['20022'] = '抱歉，登录失败，用户名或密码错误。';
        $lang['20023'] = '抱歉，此帐号不允许登录；有疑问，请联系客服。';
        $lang['20024'] = '抱歉，登录失败，请重试。';
        $lang['20025'] = '登录失败，帐号已被锁定；请%d分后再试。';
        $lang['20026'] = '抱歉，未登出，请重试。';
        $lang['20027'] = '抱歉，登录同步失败。有疑问，请联系客服。';
        $lang['20028'] = '抱歉，密码错误。';

        $lang['20030'] = '抱歉，用户不存在。';
        $lang['20031'] = '抱歉，暂不支持的登录方式。';
        $lang['20032'] = '抱歉，IP已被禁止';


        /* 收藏 */
        $lang['21001'] = '您已收藏过本话题。';
        $lang['21002'] = '收藏成功。';
        $lang['21003'] = '收藏失败。';


        /* 用户    */
        $lang['50001'] = '抱歉，您的剩余积分不足。';
        $lang['50002'] = '抱歉，打赏失败。';

        $lang['50005'] = '抱歉，用户ID错误。';
        $lang['50006'] = '抱歉，您已被禁言至<b class="red">%s</b>，请尝试联系%s。';
        $lang['50007'] = '已经是解封的，您不需操作。';
        $lang['50008'] = '积分被冻结，操作失败。';
        $lang['50009'] = '您的申诉已提交，请耐心等待客服人员处理。';


        /* 银币  */
        $lang['80001'] = '抱歉，积分充值失败。';
        $lang['80002'] = '抱歉，积分增加失败。';
        $lang['80003'] = '抱歉，积分扣除失败。';

        $lang['80006'] = '抱歉，今天的积分已经有了。';
        $lang['80007'] = '抱歉，配置不存在。';
//        $lang['80008'] = '您不能购买自己的。';
//        $lang['80009'] = '您不能打赏给自己。';
        $lang['80010'] = '支付的积分为0。';
        $lang['80011'] = '消费的积分为0。';

        $lang['80020'] = '积分用户有误。';
        $lang['80021'] = '积分操作类型有误。';


        /* 其他  */
        $lang['11001'] = '抱歉，您今天的举报已经用完，明天再举报吧。';
        $lang['11002'] = '举报成功！';
        $lang['11003'] = '抱歉，举报失败！';
        $lang['11004'] = '抱歉，举报说明最多500字！';
        $lang['11010'] = '抱歉，上传失败！';
        $lang['11011'] = '错误的请求';
        $lang['94500'] = '数据格式错误';


        $args = func_get_args();
        $params = array_slice($args, 1);

        return isset($lang[$key]) ? (empty($params) ? $lang[$key] : vsprintf($lang[$key], $params)) : '';
    }

    /**
     * @description email过滤器,检查输入字符串是否为email格式
     * @param string $str
     * @return bool
     * @author Jarvis
     * @history Create 2016/02/27
     * */
    public static function emailFilter($str = '')
    {
        return filter_var($str, FILTER_VALIDATE_EMAIL);
    }


    /**
     * @description  mobile过滤器，检查输入字符串是否为电话号码格式
     * @param string $str
     * @return bool
     * @author Jarvis
     * @history Create 2016/02/27
     * */
    public static function mobileFilter($str = '')
    {
        return preg_match("/^[0]?(14[5|7]|17[0]|18[0-9]{1}|13[0-9]{1}|15[0-9]{1}+)(\d{8})$/", $str);
    }


    /**
     * @description  获得保护手机号
     * @param string $mobile
     * @return string
     * @author Jarvis
     * @history Create 2016/02/27
     */
    public static function getSafeMobile($mobile)
    {
        return substr($mobile, 0, 3) . '****' . substr($mobile, -4, 4);
    }

    /**
     * @description  获得保护邮箱
     * @param string $email
     * @return string
     * @author Jarvis
     * @history Create 2016/02/27
     */
    public static function getSafeEmail($email)
    {
        $tmp_email = explode('@', $email);
        return substr($tmp_email[0], 0, 2) . '***' . substr($tmp_email[0], -1, 1) . '@' . $tmp_email[1];
    }

    /**
     * @description  ID检查
     * @param string $id
     * @return bool
     * @author Jarvis
     * @history Create 2016/02/27
     */
    public static function idCheck($id)
    {
        return preg_match('/^[1-9]\d*$/', $id);
    }

    /**
     * @description  用户名检查
     * @param string $user_name
     * @return bool
     * @author Jarvis
     * @history Create 2016/02/28
     */
    public static function userNameCheck($user_name)
    {
        return preg_match('/^([a-z0-9_]{4,16})$/i', $user_name);
    }


    /**
     * @description  密码长度检查
     * @param string $password
     * @return bool
     * @author Jarvis
     * @history Create 2016/03/10
     */
    public static function pwdLengthCheck($password)
    {
        if(strlen($password)>32 || strlen($password)<4){
            return false;
        } else {
            return true;
        }
    }


    /**
     * @description  合成动态设置里的安全级别
     * @param array $param
     * @return array
     * @author Jarvis
     * @history Create 2016/02/27
     */
    public static function setFeedPrivacy( $param )
    {
        $feed_privacy = array('user_safe_level ' => 0, 'pwd_safe_level' => 0);

        // 密码安全级别
        if (!empty($param['passwd'])) {
            if (preg_match('/^\d+$/', $param['passwd']) || preg_match('/^[a-zA-Z]+$/', $param['passwd']) || preg_match('/^[^\d,a-z,A-Z]+$/', $param['passwd'])) {
                $feed_privacy['pwd_safe_level'] = 1;
            } elseif (preg_match('/^[a-zA-Z\d]+$/', $param['passwd']) || preg_match('/^[^\d]+$/', $param['passwd']) || preg_match('/^[^a-zA-Z]+$/', $param['passwd'])) {
                $feed_privacy['pwd_safe_level'] = 2;
            } else {
                $feed_privacy['pwd_safe_level'] = 3;
            }
        }

        // get_one 初始数据
        if (empty($param)) {
            $feed_privacy = array('user_safe_level' => 0, 'pwd_safe_level' => 1);
        }

        return serialize($feed_privacy);
    }



    /**
     * @description  获取客户端IP地址
     * @return  string
     * @author Jarvis
     * @history Create 2016/02/27
     */
    public static function getClientIp ()
    {
        static $real_ip = NULL;
        if ($real_ip !== NULL) {
            return $real_ip;
        }
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR2'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR2']);
                /* 取X-Forwarded-For2中第?个非unknown的有效IP字符? */
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if ($ip != 'unknown') {
                        $real_ip = $ip;
                        break;
                    }
                }
            } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                /* 取X-Forwarded-For中第?个非unknown的有效IP字符? */
                foreach ($arr as $ip) {
                    $ip = trim($ip);
                    if ($ip != 'unknown') {
                        $real_ip = $ip;
                        break;
                    }
                }
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $real_ip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                if (isset($_SERVER['REMOTE_ADDR'])) {
                    $real_ip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $real_ip = '0.0.0.0';
                }
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR2')) {
                $real_ip = getenv('HTTP_X_FORWARDED_FOR2');
            } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
                $real_ip = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_CLIENT_IP')) {
                $real_ip = getenv('HTTP_CLIENT_IP');
            } else {
                $real_ip = getenv('REMOTE_ADDR');
            }
        }
        preg_match('/[\d\.]{7,15}/', $real_ip, $online_ip);
        $real_ip = !empty($online_ip[0]) ? $online_ip[0] : '0.0.0.0';
        return $real_ip;
    }

    /**
     * @description  解析admin_ip_limit.xml限制IP
     * @return array
     * @author Jarvis
     * @history Create 2016/02/27
     */
    public static function getXmlLimitIpList ()
    {

        $ip_limit_xml_file_data = '';
        $ip_limit_xml_file = BASEDIR . '/App/Config/admin_ip_limit.xml';
        if (is_file($ip_limit_xml_file)) {
            $ip_limit_xml_file_data = file_get_contents($ip_limit_xml_file);
        }

        $limit_xml_ip_list = array();

        if (!empty($ip_limit_xml_file_data))
        {
            preg_match('/<ip>(.*?)<\/ip>/isU', $ip_limit_xml_file_data, $result);
            if (!empty($result[1])) {
                $ip_str1 = str_replace(' ', '', $result[1]);
                $ip_list1 = explode(',', $ip_str1);

                $ip_str2 = preg_replace('/(\r\n|\n|\r)/', ',', $ip_str1);
                $ip_list2 = explode(',', $ip_str2);

                $ip_list = count($ip_list1) > count($ip_list2) ? $ip_list1 : $ip_list2;
                $ip_list = array_unique($ip_list);

                foreach ($ip_list as $key => $value) {
                    $ip_list[$key] = trim($value);
                    if (empty($ip_list[$key])) {
                        unset($ip_list[$key]);
                    }
                }
                $limit_xml_ip_list = array_values( $ip_list );
            }
        }
        return $limit_xml_ip_list;
    }


    /**
     * 遍历设置redis缓存
     * @param $key    前缀
     * @param $id     参数
     * @param array $data 值
     */
    public static function setRedisByArray($k, $id, $data = array())
    {
        foreach ($data as $key => $val) {
            $state[] = self::getRedis()->hSet($k . ':' . $id, $key, $val);
        }

    }


    /**
     * 获取用户请求IP
     * @return string
     */
    public static function getUserIp()
    {
        $onlineip = '';
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $onlineip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $onlineip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $onlineip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $onlineip = $_SERVER['REMOTE_ADDR'];
        }
        return $onlineip;

    }

    //获取文件类型后缀
   public static function extend($file_name){
        $extend = pathinfo($file_name);
        $extend = strtolower($extend["extension"]);
        return $extend;
    }
}
