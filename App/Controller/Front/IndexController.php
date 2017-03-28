<?php
namespace App\Controller\Front;


use App\Service\Helper\Helper;
use Illuminate\Container\Container;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @description 前台的登录页面显示 ， 登录动作 ，
 * 
 *
 * */
class IndexController extends BaseController
{
    /**
     * @description 展示登录页面
     * @return Response
     * */
    /**
     * 用户中心需要的信息初始化
     *
     * @return JsonResponse
     */
    public function __init__()
    {
        // 调用顺序一定是这样的
        parent::__init__();

    }

    public function index(){
        $data = array();
        return $this->render('Front/index',$data);
    }
}