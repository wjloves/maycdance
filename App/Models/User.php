<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model{
    /**
     * @var string
     * */
    protected $table = 'user';

    /**
     * @var string
     * */
    protected $primaryKey = 'user_id';

    protected  $fillable = ['email', 'passwd', 'user_name','is_login','new_face','mobile','pay_passwd'];

    protected  $guarded = ['user_id'];

    public     $timestamps = false;

    /**
     * @description  获取用户扩展信息
     * @return HasMany
     */
//    public  function  userDetail()
//    {
//        return  $this->hasOne('App\Models\UserDetail','user_id','user_id');
//    }

    /**
     * @description 根据uid获取信息
     * @param int $uId
     * @return App
     * */
    public static function getUserById($uId,$type = 'user_id')
    {
        $res = self::where($type,$uId)->first();
        if($res){
            return $res->toArray();
        }else {
            return false;
        }
    }

    /**
     * @description 根据user_name获取信息
     * @param string $name
     * @return App
     * @author Jarvis
     * @history Create 2016/02/25
     * */
    public static function getUserByName($name)
    {
        //return self :: where('user_name' ,$name) -> first()->toArray();
        $db_obj = self :: where('user_name' ,$name) -> first();
        if (!empty($db_obj)) {
            return $db_obj->toArray();
        } else {
            return false;
        }
    }

    /**
     * @description 根据email获取信息
     * @param string $email
     * @return App
     * @author Jarvis
     * @history Create 2016/02/25
     * */
    public static function getUserByEmail($email)
    {
        //return self :: where('email' ,$email) -> first()->toArray();
        $db_obj = self :: where('email' ,$email) -> first();
        if (!empty($db_obj)) {
            return $db_obj->toArray();
        } else {
            return false;
        }
    }

    /**
     * @description 根据mobile获取信息
     * @param string $mobile
     * @return App
     * @author Jarvis
     * @history Create 2016/02/25
     * */
    public static function getUserByMobile($mobile)
    {
        //return self :: where('mobile' ,$mobile) -> first();
        $db_obj = self :: where('mobile' ,$mobile) -> first();
        if (!empty($db_obj)) {
            return $db_obj->toArray();
        } else {
            return false;
        }
    }

    /**
     * @description 根据多个用户ID获取用户信息
     * @param $uId  字符串格式
     * @return mixed
     */
    public  static function getManyUsers($Condition,$type){
        return self::whereIn($type,$Condition)->paginate(200);
    }

    /**
     * 根据uId更新用户数据
     * @param $uId
     * @param $coniton
     * @return mixed
     */
    public  static  function editByUid($uId,$coniton){
        return self::where('user_id',$uId)->update($coniton);
    }

    /**
     * 检测用户名和手机是否重复
     * @param $coniton
     * @return mixed
     */
    public  static  function checkUser($coniton){
        return self::where('user_name',$coniton['user_name'])->first();
    }

    /**
     * 注册新用户
     * @param $contion
     * @return static
     */
    public  static function  regUser($contion){
        return self::create($contion);
    }

    /**
     * 根据uId清除用户数据
     * @param $uId
     * @return mixed
     */
    public  static  function delByUid($uId){
        return self::where('user_id',$uId)->delete();
    }
}
