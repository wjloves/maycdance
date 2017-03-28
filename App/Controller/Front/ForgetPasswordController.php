<?php
namespace App\Controller\Front;
use Core\Controller;
use App\Service\Helper\Helper;
use App\Exception\UserException;
use App\Service\DataDriver\UserDriver;
use App\Service\DataDriver\UserDetailDriver;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Helper\Log;
use App\Service\Helper\Mobile;
/**
 * @description 前台忘记密码页面显示 ，密码找回，重置密码动作
 * @Author  Jarvis
 * @history  Create 2016/3/7
 */

class ForgetPasswordController extends Controller
{
    protected static $cache_key_forget_password = 'O_505';                 //REDI存储forge_password信息键
    protected static $cache_expire_forget_password = 1800;                 //有效期 30 分钟
    protected static $cache_key_captcha_moblie = 'Mobile:0_502';           //REDI存储Mobile验证码信息键
    protected static $cache_key_captcha_email  = 'Mail:O_501';             //REDI存储Email验证码信息键

    /**
     * @description 展示忘记密码页面
     * @return JsonResponse
     * @Author  Jarvis
     * @history  Create 2016/3/9
     * */
    public function index()
    {
        try {
            //使用时间字符串作为Redis存储标记
            $pageStartTime = microtime(true);
            $sysParam = array('_t' => $pageStartTime);

            //检查是否存在跳转标记
            $sysParam['goto'] = $this->container->request->get('goto');
            $sysParam['goto'] = empty($sysParam['goto']) ? (empty($_SERVER['HTTP_REFERER']) ? MYAPI_URL_MY : $_SERVER['HTTP_REFERER']) : $sysParam['goto'];

            //记录Redis
            Helper::getRedis()->delete(self::$cache_key_forget_password . ':' . $pageStartTime);
            Helper::setRedisByArray(self::$cache_key_forget_password, $pageStartTime, array('step' => 1, 'uid' => ''));
            Helper::getRedis()->expire(self::$cache_key_forget_password . ':' . $pageStartTime,self::$cache_expire_forget_password);

            return $this->render('/Front/findPassword', $sysParam);
        } catch (\Exception $e) {
            Helper::logException($e);
            $response = Helper::getResponseData(999, 'unknown');
            return JsonResponse::create($response , 400 );
        }
    }


    /**
     * @description 获取验证码
     * @input string _t 时间标识
     * @return JsonResponse
     * @Author  Jarvis
     * @history  Create 2016/3/9
     * */
    public function getValidate()
    {
        //传递参数
        $sysParam = array('_t' => $this->container->request->get('_t'));

        $pageParam = array(
            'username' => $this->container->request->get('username'),
            'validate' => $this->container->request->get('validate'),
            'verifier_flag' => $this->container->request->get('verifier_flag'),
        );

        $sysParam['verifier_flag'] = $pageParam['verifier_flag'];

        try {
            if (empty($sysParam['_t'])) {
                return  $this->Orequest('forgetpwd_index');
            }

            //确认找回密码步骤是否正确
            $redisParam = Helper::getRedis()->hGetAll(self::$cache_key_forget_password . ':' . $sysParam['_t']);
            if (intval($redisParam['step']) != 1) {
                return  $this->Orequest('forgetpwd_index');
            }
            $redisParam['step'] = 2;

            // 检查验证码
            $session_captcha = Helper::getSession()->get('CAPTCHA_KEY');
            if (empty($session_captcha) || empty($pageParam['validate']) || strtolower($pageParam['validate']) != strtolower($session_captcha)) {
                throw new UserException(Helper::translate(90003));
            }

            //检查user_name类型
            $webUsernameFlag = 'user_name';
            if (Helper::emailFilter($pageParam['username'])) {
                $webUsernameFlag = 'email';
            } elseif (Helper::mobileFilter($pageParam['username'])) {
                $webUsernameFlag = 'mobile';
            }

            //获取用户信息
            $user_info = UserDriver::getUserInformation($pageParam['username'], $webUsernameFlag);
            if (empty($user_info)) {
                throw new UserException(Helper::translate(20030));
            }
            $redisParam['uid'] = $user_info['user_id'];
            $sysParam['user']['user_name'] = $user_info['user_name'];

            if ('email' == $pageParam['verifier_flag']) {
                //获取绑定邮箱
                $UserDetailInfo = UserDetailDriver::getUserDetailInformation($user_info['user_id'], array('pub_email'));
                if (!empty($UserDetailInfo)) {
                    $sysParam['user']['show_mail'] = Helper::getSafeEmail($UserDetailInfo['pub_email']);
                    $redisParam['pub_email'] = $UserDetailInfo['pub_email'];
                } else {
                    throw new UserException(Helper::translate(90018));
                }
            } elseif ('mobile' == $pageParam['verifier_flag']) {
                if (!empty($user_info['mobile'])) {
                    $sysParam['user']['show_mobile'] = Helper::getSafeMobile($user_info['mobile']);
                    $redisParam['mobile'] = $user_info['mobile'];
                } else {
                    throw new UserException(Helper::translate(90019));
                }
            } else {
                throw new UserException(Helper::translate(90003));
            }

            Helper::setRedisByArray(self::$cache_key_forget_password, $sysParam['_t'], $redisParam);

            return $this->render('/Front/findPassword2' ,$sysParam);
        }
        catch(UserException $ue)
        {
            //Helper::logException($ue);
            //exit( json_encode( Helper::throwMessage(20021)) );
            return new JsonResponse(array('status'=>0,'message'=>$ue->getMessage()));
        }
        catch(\Exception $e)
        {
            //Helper::logException($e);
            $response = Helper::getResponseData(999, 'unknown');
            return JsonResponse::create($response , 400 );
        }
    }


