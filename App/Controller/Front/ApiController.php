<?php
namespace App\Controller\Front;

use App\Service\Helper\Helper;
use Core\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Api\ApiResource;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Helper\Log;

/**
 * @description
 * */
class ApiController extends Controller
{
    private  static $oldMethods = array(
        'get.userInfos',  //获取用户信息
        'get.userFaces',
        'get.userNames',
        'get.userLoginNum',
        'get.searchUser',
        'checkUser',
        'checkUserCaptcha',
        'updateGame',
        'updatePwd',
        'updateResetPwd',
        'bindMobile',
        'bindMail',
        'registerPassport',
        'p3pLogin',
        'p3pExit'
        );
    /**
     * @description
     */
    public  function  pipeLine(){
        $a = $this->container->make('db');
        $action  =   $this->request->get('api_method');
        $result = ApiResource::InitRoute($action);
        Log::save(array('key' => '4000010', 'message' => $action.json_encode($result)));
//        if($result instanceof Response){
//            return $result;
//        }
        return new JsonResponse($result);

    }

}
