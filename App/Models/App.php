<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class App extends Model{
    /**
     * @var string
     * */
    protected $table = 'app';

    /**
     * @var string
     * */
    protected $primaryKey = 'auto_id';


    protected  $fillable = ['name', 'app_id', 'app_security','code'];

    protected  $guarded = ['auto_id'];

    public     $timestamps = false;

    public  function  accessStat()
    {

          return  $this->hasMany('App\Models\AccessStat','app_id','app_id');
    }

    /**
     * @description 根据appID获取app
     * @param int $appId
     * @return App
     * */
    public static function getAppByAppId($appId)
    {
        return self :: where('app_id' ,$appId) ->where('dml_flag','!=',3) -> firstOrFail();
    }
}
