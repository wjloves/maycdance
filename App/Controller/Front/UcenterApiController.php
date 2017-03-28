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
class UcenterApiController extends Controller
{

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
