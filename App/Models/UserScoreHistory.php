<?php
namespace App\Models;

class UserScoreHistory extends \Illuminate\Database\Eloquent\Model
{
    protected  $table='user_score_history';
    protected  $primaryKey = 'auto_id';
    protected  $fillable = ['user_id','user_name','trade_id','source_code','in_silver','out_silver','notes','action_type','ip'];
    protected  $guarded = ['auto_id'];
    public     $timestamps = false;


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
     * 添加用户积分历史信息
     * @param $contion
     * @return static
     */
    public static function newScoureHistory($contion){
        return self::create($contion);
    }
}
