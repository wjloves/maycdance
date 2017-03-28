<?php
namespace App\Models;
use App\Service\Helper\Helper;
use DB;

class UserDetail extends \Illuminate\Database\Eloquent\Model
{
    protected  $table='user_details';
    protected $primaryKey = 'user_id';
    protected $fillable = ['user_id', 'reg_ip', 'reg_app','reg_time'];
    public static $unguarded = true;
    public $timestamps = false;


    /**
     * 批量获取用户数据
     * @param $uIds
     * @return mixed
     */
    public static  function  getUsersDetails( $uIds ){
        return self::whereIn('user_id',$uIds)->paginate(200);
    }

    /**
     * 获取单一用户数据
     * @param $uId
     * @return mixed
     */
    public  static  function  getUserByUid( $uId ){
        $res  = self::where('user_id',$uId)->first();
        if($res){
            return $res->toArray();
        }else {
            return false;
        }
    }

    /**
     * 根据用户uId更新用户详细信息
     * @param $uId
     * @param $contion
     * @return mixed
     */
    public  static  function  userDetailEditByUid( $uId,$contion ){
        return self::where('user_id',$uId)->update($contion);
    }

    /**
     * 添加用户详情表信息
     * @param $contion
     * @return static
     */
    public static function newUser($contion){
        return self::create($contion);
    }
}
