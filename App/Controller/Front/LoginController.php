<?php

namespace App\Controller\Front;

use App\Models\User;
use App\Service\Helper\Helper;
use Core\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Helper\Log;
use App\Service\Helper\Mobile;
use App\Service\DataDriver\UserDriver;
class LoginController extends BaseController
{
    protected $dml_flag = 3;

    /**
     * 登录页面
     *
     * @return VIEW
     */
    public function login( $user = array() ){

        //判断是否登录
        if(!$this->checkLogin()){
            $data = array();
            $hiddenCode = md5(date('Y-m-d H'));
            $data['hiddenCode'] = $hiddenCode;
            if( ( isset($_POST['hiddenCode']) && !empty($_POST['hiddenCode']) ) ||  !empty($user) ){
                $login = $this->request->get('login');
                $username = $this->request->get('username');
                $password = $this->request->get('password');
                $_HiddenCode = $this->request->get('hiddenCode');
                $userdata = User::getUserByName($login['username']);
                if(!empty($userdata)){
                    if( $userdata['passwd'] === Helper::enPasswd($password) ){
                        $_login = array(
                            'username'=>$userdata['user_name'],
                            'uid'=>$userdata['user_id'],
                            '_loginTime'=>time()
                        );
                        return $this->setUserAndJump($_login,$userdata);
                    }else{
                        exit( json_encode( Helper::throwMessage(20021)) );
                    }
                }else{
                    exit( json_encode( Helper::throwMessage(20021)) );
                }
            }
            
            return $this->render('Front/login',$data);
        }

        return  $this->Orequest('front_index');
    }


    /**
     * 用户退出
     * @return RedirectResponse
     */
    public  function loginOut(){
        $_SESSION['login']=null;
        unset($_SESSION);
        setcookie('PHPSESSID','');
        return $this->Orequest('adminlogin');
    }

    /**
     * @description  用户注册
     * */
    public function register(){
        $data = array();
        if($this->checkLogin()){
            return $this->Orequest('front_index');
        }
        return $this->render('Front/register',$data);
    }

    /**
     * 设置用户信息并登录
     * @param $login
     * @param $user
     * @return RedirectResponse
     */
    private  function  setUserAndJump($login,$user){
        //设置cookie和session信息
        Helper::getSession()->set('user',$login);
        Helper::setLoginCookie($user['user_name'],$user);
        return  $this->Orequest('front_index');
    }

    /**
     * 注册提交
     * @return void
     */
    public function register_user(){
        $data = Helper::getContainer()->request->get('data');
        $op = '';
        try {
            // mobile reg
            if( !empty( $data['mobile'] ) && Helper::mobileFilter( $data['mobile'] ) ){
                $op = 'mobile';
            }

            $detail = array(
                'reg_ip'=> Helper::getUserIp(),
                'reg_app'=>'u'
            );

            // 注册
            switch( $op ){
                case 'mobile': // mobile reg
                    // 手机验证码
                    $mobileValidate = intval($data['mobileValidate']);
                    $mobileCkstr = Mobile::get_validate_mobile($data['mobile']);

                    if( empty($mobileCkstr) || empty($mobileValidate) || $mobileValidate != $mobileCkstr ) {
                        exit( json_encode( Helper::throwMessage(20012)) );
                    }
                    unset($data['mobileValidate']);
                    $data['mobile'] = $data['user_name'];
                    $statuse = User::checkUser($data);
                    if($statuse){
                        exit(json_encode( Helper::throwMessage( 20006 )));
                    }
                    $detail[ 'mobile_verify'] = 1;
                    $return = UserDriver::saveUser($data,$detail);
                    break;
                default: // user name reg
                    $return = UserDriver::saveUser($data,$detail);
            }


            if( $return['status'] ){
                try{
                    // 登录
                    $user = User::getUserById($return['user_id']);
                    $_login = array(
                        'username'=>$user['user_name'],
                        'uid'=>$user['user_id'],
                        '_loginTime'=>time()
                    );
                    $this->setUserAndJump($_login,$user);
                }
                catch( \Exception $e){
                    // 登录失败日志
                    exit( json_encode( Helper::throwMessage(20024)) );
                }

            }

        }
        catch( \Exception $e) {
            exit(json_encode( Helper::throwMessage( 20009 )));
            //$return['msg'] = $e->getMessage();
        }
    }
}
