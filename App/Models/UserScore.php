<?php
namespace App\Models;

class UserScore extends \Illuminate\Database\Eloquent\Model
{
    protected  $table='user_score';
    protected $primaryKey = 'user_id';
    protected $fillable = ['user_id','total','silver_total','silver_balance'];
    public static $unguarded = true;
    public $timestamps = false;


    /**
     * 获取单一用户数据
     * @param $uId
     * @return mixed
     */
    public  static  function  getUserByUid( $uId ){
        return self::where('user_id',$uId)->first();
    }

    /**
     * 根据用户uId更新用户详细信息
     * @param $uId
     * @param $contion
     * @return mixed
     */
    public  static  function  userEditByUid( $uId,$contion ){
        return self::where('user_id',$uId)->update($contion);
    }

    /**
     * 添加用户积分表信息
     * @param $contion
     * @return static
     */
    public static function newUser($contion){
        return self::create($contion);
    }
}
