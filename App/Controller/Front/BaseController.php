<?php

namespace App\Controller\Front;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserScore;
use Core\Controller;
use App\Service\Helper\Helper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Captcha\Captcha;

class BaseController extends Controller
{
    protected $dml_flag = 3;
    protected $user_online = false;
    public static $cache_key_userDetail_one = 'UD_101';
    public static $cache_key_user_one = 'U_101';
    protected $userInfo; // 在线用户的信息
    protected  $timeOut = 3600;

    /**
     *  检测用户是否登录，把用户信息放在公用的存储位置
     */
    public  function  __init__(){
        $data = array();
        if( $this->checkLogin() ){
            //用户基本信息
            $userInfo =  Helper::getRedis()->hGetAll(self::$cache_key_user_one.':'.$this->user_online);
            if(!$userInfo){
                $userInfo = User::getUserById($this->user_online);
                Helper::setRedisByArray(self::$cache_key_user_one,$this->user_online,$userInfo);
            }
            //用户详细信息
            $userdetails = UserDetail::getUserByUid($this->user_online);
            if(!$userdetails){
                $userInfo = UserDetail::getUserByUid($this->user_online);
                Helper::setRedisByArray(self::$cache_key_userDetail_one,$this->user_online,$userInfo);
            }
            //用户积分信息
             $source  = UserScore::getUserByUid($this->user_online);
             Helper::getRedis()->hSet(self::$cache_key_user_one.':'.$this->user_online,'silver_balance',$source['silver_balance']);
             $userInfo['silver_balance']  = $source['silver_balance'];
             $userInfo = array_merge($userInfo,$userdetails);
             $this->userInfo = $userInfo;
             $data['userInfo'] = $userInfo;
        }
         $this->assign($data);
    }

    /**
     * 检查用户
     * $request,$next
     * @return JsonResponse
     */

    public function checkLogin(){
        $login = Helper::getSession()->get('user');
        if (empty($login) || empty($login['uid'])) {
           return $this->user_online;
        }else{
            $this->user_online = $login['uid'];
        }
        return $this->user_online;

    }

    /**
     * 获取用户登录信息
     * @return array
     */
    protected  function getUserMessage(){
        $login = Helper::getSession()->get('login');
        return  $login;
    }


    /**
     * 未登录的 跳转至登录页面
     *
     * @param $request
     * @param $next
     * @return RedirectResponse
     */
    public function notLogin($request, $next){
        if (!$this->userInfo) {
            return  $this->Orequest('front_login');
        }
        return $next($request);
    }


    /**
     * [captcha 公用验证码调用方法]
     *
     * @author  挪用DC的 有问题找他
     * @version
     * @return  image/png
     */
    public function captchaV(){
        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="' .Captcha::Generate(). '"');
        return new Response(uniqid() . '.png', 200, $headers);
    }

    /**
     * 验证码方法
     * 获取输出图形验证码
     * @author 挪用和修改DC原方法  有问题找他
     * @return array
     */
    public static function captcha(){
        Captcha::$height = 28;
        Captcha::$width = 90;
        $image = Captcha::Generate();
        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="' . $image . '"');
        Helper::getSession()->set('CAPTCHA_KEY',Captcha::Phrase());
        $result = array(Captcha::Phrase() . '.png', 200, $headers);
        return $result;
    }
}