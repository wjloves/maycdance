<?php

namespace App\Controller\Admin;

use App\Models\AdminUser;
use Core\Controller;
use App\Service\Helper\Helper;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseController extends Controller
{
    protected $dml_flag = 3;


    /**
     * 检查用户
     * @return JsonResponse
     */

    public function checkLogin($request,$next){
        $login = Helper::getSession()->get('login');
        if (empty($login) || empty($login['admin_id'])) {
            return $this->showMessage('请登录',"/admin/login",true);
        }
        $userdata = AdminUser::find($login['admin_id']);
        if ($userdata) {
            return $next($request);
        } else {
            return $this->showMessage('用户信息异常，请重新登录',"/admin/login",true);
        }
    }

    /**
     * 获取用户登录信息
     * @return array
     */
    protected  function getUserMessage(){
        $login = Helper::getSession()->get('login');
         return  $login;
    }

}