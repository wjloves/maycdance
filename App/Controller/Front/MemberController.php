<?php
namespace App\Controller\Front;


use App\Models\User;
use App\Models\UserDetail;
use App\Service\Helper\Helper;
use Core\Controller;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Captcha\Captcha;

/**
 * @description   个人资料主控类
 *
 *
 * */
class MemberController extends BaseController
{

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


    /**
     * 用户基本信息修改提交
     * @return Response
     */
    public function index(){
        if( isset($_POST['isedit']) && !empty($_POST['isedit'])){
            $data = $this->request->get('data');
            $state = UserDetail::userDetailEditByUid($data['user_id']);
            if($state){
                Helper::setRedisByArray(self::$cache_key_userDetail_one,$data['user_id'],$data);
                exit( json_encode(array('status'=>true)) );
            }else{
                exit( json_encode( Helper::throwMessage( 90005 )) );
            }
        }
        //获取省市地区数组
        $location = Helper::getRedis()->get('location');
        if(!$location){
            include_once(__CONFIG__.'/location.php');
            $location =  $GLOBALS['config']['location'];
            Helper::getRedis()->set('location',$location);
        }
        $data['location'] = $location;
        $data['user'] = $this->userInfo;
        return $this->render('Front/Member/index',$data);
    }

    public function avatarUpload(){
        $data = array();
        $tmpPath =  __UPLOAD__.'tmp/';
        $avatarPath =  __UPLOAD__.'avatar/';
        //临时上传文件
        if( isset($_POST) && $_SERVER['REQUEST_METHOD'] == "POST" ){
            $extArr = array("jpg", "png", "gif");
            $name = $_FILES['photoimg']['name'];
            $size = $_FILES['photoimg']['size'];
            //条件判断
            if(empty($name)){
                exit('请选择要上传的图片');
            }
            $ext = Helper::extend($name);
            if(!in_array($ext,$extArr)){
                exit('图片格式错误！');
            }
            if($size>(100*1024)){
               exit('图片大小不能超过100KB');
            }
            //判断目录是否存在
            if(!is_dir($tmpPath)) {
                mkdir($tmpPath,777,true);
            }

            $image_name = md5($this->userInfo['user_id']).".".$ext;
            $tmp = $_FILES['photoimg']['tmp_name'];
            //判断文件是否重名
            if(file_exists($tmpPath.$image_name)){
                unlink($tmpPath.$image_name);
            }
            //临时上传
            if(move_uploaded_file($tmp, $tmpPath.$image_name)){
                echo '<img src="'.$tmpPath.$image_name.'"  class="preview" id="tmpImage">';
            }else{
                echo '上传出错了！';
            }
            exit;
        }

        //用户点击修改文件
        if( isset($_GET['isReal']) && !empty($_GET['isReal'])){
            //获取名称
            $tmpfile = $this->request->get('filename');
            $tmparr = explode('/',$tmpfile);
            $filename = array_pop($tmparr);

            //文件和目录判断
            if(!file_exists($tmpfile)){
                exit('请先上传图片');
            }
            if(!is_dir($avatarPath)) {
                mkdir($avatarPath,777,true);
            }
            if(file_exists($avatarPath.$filename)){
                unlink($avatarPath.$filename);
            }
            //真实上传
            if(rename($tmpfile,$avatarPath.$filename)){
                exit('修改成功');
            }else{
                exit('修改失败');
            }
        }
        return $this->render('Front/Member/index',$data);
    }


}