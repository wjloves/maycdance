<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Information extends Model{
    /**
     * @var string
     * */
    protected $table = 'information';

    /**
     * @var string
     * */
    protected $primaryKey = 'auto_id';


    protected  $fillable = ['title', 'img', 'url','app'];

    protected  $guarded = ['auto_id'];

    public     $timestamps = false;

    /**
     * 递归新增数据
     * @param $contion
     * @return static
     */
    public static function insertInfo($contion){
        foreach($contion as $val){
            $state = self::create($val);
            if(!$state){
                $newContion[] = $val;
            }
        }
        if(!empty($newContion)){
            self::insertInfo($newContion);
        }
        return true;
    }
}