    /**
     * @description 安全认证
     * @return JsonResponse
     * @input string _t 时间标识
     * @input string validate 验证码
     * @Author  Jarvis
     * @history  Create 2016/3/10
     * */
    public function safetyLegalize()
    {
        //获取参数
        $sysParam = array( '_t'=>  $this->container->request->get('_t'));
        $pageParam = array(
            'validate' => $this->container->request->get('validate'),
            'verifier_flag' => $this->container->request->get('verifier_flag'),
        );

        try
        {
            if( empty($sysParam['_t'])){
                return  $this->Orequest('forgetpwd_index');
            }

            //确认找回密码步骤是否正确
            $redisParam = Helper::getRedis()->hGetAll(self::$cache_key_forget_password . ':' .$sysParam['_t']);
            if (intval($redisParam['step']) != 2) {
                return  $this->Orequest('forgetpwd_index');
            }

            if (empty($pageParam['verifier_flag'])) {
                throw new UserException(Helper::translate(90006));
            }

            // 获取验证码
            if( 'email' == $pageParam['verifier_flag']) {
                $cacheKey = self::$cache_key_captcha_email . ':' . $redisParam['pub_email'];
            } else {
                $cacheKey = self::$cache_key_captcha_moblie . ':' . $redisParam['mobile'];
            }
            $publicCaptcha = Helper::getRedis()->get($cacheKey);

            //删除Redis验证码存储
            Helper::getRedis()->del($cacheKey);

            //对比验证码
            if( empty($publicCaptcha) || empty($pageParam['validate']) || strtolower($pageParam['validate']) != strtolower($publicCaptcha) )
            {
                throw new UserException(Helper::translate(20011));
            }

            //设置步骤 标识
            Helper::getRedis()->hSet(self::$cache_key_forget_password . ':' .$sysParam['_t'], 'step' , 3);

            return $this->render('/Front/findPassword3' ,$sysParam);
        }
        catch(UserException $ue)
        {
            Helper::logException($ue);
            return new JsonResponse(array('status'=>0,'message'=>$ue->getMessage()));
        }
        catch(\Exception $e)
        {
            Helper::logException($e);
            $response = Helper::getResponseData(999, 'unknown');
            return JsonResponse::create($response , 400 );
        }
    }


