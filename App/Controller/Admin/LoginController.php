<?php

namespace App\Controller\Admin;

use App\Models\AdminUser;
use App\Service\Helper\Helper;
use Core\Controller;
use App\Controller\Admin\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    protected $dml_flag = 3;
    /**
     * 认证平台后台登录页面管理
     *
     * @return VIEW
     */
    public function login(){
        //判断是否登录

        //生成隐藏验证码
        $data = array();
        $hiddenCode = md5(date('Y-m-d H'));
        $data['hiddenCode'] = $hiddenCode;
        
        if(isset($_POST['hiddenCode']) && !empty($_POST['hiddenCode'])){
            $username = $this->request->get('username');
            $password = $this->request->get('password');
            $_HiddenCode = $this->request->get('hiddenCode');

            $userdata =AdminUser::where('username',$username)->first();
            if(!empty($userdata)){
                if( $userdata->passwd === md5(base64_encode(md5($password))) ){
                    $_login = array(
                        'username'=>$username,
                        'admin_id'=>$userdata->admin_id,
                        '_loginTime'=>time()
                    );
                    Helper::getSession()->set('login',$_login);
                    return  $this->Orequest('admin_index');
                    //  return new RedirectResponse($this->container->make('url')->route('admin_index'));
                }else{
                    return $this->showMessage('密码错误','/admin/login',true);
                }
            }else{
                return $this->showMessage('用户错误','/admin/login',true);
            }

        }
        return $this->render('Admin/login',$data);
    }



    public  function loginOut(){
        $_SESSION['login']=null;
        unset($_SESSION);
        setcookie('PHPSESSID','');
        return $this->Orequest('adminlogin');
    }

}