    /**
     * @description 密码更新
     * @input string _t 时间标识
     * @return JsonResponse
     * @Author  Jarvis
     * @history  Create 2016/3/10
     * */
    public function updatePassword()
    {
        //获取参数
        $sysParam = array('_t' => $this->container->request->get('_t'));
        $webUserInfo = array(
            'new_password' => $this->container->request->get('new_password'),
            'confirm_password' => $this->container->request->get('confirm_password')
        );

        try {
            if (empty($sysParam['_t'])) {
                return  $this->Orequest('forgetpwd_index');
            }

            //确认找回密码步骤是否正确
            $cacheForgetPwd = Helper::getRedis()->hGetAll(self::$cache_key_forget_password . ':' . $sysParam['_t']);

            //删除存储Redis $cache_key_forget_password
            Helper::getRedis()->delete(self::$cache_key_forget_password . ':' . $sysParam['_t']);

            if (intval($cacheForgetPwd['step']) != 3) {
                return  $this->Orequest('forgetpwd_index');
            }

            //确认密码正确
            if (empty($webUserInfo['new_password']) || ($webUserInfo['new_password'] != $webUserInfo['confirm_password'])) {
                throw new UserException(Helper::translate(20002));
            }

            //检查密码长度
            if (!Helper::pwdLengthCheck($webUserInfo['new_password'])) {
                throw new UserException('抱歉，密码长度在4-32位');
            }

            //更新密码
            $dbUserInfo = UserDriver::getUserInformation($cacheForgetPwd['uid']);
            if ($dbUserInfo['passwd'] != Helper::enPasswd($webUserInfo['new_password'])) {
                $result = UserDriver::updateUserInfo($cacheForgetPwd['uid'], array('passwd' => $webUserInfo['new_password']));
                if ($result) {
                    Log::save(array('key' => '2000006', 'message' => ' forget_pwd ' . var_export(array('uid' => $cacheForgetPwd['uid']), true)));
                    return $this->render('/Front/findPassword4' ,$sysParam);
                } else {
                    throw new UserException(Helper::translate(90005));
                }
            } else {
                throw new UserException(Helper::translate(20007));
            }

        } catch (UserException $ue) {
            Helper::logException($ue);
            return new JsonResponse(array('status' => 0, 'message' => $ue->getMessage()));
        } catch (\Exception $e) {
            Helper::logException($e);
            $response = Helper::getResponseData(999, 'unknown');
            return JsonResponse::create($response, 400);
        }
    }
    /**
     * @description 请求邮箱验证码
     * @return JsonResponse
     * @Author  Jarvis
     * @history  Create 2016/3/12
     * */
    public function emailCaptcha()
    {
        $sysParam = array('_t' => $this->container->request->get('_t'));

        try {
            if (empty($sysParam['_t'])) {
                return $this->Orequest('forgetpwd_index');
            }

            $email = Helper::getRedis()->hGet(self::$cache_key_forget_password . ':' . $sysParam['_t'], 'pub_email');
            if (empty($email)) {
                throw new UserException(Helper::translate(90006));
            }

            //获取验证码
            $email_validate = Mobile::randNumber();
            //邮件标题
            $subject = '亲爱的用户，您获取到的验证码如下：';
            //邮件内容
            $message = sprintf("您此次的验证码为：%s，请勿将验证码提供给他人。", $email_validate);

            Helper::sendMail($subject, $message, $email);

            //存储验证码
            $cacheKey = self::$cache_key_captcha_email . ':' . $email;
            $res_m = Helper::getRedis()->set($cacheKey, $email_validate);
            Helper::getRedis()->expire($cacheKey, 300);  //有效期 5 分钟
            if (!$res_m) {
                throw new UserException(Helper::translate(90005));
            }
        } catch (UserException $ue) {
            Helper::logException($ue);
            return new JsonResponse(array('status' => 0, 'message' => $ue->getMessage()));
        } catch (\Exception $e) {
            Helper::logException($e);
            $response = Helper::getResponseData(999, 'unknown');
            return JsonResponse::create($response, 400);
        }
    }


    /**
     * @description 请求电话验证码
     * @input mobile
     * @return JsonResponse
     * @Author  Jarvis
     * @history  Create 2016/3/12
     * */
    public function mobileCaptcha()
    {
        $sysParam = array('_t' => $this->container->request->get('_t'));
        try {
            if (empty($sysParam['_t'])) {
                return $this->Orequest('forgetpwd_index');
            }

            $config['mobile'] = Helper::getRedis()->hGet(self::$cache_key_forget_password . ':' . $sysParam['_t'], 'mobile');
            if (empty($config['mobile'])) {
                throw new UserException(Helper::translate(90006));
            }
            $result = Mobile::echo_validate_mobile($config);
            if (!$result) {
                throw new UserException(Helper::translate(90005));
            }
        } catch (UserException $ue) {
            Helper::logException($ue);
            return new JsonResponse(array('status' => 0, 'message' => $ue->getMessage()));
        } catch (\Exception $e) {
            Helper::logException($e);
            $response = Helper::getResponseData(999, 'unknown');
            return JsonResponse::create($response, 400);
        }
    }

}


